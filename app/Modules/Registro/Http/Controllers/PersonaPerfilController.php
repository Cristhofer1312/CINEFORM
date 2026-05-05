<?php

namespace Modules\Registro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Registro\Entities\Personas;
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
            $persona = Personas::with('especializaciones')
                ->where('dni', $dni)
                ->first();

            if (!$persona) {
                return response()->json(['success' => false, 'message' => 'Persona no encontrada con esa cédula'], 404);
            }

            // Cargar perfiles actuales de forma segura
            $perfilesActuales = [];
            if ($persona->usuario) {
                $perfilesActuales = $persona->usuario->getPerfiles()->pluck('id')->toArray();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_persona'       => $persona->id_persona,
                    'nombre_completo'  => $persona->nombre_completo,
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

        $persona = Personas::find($id_persona);

        if (!$persona) {
            return response()->json(['success' => false, 'message' => 'Persona no encontrada en la base de datos'], 404);
        }

        $user = $persona->usuario;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'La persona no tiene un usuario asociado para asignar perfiles'], 400);
        }

        try {
            DB::beginTransaction();

            $perfilesIds = $request->input('perfiles', []);
            
            \Illuminate\Support\Facades\Log::info('Asignación de perfiles', [
                'id_persona' => $id_persona, 
                'user_id' => $user->id, 
                'perfiles_recibidos' => $perfilesIds
            ]);

            // 1. Eliminar asignaciones actuales para este usuario
            DB::table('security.profiles_users')->where('id_users', $user->id)->delete();

            // 2. Insertar las nuevas asignaciones
            if (!empty($perfilesIds)) {
                $inserts = [];
                foreach ($perfilesIds as $idRol) {
                    $inserts[] = [
                        'id_rol' => $idRol,
                        'id_users' => $user->id,
                        'status' => 0, // 0 = Activo
                        'creado_por' => auth()->id() ?? 1,
                        'creado_en' => now(),
                        'actualizado_por' => auth()->id() ?? 1,
                        'actualizado_en' => now(),
                    ];
                }
                DB::table('security.profiles_users')->insert($inserts);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Perfiles actualizados correctamente para ' . $persona->nombre_completo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al asignar perfiles: ' . $e->getMessage()], 500);
        }
    }
}
