<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;

class CalificacionController extends BaseController
{
    /**
     * Muestra la planilla de calificaciones para un contenido específico.
     */
    public function index(Request $request, $curso_id, $contenido_id)
    {
        $curso = Curso::findOrFail($curso_id);

        $this->authorize('calificar', $curso);

        $contenido = ContenidoCurso::where('id_curso', $curso_id)
            ->where('id_contenido_curso', $contenido_id)
            ->firstOrFail();

        if (! $contenido->es_evaluacion) {
            abort(400, 'Este contenido no es una evaluación.');
        }

        // Obtener estudiantes inscritos y sus calificaciones para este contenido
        // Usamos leftJoin para traer la calificación si existe
        $query = Inscripcion::where('taller.inscripciones.id_curso', $curso_id)
            ->where('taller.inscripciones.estado', Inscripcion::ESTADO_APROBADO)
            ->join('comun.personas', 'taller.inscripciones.id_persona', '=', 'comun.personas.id_persona')
            ->leftJoin('taller.calificaciones', function ($join) use ($contenido_id) {
                $join->on('taller.inscripciones.id_persona', '=', 'taller.calificaciones.id_persona')
                    ->where('taller.calificaciones.id_contenido_curso', '=', $contenido_id);
            });

        // Filtro de búsqueda
        if ($request->has('search') && ! empty($request->search)) {
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
        $this->authorize('calificar', $curso);

        $estadoActualId = $curso->estado_actual->id_estado ?? null;
        if (in_array($estadoActualId, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value], true)) {
            abort(403, 'No se pueden modificar calificaciones: el curso ya está Finalizado o Cerrado.');
        }

        $contenido = ContenidoCurso::where('id_curso', $curso_id)
            ->where('id_contenido_curso', $contenido_id)
            ->firstOrFail();

        if (! $contenido->es_evaluacion) {
            abort(400, 'Este contenido no es una evaluación.');
        }

        $request->validate([
            'calificaciones' => 'array',
            'calificaciones.*.nota' => 'nullable|numeric|min:0|max:100',
            'calificaciones.*.observacion' => 'nullable|string|max:500',
        ]);

        // Solo se puede calificar a participantes con inscripción aprobada en este curso.
        $personasValidas = Inscripcion::where('id_curso', $curso_id)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->pluck('id_persona')
            ->map(fn ($id) => (int) $id)
            ->all();

        $personasInvalidas = array_diff(array_map('intval', array_keys($request->calificaciones ?? [])), $personasValidas);
        if (! empty($personasInvalidas)) {
            abort(422, 'Hay participantes que no están inscritos y aprobados en este curso.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->calificaciones as $persona_id => $datos) {
                $nota = $datos['nota'] ?? null;
                $observacion = $datos['observacion'] ?? null;

                $tieneNota = ($nota !== null && $nota !== '');
                $tieneObs = ($observacion !== null && trim($observacion) !== '');

                // Si ambos vienen vacíos, omitir para no borrar notas existentes ni insertar vacíos
                if (! $tieneNota && ! $tieneObs) {
                    continue;
                }

                $updateData = [
                    'calificado_por' => Auth::id(),
                    'actualizado_en' => now(),
                ];

                if ($tieneNota) {
                    $updateData['calificacion'] = $nota;
                }
                if ($observacion !== null) {
                    $updateData['observacion'] = $tieneObs ? trim($observacion) : null;
                }

                DB::table('taller.calificaciones')->updateOrInsert(
                    [
                        'id_curso' => $curso_id,
                        'id_contenido_curso' => $contenido_id,
                        'id_persona' => $persona_id,
                    ],
                    $updateData
                );
            }
            DB::commit();

            return back()->with('success', 'Calificaciones guardadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al guardar calificaciones: '.$e->getMessage());
        }
    }
}
