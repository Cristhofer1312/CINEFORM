<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Inscripcion;
use Auth;

class CalificacionController extends BaseController
{
    /**
     * Muestra la planilla de calificaciones para un contenido específico.
     */
    public function index(Request $request, $curso_id, $contenido_id)
    {
        $curso = Curso::findOrFail($curso_id);

        // Verificar permisos (solo facilitador)
        // Nota: BaseController ya tiene métodos para validar datos personales, pero aquí validamos autoría
        $personalData = $this->getUsuarioAutenticado()->personalData;
        if ($curso->id_persona != $personalData->id_persona) {
            abort(403, 'No tiene permiso para calificar este curso.');
        }

        $contenido = ContenidoCurso::where('id_curso', $curso_id)
            ->where('id_contenido_curso', $contenido_id)
            ->firstOrFail();

        if (!$contenido->es_evaluacion) {
            abort(400, 'Este contenido no es una evaluación.');
        }

        // Obtener estudiantes inscritos y sus calificaciones para este contenido
        // Usamos leftJoin para traer la calificación si existe
        $query = Inscripcion::where('taller.inscripciones.id_curso', $curso_id)
            ->join('comun.personas', 'taller.inscripciones.id_persona', '=', 'comun.personas.id_persona')
            ->leftJoin('taller.calificaciones', function ($join) use ($contenido_id) {
                $join->on('taller.inscripciones.id_persona', '=', 'taller.calificaciones.id_persona')
                    ->where('taller.calificaciones.id_contenido_curso', '=', $contenido_id);
            });

        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comun.personas.dni', 'like', "%{$search}%")
                    ->orWhere('comun.personas.primer_nombre', 'like', "%{$search}%")
                    ->orWhere('comun.personas.primer_apellido', 'like', "%{$search}%");
            });
        }

        $estudiantes = $query->select(
            'comun.personas.id_persona as id_persona',
            'comun.personas.dni',
            'comun.personas.primer_nombre',
            'comun.personas.segundo_nombre',
            'comun.personas.primer_apellido',
            'comun.personas.segundo_apellido',
            'taller.calificaciones.calificacion',
            'taller.calificaciones.observacion',
            'taller.calificaciones.id_calificacion'
        )
            ->orderBy('comun.personas.primer_apellido')
            ->get();

        $search = $request->search;

        return view('taller::a.CursoCalificar', compact('curso', 'contenido', 'estudiantes', 'search'));
    }

    /**
     * Guarda las calificaciones.
     */
    public function store(Request $request, $curso_id, $contenido_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $personalData = $this->getUsuarioAutenticado()->personalData;

        if ($curso->id_persona != $personalData->id_persona) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'calificaciones' => 'array',
            'calificaciones.*.nota' => 'nullable|numeric|min:0|max:100',
            'calificaciones.*.observacion' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->calificaciones as $persona_id => $datos) {
                // Si viene nota o calificación, actualizamos/creamos
                if (isset($datos['nota']) || isset($datos['observacion'])) {
                    DB::table('calificaciones')->updateOrInsert(
                        [
                            'id_curso' => $curso_id,
                            'id_contenido_curso' => $contenido_id,
                            'id_persona' => $persona_id
                        ],
                        [
                            'calificacion' => $datos['nota'],
                            'observacion' => $datos['observacion'],
                            'calificado_por' => Auth::id(), // ID de usuario del sistema (no personalData)
                            'actualizado_en' => now()
                        ]
                    );
                }
            }
            DB::commit();
            return back()->with('success', 'Calificaciones guardadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar calificaciones: ' . $e->getMessage());
        }
    }
}
