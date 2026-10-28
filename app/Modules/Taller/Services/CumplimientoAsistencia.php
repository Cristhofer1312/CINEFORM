<?php

namespace Modules\Taller\Services;

use Illuminate\Support\Collection;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\AsistenciaJustificacion;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;

/**
 * D3 (26/10): para certificar se exige asistir a todas las clases del curso
 * (contenidos que no son evaluación). Una falta justificada por el facilitador
 * cuenta como cumplida.
 */
class CumplimientoAsistencia
{
    /**
     * Resumen indexado por id_inscripcion. `justificadas` es
     * [id_contenido_curso => AsistenciaJustificacion]; `faltas` son las clases
     * sin asistencia ni justificación.
     *
     * @param  Collection<int, Inscripcion>  $inscripciones
     */
    public function porInscripcion(Curso $curso, Collection $inscripciones): array
    {
        $clases = $this->clases($curso);
        $ids = $clases->pluck('id_contenido_curso');

        $asistencias = Asistencia::whereIn('id_contenido_curso', $ids)
            ->whereIn('id_persona', $inscripciones->pluck('id_persona'))
            ->where('activa', true)
            ->get()
            ->groupBy('id_persona');

        $justificaciones = AsistenciaJustificacion::whereIn('id_contenido_curso', $ids)
            ->whereIn('id_inscripcion', $inscripciones->pluck('id_inscripcion'))
            ->get()
            ->groupBy('id_inscripcion');

        $resultado = [];
        foreach ($inscripciones as $inscripcion) {
            $asistidas = $asistencias->get($inscripcion->id_persona, collect())->pluck('id_contenido_curso')->unique()->values();
            // toBase(): except() de la colección Eloquent filtra por clave primaria, no por índice.
            $justificadas = $justificaciones->get($inscripcion->id_inscripcion, collect())
                ->toBase()
                ->keyBy('id_contenido_curso')
                ->except($asistidas->all());
            $faltas = $ids->diff($asistidas)->diff($justificadas->keys())->values();

            $total = $ids->count();
            $resultado[$inscripcion->id_inscripcion] = [
                'total' => $total,
                'asistidas' => $asistidas,
                'justificadas' => $justificadas,
                'faltas' => $faltas,
                'porcentaje' => $total > 0 ? round((($total - $faltas->count()) / $total) * 100, 1) : 100.0,
                'cumple' => $faltas->isEmpty(),
            ];
        }

        return $resultado;
    }

    public function deInscripcion(Curso $curso, Inscripcion $inscripcion): array
    {
        return $this->porInscripcion($curso, collect([$inscripcion]))[$inscripcion->id_inscripcion];
    }

    /** @return Collection<int, ContenidoCurso> */
    private function clases(Curso $curso): Collection
    {
        return ContenidoCurso::where('id_curso', $curso->id_curso)
            ->where('es_evaluacion', false)
            ->get(['id_contenido_curso']);
    }
}
