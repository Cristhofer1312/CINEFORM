<?php

namespace Modules\Taller\Services;

use App\Enums\EstadoCurso;
use Illuminate\Support\Facades\DB;

class EstadisticasService
{
    /**
     * Aplica los filtros compartidos a una consulta sobre taller.cursos (alias 'cursos').
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filtros anio, id_modalidad, id_actividad_formativa, id_aspecto, id_estado
     */
    public function aplicarFiltros($query, array $filtros): void
    {
        if (!empty($filtros['anio'])) {
            $query->where('cursos.anio', $filtros['anio']);
        }
        if (!empty($filtros['id_modalidad'])) {
            $query->where('cursos.id_modalidad', $filtros['id_modalidad']);
        }
        if (!empty($filtros['id_actividad_formativa'])) {
            $query->where('cursos.id_actividad_formativa', $filtros['id_actividad_formativa']);
        }
        if (!empty($filtros['id_aspecto'])) {
            $query->where('cursos.id_aspecto', $filtros['id_aspecto']);
        }
        if (!empty($filtros['id_estado'])) {
            $query->whereExists(function ($sub) use ($filtros) {
                $sub->select(DB::raw(1))
                    ->from(DB::raw('(SELECT DISTINCT ON (ce.id_curso) ce.id_curso, ce.id_estado FROM taller.curso_estado ce ORDER BY ce.id_curso, ce.created_at DESC) AS ultimo_estado'))
                    ->whereColumn('ultimo_estado.id_curso', 'cursos.id_curso')
                    ->where('ultimo_estado.id_estado', $filtros['id_estado']);
            });
        }
    }

    /**
     * Query base sobre taller.cursos con los filtros aplicados.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function cursosQuery(array $filtros)
    {
        $query = DB::table('taller.cursos as cursos');
        $this->aplicarFiltros($query, $filtros);
        return $query;
    }

    /**
     * Indicadores globales (cards KPI).
     */
    public function getKpis(array $filtros = []): array
    {
        // Total de cursos (con filtros)
        $totalCursos = $this->cursosQuery($filtros)->count();

        // Inscripciones aprobadas y participantes únicos (con filtros por curso)
        $inscripcionesBase = DB::table('taller.inscripciones as i')
            ->join('taller.cursos as cursos', 'cursos.id_curso', '=', 'i.id_curso')
            ->where('i.estado', 'aprobado');
        $this->aplicarFiltros($inscripcionesBase, $filtros);

        $inscripcionesAprobadas = (clone $inscripcionesBase)->count();
        $participantesUnicos = (clone $inscripcionesBase)->distinct()->count('i.id_persona');

        // Certificados (sobre inscripciones aprobadas)
        $certificados = (clone $inscripcionesBase)
            ->selectRaw("COUNT(*) FILTER (WHERE i.certificado_aprobado IS TRUE) AS aprobados")
            ->selectRaw("COUNT(*) FILTER (WHERE i.certificado_aprobado IS FALSE) AS denegados")
            ->selectRaw("COUNT(*) FILTER (WHERE i.certificado_aprobado IS NULL) AS pendientes")
            ->first();

        // Ocupación promedio de cupos (cursos con cupo definido y al menos 1 inscripción aprobada)
        $ocupacion = DB::table('taller.cursos as cursos')
            ->leftJoin('taller.inscripciones as i', function ($join) {
                $join->on('i.id_curso', '=', 'cursos.id_curso')
                    ->where('i.estado', '=', 'aprobado');
            })
            ->whereNotNull('cursos.cantidad_cupos')
            ->where('cursos.cantidad_cupos', '>', 0);
        $this->aplicarFiltros($ocupacion, $filtros);

        $ocupacionPromedio = round((clone $ocupacion)
            ->selectRaw('(COUNT(i.id_inscripcion) * 100.0) / CAST(cursos.cantidad_cupos AS NUMERIC) AS promedio')
            ->groupBy('cursos.id_curso', 'cursos.cantidad_cupos')
            ->get()
            ->avg('promedio'), 1);

        return [
            'total_cursos' => $totalCursos,
            'inscripciones_aprobadas' => $inscripcionesAprobadas,
            'participantes_unicos' => $participantesUnicos,
            'certificados_aprobados' => (int) $certificados->aprobados,
            'certificados_denegados' => (int) $certificados->denegados,
            'certificados_pendientes' => (int) $certificados->pendientes,
            'ocupacion_promedio' => $ocupacionPromedio ?: 0,
        ];
    }

