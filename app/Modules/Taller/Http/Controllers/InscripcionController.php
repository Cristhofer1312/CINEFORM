<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Curso;
use Carbon\Carbon;
use Modules\Taller\Http\Controllers\BaseController;

class InscripcionController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        /**
         * IMPORTANTE — Esquema PostgreSQL:
         * NO usar 'exists:taller.cursos,...' directamente en las reglas de validación.
         * Laravel interpreta el punto (.) como separador de conexión.tabla, NO como esquema.tabla,
         * lo cual causa errores de "tabla no encontrada" en PostgreSQL multi-esquema.
         * Usar la referencia a la clase del Modelo (Curso::class) resuelve el $table correcto
         * ('taller.cursos') desde la propiedad del modelo, evitando la ambigüedad.
         */
        $request->validate([
            'id_curso' => 'required|exists:' . Curso::class . ',id_curso',
        ]);

        try {
            // Verificar permiso formal de inscripción
            if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::INSCRIBIRSE_CURSO)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes los permisos necesarios para inscribirte en este programa.'
                ], 403);
            }

            $curso = Curso::findOrFail($request->id_curso);
            $user = $this->getUsuarioAutenticado();

            if ($this->usuarioSinDatosPersonales()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron los datos personales del usuario.'
                ], 404);
            }

            if ($curso->estado_actual->id_estado != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'El curso no se encuentra en estado de inscripción.',
                    'data' => $curso
                ], 400);
            }
            // Verificar si el usuario es el propietario del curso
            if ($curso->id_persona == $user->personalData->id_persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes inscribirte en tu propio curso.'
                ], 403);
            }

            // Verificar si ya está inscrito
            $yaInscrito = Inscripcion::where('id_curso', $curso->id_curso)
                ->where('id_persona', $user->personalData->id_persona)
                ->exists();

            if ($yaInscrito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya estás inscrito en este curso.'
                ], 400);
            }

            // Verificar si hay cupos disponibles
            $inscritos = Inscripcion::where('id_curso', $curso->id_curso)->count();
            if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cupos disponibles para este curso.'
                ], 400);
            }

            // Crear la inscripción
            $inscripcion = Inscripcion::create([
                'id_curso' => $curso->id_curso,
                'id_persona' => $user->personalData->id_persona,
                'fecha_inscripcion' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscripción exitosa.',
                'data' => $inscripcion,
                'cupos_restantes' => $curso->cantidad_cupos !== null 
                    ? max(0, (int)$curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count()) 
                    : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la inscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->getUsuarioAutenticado();

        $inscripcion = Inscripcion::findOrFail($id);
        $curso = $inscripcion->curso;

        if ($curso->estado_actual->id_estado != 6) {
            return response()->json([
                'success' => false,
                'message' => 'El curso ya ha iniciado, no se puede gestionar el cupo.',
                'data' => $curso
            ], 403);
        }

        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario. Por favor, complete su perfil primero.'
            ], 404);
        }

        if ($inscripcion->id_persona != $user->personalData->id_persona) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizada para cancelar esta inscripción.'
            ], 403);
        }

        $inscripcion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción cancelada correctamente.',
            'cupos_restantes' => $curso->cantidad_cupos !== null 
                ? max(0, (int)$curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count()) 
                : null
        ]);
    }
}