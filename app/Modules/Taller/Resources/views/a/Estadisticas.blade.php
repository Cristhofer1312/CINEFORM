@extends('layouts.kaiadmin-menu')

@section('title', 'Estadísticas de Cursos')

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-card overflow-hidden" style="border-radius: 1rem;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-info-soft text-info me-2 px-3 py-1 rounded-pill" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-chart-bar me-1"></i> Administración
                            </span>
                            <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">Módulo Taller</small>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">Estadísticas de Cursos</h3>
                        <p class="text-muted mb-0 mt-1 small">Indicadores globales de los programas de formación registrados en el sistema.</p>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-3 py-2 small" id="actualizado-label">
                        <i class="fas fa-sync-alt me-1"></i> Cargando...
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <form id="filtros-form" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Año</label>
                    <select name="anio" id="filtro-anio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($opciones['anios'] as $anio)
                            <option value="{{ $anio }}" @selected((int) $filtros['anio'] === (int) $anio)>{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Modalidad</label>
                    <select name="id_modalidad" id="filtro-modalidad" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($opciones['modalidades'] as $m)
                            <option value="{{ $m->id_modalidad }}" @selected((int) $filtros['id_modalidad'] === (int) $m->id_modalidad)>{{ $m->nombre_modalidad }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Estado</label>
                    <select name="id_estado" id="filtro-estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($opciones['estados'] as $e)
                            <option value="{{ $e['id'] }}" @selected((int) $filtros['id_estado'] === (int) $e['id'])>{{ $e['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Actividad Formativa</label>
                    <select name="id_actividad_formativa" id="filtro-actividad" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($opciones['actividades'] as $a)
                            <option value="{{ $a->id_actividad_formativa }}" @selected((int) $filtros['id_actividad_formativa'] === (int) $a->id_actividad_formativa)>{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Aspecto</label>
                    <select name="id_aspecto" id="filtro-aspecto" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($opciones['aspectos'] as $asp)
                            <option value="{{ $asp->id_aspecto }}" @selected((int) $filtros['id_aspecto'] === (int) $asp->id_aspecto)>{{ $asp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 w-100">
                        <i class="fas fa-filter me-1"></i> Aplicar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4" id="kpis-row"></div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-flag-checkered me-2 text-info"></i> Cursos por Estado</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="chart-estado"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 col-xl-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i> Evolución de Inscripciones</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="chart-inscripciones"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-video me-2 text-success"></i> Por Modalidad</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-modalidad"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i> Por Actividad Formativa</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-actividad"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-clapperboard me-2 text-danger"></i> Por Aspecto de Formación</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-aspecto"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-calendar me-2 text-secondary"></i> Cursos por Año</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-anio"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-layer-group me-2 text-info"></i> Por Trimestre</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-trimestre"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-map-marker-alt me-2 text-success"></i> Distribución Geográfica</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 260px;">
                        <canvas id="chart-geografia"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .chart-container canvas { max-width: 100%; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const PALETTE = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1',
            '#fd7e14', '#20c997', '#e83e8c', '#5a5c69', '#74b9ff', '#ff7675',
            '#00b894', '#d63031', '#0984e3', '#e17055', '#a29bfe', '#fab1a0'
        ];
        const charts = {};
        const URL_DATOS = @json(route('taller.estadisticas.datos'));

        const CHART_DEFAULTS = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        };

        function colores(n, alpha = 0.75) {
            return Array.from({ length: n }, (_, i) => {
                const base = PALETTE[i % PALETTE.length];
                return alpha < 1 ? hexToRgba(base, alpha) : base;
            });
        }

        function hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        function crearChart(id, config) {
            const ctx = document.getElementById(id);
            if (!ctx) return null;
            if (charts[id]) charts[id].destroy();
            charts[id] = new Chart(ctx, config);
            return charts[id];
        }

        function chartDona(labels, values) {
            return {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colores(labels.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: { ...CHART_DEFAULTS }
            };
        }

        function chartBarras(labels, values, colorBase = '#4e73df') {
            return {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad',
                        data: values,
                        backgroundColor: colores(labels.length, 0.75),
                        borderColor: colores(labels.length),
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...CHART_DEFAULTS,
                    plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            };
        }

        function chartLinea(labels, values) {
            return {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Inscripciones',
                        data: values,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.15)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#1cc88a',
                        pointRadius: 4
                    }]
                },
                options: {
                    ...CHART_DEFAULTS,
                    plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            };
        }

        function renderKpis(kpis) {
            const row = document.getElementById('kpis-row');
            const cards = [
                { icon: 'fa-graduation-cap', color: 'primary', label: 'Total Cursos', value: kpis.total_cursos },
                { icon: 'fa-user-check', color: 'success', label: 'Inscritos Aprobados', value: kpis.inscripciones_aprobadas },
                { icon: 'fa-users', color: 'info', label: 'Participantes Únicos', value: kpis.participantes_unicos },
                { icon: 'fa-award', color: 'warning', label: 'Certificados Aprobados', value: kpis.certificados_aprobados },
                { icon: 'fa-hourglass-half', color: 'secondary', label: 'Certificados Pendientes', value: kpis.certificados_pendientes },
                { icon: 'fa-times-circle', color: 'danger', label: 'Certificados Denegados', value: kpis.certificados_denegados },
                { icon: 'fa-chair', color: 'dark', label: 'Ocupación de Cupos', value: kpis.ocupacion_promedio + '%' }
            ];

            row.innerHTML = cards.map(c => `
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-${c.color} bg-opacity-10 text-${c.color}" style="width:48px;height:48px;flex-shrink:0;">
                                <i class="fas ${c.icon} fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-5">${c.value}</div>
                                <div class="text-muted small" style="font-size:0.8rem;">${c.label}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderCharts(data) {
            crearChart('chart-estado', chartDona(data.porEstado.labels, data.porEstado.values));
            crearChart('chart-inscripciones', chartLinea(data.inscripcionesTiempo.labels, data.inscripcionesTiempo.values));
            crearChart('chart-modalidad', chartBarras(data.porModalidad.labels, data.porModalidad.values, '#1cc88a'));
            crearChart('chart-actividad', chartBarras(data.porActividad.labels, data.porActividad.values, '#f6c23e'));
            crearChart('chart-aspecto', chartBarras(data.porAspecto.labels, data.porAspecto.values, '#e74a3b'));
            crearChart('chart-anio', chartBarras(data.porAnio.labels, data.porAnio.values, '#6f42c1'));
            crearChart('chart-trimestre', chartBarras(data.porTrimestre.labels, data.porTrimestre.values, '#36b9cc'));
            crearChart('chart-geografia', chartBarras(data.geografia.labels, data.geografia.values, '#20c997'));
        }

        function aplicarFiltros() {
            const params = new URLSearchParams();
            const mapeo = {
                'anio': 'anio',
                'id_modalidad': 'modalidad',
                'id_estado': 'estado',
                'id_actividad_formativa': 'actividad',
                'id_aspecto': 'aspecto'
            };
            Object.entries(mapeo).forEach(([param, sufijo]) => {
                const value = document.getElementById('filtro-' + sufijo).value;
                if (value !== '' && value !== '0') params.set(param, value);
            });

            const url = URL_DATOS + '?' + params.toString();
            const label = document.getElementById('actualizado-label');
            label.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cargando...';

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    renderKpis(data.kpis);
                    renderCharts(data);
                    label.innerHTML = '<i class="fas fa-check-circle me-1 text-success"></i> Actualizado';
                })
                .catch(err => {
                    label.innerHTML = '<i class="fas fa-exclamation-triangle me-1 text-danger"></i> Error al cargar';
                    console.error(err);
                });
        }

        document.getElementById('filtros-form').addEventListener('submit', function (e) {
            e.preventDefault();
            aplicarFiltros();
        });

        const datosIniciales = @json($datos);
        renderKpis(datosIniciales.kpis);
        renderCharts(datosIniciales);
        document.getElementById('actualizado-label').innerHTML = '<i class="fas fa-check-circle me-1 text-success"></i> Actualizado';
    });
</script>
@endsection
