<?php

namespace Modules\Taller\Http\Controllers;

use App\Constants\SecurityAction;
use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Services\EstadisticasService;

class EstadisticasController extends BaseController
{
    protected $service;

    public function __construct(EstadisticasService $service)
    {
        $this->service = $service;
    }

    /**
     * Interfaz de estadísticas de cursos (página standalone).
     */
    public function index(Request $request)
    {
        $this->autorizar();

        $filtros = $this->filtrosDesdeRequest($request);

        $opciones = [
            'anios' => DB::table('taller.cursos')
                ->whereNotNull('anio')
                ->distinct()
                ->orderByDesc('anio')
                ->pluck('anio'),
            'modalidades' => DB::table('taller.modalidad')->orderBy('nombre_modalidad')->get(),
            'actividades' => DB::table('taller.actividades_formativas')->orderBy('nombre')->get(),
            'aspectos' => DB::table('taller.aspectos')->orderBy('nombre')->get(),
            'estados' => collect(EstadoCurso::cases())->map(fn ($e) => [
                'id' => $e->value,
                'label' => $e->label(),
            ]),
        ];

        $datos = $this->recolectarDatos($filtros);

        return view('taller::a.Estadisticas', compact('opciones', 'filtros', 'datos'));
    }

    /**
     * Endpoint AJAX que devuelve todos los datasets + KPIs según los filtros.
     */
    public function datos(Request $request)
    {
        $this->autorizar();

        if (!$request->ajax()) {
            abort(404);
        }

        $filtros = $this->filtrosDesdeRequest($request);

        return response()->json($this->recolectarDatos($filtros));
    }

    /**
     * Recolecta todos los indicadores y gráficos con los filtros aplicados.
     */
    protected function recolectarDatos(array $filtros): array
    {
        return [
            'kpis' => $this->service->getKpis($filtros),
            'porEstado' => $this->service->getCursosPorEstado($filtros),
            'porModalidad' => $this->service->getCursosPorModalidad($filtros),
            'porActividad' => $this->service->getCursosPorActividadFormativa($filtros),
            'porAspecto' => $this->service->getCursosPorAspecto($filtros),
            'porAnio' => $this->service->getCursosPorAnio($filtros),
            'porTrimestre' => $this->service->getCursosPorTrimestre($filtros),
            'inscripcionesTiempo' => $this->service->getInscripcionesEnElTiempo($filtros),
            'geografia' => $this->service->getDistribucionGeografica($filtros),
        ];
    }

    /**
     * Extrae y limpia los filtros válidos del request.
     */
    protected function filtrosDesdeRequest(Request $request): array
    {
        return [
            'anio' => $request->integer('anio') ?: null,
            'id_modalidad' => $request->integer('id_modalidad') ?: null,
            'id_actividad_formativa' => $request->integer('id_actividad_formativa') ?: null,
            'id_aspecto' => $request->integer('id_aspecto') ?: null,
            'id_estado' => $request->integer('id_estado') ?: null,
        ];
    }

    /**
     * Verifica el permiso RBAC para consultar estadísticas.
     */
    protected function autorizar(): void
    {
        if (!hasPermissionRoute('taller.estadisticas.index', SecurityAction::VER_ESTADISTICAS)) {
            abort(403, 'No tienes permiso para consultar las estadísticas.');
        }
    }
}
