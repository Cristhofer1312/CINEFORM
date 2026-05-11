<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Modules\Taller\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;
use Modules\Comun\Entities\PersonalData;

class CursoAsignadoController extends BaseController
{
    /**
     * Muestra los cursos asignados al facilitador (donde es responsable)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = $this->getUsuarioAutenticado();

        if ($this->usuarioSinDatosPersonales()) {
            return view('taller::a.CursosAsignados', ['cursos' => collect()]);
        }

        // Cargamos los cursos con sus relaciones
        $cursos = Curso::with([
            'modalidad',
            'estados' => function ($query) {
                $query->orderBy('taller.curso_estado.created_at', 'desc')
                    ->withPivot('motivo');
            }
        ])
            ->where('id_persona', $user->personalData->id_persona)
            ->withCount(['contenidos as total_contenidos', 'inscripciones'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        // Agregamos el estado actual a cada curso
        $cursos->getCollection()->each(function ($curso) {
            $curso->estado_actual = $curso->estados->first();
            // También podemos agregar el ID del estado directamente al modelo para facilitar el acceso
            if ($curso->estado_actual) {
                $curso->estado_id = $curso->estado_actual->id_estado;
            }
        });

        return view('taller::a.CursosAsignados', compact('cursos'));
    }



    /**
     * Accept a course (legacy method)
     * 
     * @param int $id_curso
     * @return \Illuminate\Http\RedirectResponse
     */
    public function aceptar($id_curso)
    {
        try {
            // Call the new method
            $response = $this->aceptarCurso($id_curso);
            $data = $response->getData();

            if ($data->success) {
                return redirect()->route('taller.mis-cursos-asignados')
                    ->with('success', $data->message);
            }

            return back()->with('error', $data->message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al aceptar el curso: ' . $e->getMessage());
        }
    }
    /**
     * Update the course status to accepted (status_id = 6)
     *
     * @param int $id_curso
     * @return \Illuminate\Http\JsonResponse
     */
    public function aceptarCurso($id_curso)
    {
        try {
            if ($this->usuarioSinDatosPersonales()) {
                return response()->json(['success' => false, 'message' => 'Usuario no tiene datos personales asociados'], 403);
            }

            $user = $this->getUsuarioAutenticado();

            // Brecha #3 corregida: verificar permiso RBAC accept_course antes de procesar
            if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::RESPONDER_CURSO)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes el permiso para aceptar o declinar asignaciones de cursos.'
                ], 403);
            }

            // Verificar que el curso existe y pertenece al facilitador actual
            $curso = Curso::where('id_curso', $id_curso)
                ->where('id_persona', $user->personalData->id_persona)
                ->firstOrFail();

            // Actualizar el estado existente del curso a EDICION
            $updated = DB::table('taller.curso_estado')
                ->where('id_curso', $id_curso)
                ->latest('created_at')
                ->update([
                    'id_estado' => EstadoCurso::EDICION->value,
                    'updated_at' => now()
                ]);


            return response()->json([
                'success' => true,
                'message' => 'Curso aceptado exitosamente',
                'estado_actual' => [
                    'id_estado' => EstadoCurso::EDICION->value,
                    'updated_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado o no autorizado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el estado del curso: ' . $e->getMessage()], 500);
        }
    }
}
