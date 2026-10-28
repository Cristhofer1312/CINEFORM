<?php

namespace Modules\Registro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Comun\Entities\PersonalData;
use Modules\Security\Entities\Profile;
use Illuminate\Support\Facades\DB;

class PersonaPerfilController extends Controller
{
    public function index()
    {
        $perfiles = Profile::all();
        $asignables = $this->perfilesAsignables();

        return view('registro::asignar_perfil', compact('perfiles', 'asignables'));
    }

    /**
     * D8: perfiles activos que el actor posee con asignación activa; el raíz, todos.
     *
     * @return array<int, int>
     */
    private function perfilesAsignables(): array
    {
        return app(\App\Services\Security\Autorizacion::class)->perfilesAsignables();
    }

    public function searchByDni($dni)
    {
        try {
            $dniClean = preg_replace('/[^0-9]/', '', $dni);

            $persona = PersonalData::with('especializaciones')
                ->where('dni', $dni)
                ->when(!empty($dniClean), function ($q) use ($dniClean) {
                    $q->orWhere('dni', $dniClean);
                })
                ->first();

            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Persona no encontrada con la cédula ' . $dni
                ], 200);
            }

            // Cargar perfiles actuales de forma segura
            $perfilesActuales = [];
            $hasUser = false;

            if ($persona->user) {
                $hasUser = true;
                $perfilesActuales = $persona->user->perfiles->pluck('id')->map(fn($id) => (int)$id)->toArray();
            } else if ($persona->user_id) {
                $user = \Modules\Security\Entities\User::find($persona->user_id);
                if ($user) {
                    $hasUser = true;
                    $perfilesActuales = $user->perfiles->pluck('id')->map(fn($id) => (int)$id)->toArray();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_persona'       => $persona->id_persona,
                    'nombre_completo'  => $persona->nombre_completo,
                    'has_user'         => $hasUser,
                    'especializaciones'=> $persona->especializaciones->pluck('nombre'),
                    'perfiles_actuales'=> $perfilesActuales,
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en searchByDni', [
                // Documento enmascarado: el log no guarda PII completa.
                'dni'   => str_repeat('*', max(strlen((string) $dni) - 3, 0)).substr((string) $dni, -3),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al buscar la persona: ' . (config('app.debug') ? $e->getMessage() : 'intente nuevamente o contacte al administrador.')
            ], 500);
        }
    }

    public function assignProfiles(Request $request)
    {
        $id_persona = $request->input('id_persona');
        
        if (!$id_persona) {
            return response()->json(['success' => false, 'message' => 'Falta el ID de la persona'], 400);
        }

        $persona = PersonalData::find($id_persona);

        if (!$persona) {
            return response()->json(['success' => false, 'message' => 'Persona no encontrada en la base de datos'], 404);
        }

        $user = $persona->user;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'La persona no tiene un usuario asociado para asignar perfiles'], 400);
        }

        $actor = auth()->user();
        $autorizacion = app(\App\Services\Security\Autorizacion::class);

        // 1. Bloquear modificar perfiles del usuario Administrador raíz (ID 1)
        if ($autorizacion->esRaiz($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No está permitido modificar los perfiles del usuario administrador principal.'
            ], 403);
        }

        // 2. Bloquear autoasignación / autopromoción
        if ($actor && $user->id === $actor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes modificar tus propios perfiles.'
            ], 403);
        }

        $perfilesIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('perfiles', [])))));

        // 4. Validar existencia y estado activo de todos los perfiles recibidos
        $perfilesValidos = DB::table('security.profiles')
            ->whereIn('id', $perfilesIds)
            ->where('active', true)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (count($perfilesValidos) !== count($perfilesIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Uno o más perfiles seleccionados no son válidos o están inactivos.'
            ], 422);
        }

        // 5. D8: quien tiene ASIGNAR_PERFIL (middleware de la ruta) solo asigna o quita
        // los perfiles que él mismo posee. Los demás perfiles del usuario se conservan.
        $actuales = DB::table('security.profiles_users')
            ->where('id_users', $user->id)
            ->pluck('id_rol')
            ->map(fn ($id) => (int) $id)
            ->all();

        $asignables = $this->perfilesAsignables();

        $noPermitidos = array_values(array_diff($perfilesIds, $asignables, $actuales));
        if (! empty($noPermitidos)) {
            $nombres = DB::table('security.profiles')->whereIn('id', $noPermitidos)->orderBy('id')->pluck('name')->implode(', ');

            return response()->json([
                'success' => false,
                'message' => 'Solo puede asignar perfiles que usted posee. No puede asignar: '.$nombres.'.',
            ], 403);
        }

        $finales = array_values(array_unique(array_merge(array_diff($actuales, $asignables), $perfilesIds)));
        sort($finales);

        // 3. El usuario debe conservar al menos un perfil
        if (empty($finales)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe asignar al menos un perfil al usuario.'
            ], 422);
        }

        $aQuitar = array_values(array_diff($actuales, $finales));
        $aAgregar = array_values(array_diff($finales, $actuales));

        if (empty($aQuitar) && empty($aAgregar)) {
            return response()->json(['success' => true, 'message' => 'Sin cambios en los perfiles de ' . $persona->nombre_completo]);
        }

        try {
            DB::beginTransaction();

            if (! empty($aQuitar)) {
                DB::table('security.profiles_users')
                    ->where('id_users', $user->id)
                    ->whereIn('id_rol', $aQuitar)
                    ->delete();
            }

            // status = 1 significa activo en profiles_users
            if (! empty($aAgregar)) {
                DB::table('security.profiles_users')->insert(array_map(fn ($idRol) => [
                    'id_rol' => $idRol,
                    'id_users' => $user->id,
                    'status' => 1,
                    'creado_por' => auth()->id() ?? 1,
                    'creado_en' => now(),
                    'actualizado_por' => auth()->id() ?? 1,
                    'actualizado_en' => now(),
                ], $aAgregar));
            }

            // Invalida la sesión activa del usuario modificado regenerando su session_token.
            // Usamos DB::table porque session_token no está en User::$fillable.
            DB::table('security.users')
                ->where('id', $user->id)
                ->update(['session_token' => \Illuminate\Support\Str::random(40)]);

            sort($actuales);
            sort($finales);
            app(\App\Services\Security\Auditoria::class)->registrar(
                'usuario.perfiles.asignar',
                'usuario',
                $user->id,
                ['perfiles' => $actuales],
                ['perfiles' => $finales]
            );

            DB::commit();
            app(\App\Services\Security\Autorizacion::class)->invalidar();
            return response()->json(['success' => true, 'message' => 'Perfiles actualizados correctamente para ' . $persona->nombre_completo]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al asignar perfiles: ' . (config('app.debug') ? $e->getMessage() : 'intente nuevamente o contacte al administrador.')], 500);
        }
    }
}
