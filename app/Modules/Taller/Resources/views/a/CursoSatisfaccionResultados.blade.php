@extends('layouts.kaiadmin-menu')

@section('title', 'Resultados de la Encuesta de Satisfacción - ' . $curso->nombre)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-chart-bar fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-dark mb-2">Resultados de la encuesta de satisfacción</h2>
                            <h5 class="text-primary fw-bold mb-0">{{ $curso->nombre }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 d-flex flex-wrap gap-2 justify-content-between">
                <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-outline-secondary rounded-pill fw-bold px-4">
                    <i class="fas fa-arrow-left me-2"></i> Volver al curso
                </a>
                @if($estadisticas !== null)
                    @can('reporteSatisfaccion', $curso)
                        <a href="{{ route('taller.cursos.satisfaccion.reporte', $curso->crypt_id) }}" class="btn btn-success rounded-pill fw-bold px-4">
                            <i class="fas fa-file-excel me-2"></i> Descargar reporte (Excel)
                        </a>
                    @endcan
                @endif
            </div>

            @if($estadisticas === null)
                <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                    <div class="card-body p-5 text-center">
                        <div class="bg-info-soft text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 72px; height: 72px;">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Aún no hay respuestas registradas</h4>
                        <p class="text-muted mb-0">Los resultados aparecerán aquí cuando se completar la encuesta de satisfacción.</p>
                    </div>
                </div>
            @else
                @php
                    $indiceGeneral = (float) $estadisticas['indice'];
                    $interpretacion = $indiceGeneral >= 4.5
                        ? 'Excelente'
                        : ($indiceGeneral >= 3.5
                            ? 'Muy bueno'
                            : ($indiceGeneral >= 2.5 ? 'Regular' : 'Deficiente'));
                @endphp

                <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-stretch">
                            <div class="col-md-6">
                                <div class="bg-primary-soft rounded-4 p-4 h-100 d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-primary shadow-xs flex-shrink-0" style="width: 52px; height: 52px;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1">Total de respuestas</small>
                                        <span class="h3 fw-bold text-dark mb-0">{{ number_format($estadisticas['total']) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-success-soft rounded-4 p-4 h-100 d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-success shadow-xs flex-shrink-0" style="width: 52px; height: 52px;">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1">Índice general</small>
                                        <span class="h3 fw-bold text-dark mb-0">{{ number_format($indiceGeneral, 2) }} <small class="text-muted fs-6 fw-normal">/ 5</small></span>
                                        <span class="badge bg-success-soft text-success ms-2">{{ $interpretacion }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($estadisticas['preguntas'] as $datos)
                    <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom py-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="fw-bold text-dark mb-0">{{ $datos['titulo'] }}</h5>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                    Promedio: {{ number_format($datos['promedio'], 2) }}
                                </span>
                                <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill">
                                    Favorable: {{ $datos['porcentaje'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @foreach($datos['distribucion'] as $nivel => $detalle)
                                @php
                                    $porcentajeNivel = $estadisticas['total'] > 0
                                        ? min(100, max(0, ($detalle['cantidad'] / $estadisticas['total']) * 100))
                                        : 0;
                                @endphp
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                                        <span class="fw-bold text-dark"><span class="badge bg-light text-primary border me-2 rounded-circle">{{ $nivel }}</span>{{ $detalle['etiqueta'] }}</span>
                                        <span class="text-muted small fw-bold">{{ $detalle['cantidad'] }} ({{ number_format($porcentajeNivel) }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 10px;" role="progressbar" aria-label="{{ $detalle['etiqueta'] }}" aria-valuenow="{{ $porcentajeNivel }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-primary rounded-pill" style="width: {{ $porcentajeNivel }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
