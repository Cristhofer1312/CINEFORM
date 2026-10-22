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
        return view('registro::asignar_perfil', compact('perfiles'));
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
                'dni'   => $dni,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al buscar la persona: ' . $e->getMessage()
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

        // 1. Bloquear modificar perfiles del usuario Administrador raíz (ID 1)
        if ($user->id === 1) {
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

        // 3. Exigir al menos un perfil asignado
        if (empty($perfilesIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe asignar al menos un perfil al usuario.'
            ], 422);
        }

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

        // 5. Allowlist por jerarquía del actor:
        // - Admin (perfil 1 o usuario raíz 1): puede asignar cualquier perfil activo.
        // - Coordinador (perfil 4): SOLO puede asignar Facilitador (2) o Participante (3).
        // - Cualquier otro: denegado.
        $perfilActor = (int) session('profile_id');
        $esAdmin = ($actor && $actor->id === 1) || $perfilActor === \App\Constants\Perfil::ADMINISTRADOR;
        $esCoordinador = $perfilActor === \App\Constants\Perfil::COORDINADOR;

        if (!$esAdmin && !$esCoordinador) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para asignar perfiles.'
            ], 403);
        }

        if (!$esAdmin && $esCoordinador) {
            $allowlistCoordinador = [\App\Constants\Perfil::FACILITADOR, \App\Constants\Perfil::PARTICIPANTE];
            foreach ($perfilesIds as $idRol) {
                if (!in_array($idRol, $allowlistCoordinador, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El perfil Coordinador únicamente puede asignar roles de Facilitador y Participante.'
                    ], 403);
                }
            }
        }

        try {
            DB::beginTransaction();
            
            \Illuminate\Support\Facades\Log::info('Asignación de perfiles', [
                'id_persona' => $id_persona, 
                'user_id' => $user->id, 
                'actor_id' => $actor?->id,
                'perfiles_recibidos' => $perfilesIds
            ]);

            // Eliminar asignaciones actuales para este usuario
            DB::table('security.profiles_users')->where('id_users', $user->id)->delete();

            // Insertar las nuevas asignaciones (status = 1 significa activo en profiles_users)
            $inserts = [];
            foreach ($perfilesIds as $idRol) {
                $inserts[] = [
                    'id_rol' => $idRol,
                    'id_users' => $user->id,
                    'status' => 1, // 1 = Activo (coherente con ProfilesUsersSeeder, RegisterController, PostulacionFacilitadorController)
                    'creado_por' => auth()->id() ?? 1,
                    'creado_en' => now(),
                    'actualizado_por' => auth()->id() ?? 1,
                    'actualizado_en' => now(),
                ];
            }
            DB::table('security.profiles_users')->insert($inserts);

            // Invalida la sesión activa del usuario modificado regenerando su session_token.
            // Usamos DB::table porque session_token no está en User::$fillable.
            DB::table('security.users')
                ->where('id', $user->id)
                ->update(['session_token' => \Illuminate\Support\Str::random(40)]);

            DB::commit();
            app(\App\Services\Security\Autorizacion::class)->invalidar();
            return response()->json(['success' => true, 'message' => 'Perfiles actualizados correctamente para ' . $persona->nombre_completo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al asignar perfiles: ' . $e->getMessage()], 500);
        }
    }
}