    /**
     * Distribución de cursos por estado actual.
     */
    public function getCursosPorEstado(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->joinSub(
                DB::table('taller.curso_estado as ce2')
                    ->selectRaw('DISTINCT ON (ce2.id_curso) ce2.id_curso, ce2.id_estado')
                    ->orderByRaw('ce2.id_curso, ce2.created_at DESC'),
                'ue',
                'ue.id_curso',
                '=',
                'cursos.id_curso'
            )
            ->selectRaw('ue.id_estado, COUNT(*) AS total')
            ->groupBy('ue.id_estado')
            ->orderBy('ue.id_estado')
            ->get()
            ->keyBy('id_estado');

        $labels = [];
        $values = [];
        foreach (EstadoCurso::cases() as $estado) {
            $labels[] = $estado->label();
            $values[] = (int) ($rows[$estado->value]->total ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Distribución de cursos por modalidad.
     */
    public function getCursosPorModalidad(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->leftJoin('taller.modalidad as m', 'm.id_modalidad', '=', 'cursos.id_modalidad')
            ->selectRaw("COALESCE(m.nombre_modalidad, 'Sin modalidad') AS label, COUNT(*) AS total")
            ->groupBy('m.nombre_modalidad')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Distribución de cursos por actividad formativa.
     */
    public function getCursosPorActividadFormativa(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->leftJoin('taller.actividades_formativas as af', 'af.id_actividad_formativa', '=', 'cursos.id_actividad_formativa')
            ->selectRaw("COALESCE(af.nombre, 'Sin actividad') AS label, COUNT(*) AS total")
            ->groupBy('af.nombre')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Distribución de cursos por aspecto de formación.
     */
    public function getCursosPorAspecto(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->leftJoin('taller.aspectos as a', 'a.id_aspecto', '=', 'cursos.id_aspecto')
            ->selectRaw("COALESCE(a.nombre, 'Sin aspecto') AS label, COUNT(*) AS total")
            ->groupBy('a.nombre')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Distribución de cursos por año.
     */
    public function getCursosPorAnio(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->selectRaw('cursos.anio, COUNT(*) AS total')
            ->groupBy('cursos.anio')
            ->orderBy('cursos.anio')
            ->get();

        return [
            'labels' => $rows->pluck('anio')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Distribución de cursos por trimestre.
     */
    public function getCursosPorTrimestre(array $filtros = []): array
    {
        $rows = $this->cursosQuery($filtros)
            ->whereNotNull('cursos.trimestre')
            ->selectRaw('cursos.trimestre, COUNT(*) AS total')
            ->groupBy('cursos.trimestre')
            ->orderBy('cursos.trimestre')
            ->get();

        $labels = [];
        $values = [];
        for ($t = 1; $t <= 4; $t++) {
            $labels[] = "Trimestre $t";
            $values[] = (int) ($rows->firstWhere('trimestre', $t)->total ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Evolución de inscripciones por mes (YYYY-MM).
     */
    public function getInscripcionesEnElTiempo(array $filtros = []): array
    {
        $query = DB::table('taller.inscripciones as i')
            ->join('taller.cursos as cursos', 'cursos.id_curso', '=', 'i.id_curso')
            ->selectRaw("TO_CHAR(i.fecha_inscripcion, 'YYYY-MM') AS mes, COUNT(*) AS total")
            ->groupBy('mes')
            ->orderBy('mes');
        $this->aplicarFiltros($query, $filtros);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('mes')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Distribución geográfica de los cursos por estado (curso_localidades).
     */
    public function getDistribucionGeografica(array $filtros = []): array
    {
        $query = DB::table('taller.curso_localidades as cl')
            ->join('taller.cursos as cursos', 'cursos.id_curso', '=', 'cl.id_curso')
            ->join('comun.estados as e', 'e.id', '=', 'cl.id_estado')
            ->selectRaw('e.description AS label, COUNT(*) AS total')
            ->groupBy('e.description')
            ->orderByDesc('total');
        $this->aplicarFiltros($query, $filtros);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }
}
