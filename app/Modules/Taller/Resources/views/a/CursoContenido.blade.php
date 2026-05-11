@extends('layouts.kaiadmin-menu')

@section('title', 'Contenido del Curso')

{{--
Vista: CursoContenido
Descripción: Muestra el reproductor/visor de contenido para un curso específico.
Incluye una barra lateral de navegación entre lecciones y un área principal para el contenido seleccionado.
--}}

@section('content')
    {{-- Contenedor fluido con altura mínima para asegurar footer al fondo --}}
    <div class="container-fluid py-4" style="min-height: 85vh;">
        <div class="mb-3">
            <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-light shadow-sm border">
                <i class="fas fa-arrow-left me-2"></i> Volver a Detalles del Curso
            </a>
        </div>
        <div class="row">
            <!-- Sidebar de Navegación (Izquierda en pantallas grandes) -->
            <!-- Sidebar de Navegación (Derecha) -->
            <div class="col-lg-4 order-lg-2 mb-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-4 px-4 text-white border-0">
                        <h5 class="mb-1 fw-bold text-white"><i class="fas fa-list-ul me-2"></i> Contenido del Curso</h5>
                        <p class="mb-0 small opacity-75 text-white">{{ $curso->nombre }}</p>
                    </div>
                    <div class="card-body p-3 bg-light">
                        <div class="list-group list-group-flush rounded-3 overflow-hidden shadow-sm" style="max-height: 600px; overflow-y: auto;">
                            @forelse($curso->contenidos as $index => $contenido)
                                @php
                                    $esActivo = $contenidoActual && $contenido->id_contenido_curso == $contenidoActual->id_contenido_curso;
                                    $icono = 'fa-file-alt';
                                    $urlStr = strtolower($contenido->url_contenido ?? '');
                                    if (str_contains($urlStr, 'youtube') || str_contains($urlStr, 'vimeo') || str_contains($urlStr, '.mp4')) {
                                        $icono = 'fa-play-circle';
                                    } elseif (str_contains($urlStr, '.pdf') || str_contains($urlStr, 'drive.google.com') || str_contains($urlStr, '.doc') || str_contains($urlStr, '.zip')) {
                                        $icono = 'fa-file-download';
                                    }

                                    if ($contenido->es_evaluacion) $icono = 'fa-clipboard-check';
                                @endphp
                                <a href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id, 'contenido_id' => $contenido->crypt_id]) }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-2 rounded-3 py-3 px-3 shadow-sm {{ $esActivo ? 'active-lesson-shadow border-start border-primary border-4' : 'bg-white' }}">
                                    <div class="lesson-number me-3 text-center rounded-circle {{ $esActivo ? 'bg-primary text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; line-height: 32px; font-weight: bold; font-size: 0.85rem; transition: all 0.3s;">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="mb-0 fw-bold text-dark" style="font-size: 0.95rem; line-height: 1.2;">
                                                {{ $contenido->titulo }}
                                            </span>
                                            @if($contenido->es_evaluacion)
                                                <span class="badge bg-warning text-dark ms-2 shadow-xs" style="font-size: 0.7rem; padding: 0.35em 0.65em;">
                                                    {{ $contenido->ponderacion ?? '0' }}%
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center mt-1">
                                            <i class="fas {{ $icono }} me-2 text-primary opacity-75" style="font-size: 0.8rem;"></i>
                                            <small class="text-secondary text-truncate" style="max-width: 180px; font-size: 0.8rem;">
                                                {{ $contenido->descripcion_breve ?? 'Lección académica' }}
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-5 text-center bg-white">
                                    <i class="fas fa-ghost fa-3x text-light mb-3"></i>
                                    <p class="text-muted mb-0">No hay módulos disponibles en este curso.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área Principal de Contenido -->
            <div class="col-lg-8 order-lg-1">
                @if($contenidoActual)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="h3 mb-3">{{ $contenidoActual->titulo }}</h2>

                            @php
                                // Determinar el tipo de contenido y configurar el botón de acción
                                $urlLower = strtolower($contenidoActual->url_contenido ?? '');
                                $tipo = 'clase';
                                $hasUrl = !empty($contenidoActual->url_contenido);
                                if ($hasUrl) {
                                    $tipo = 'enlace';
                                    if (str_contains($urlLower, 'youtube') || str_contains($urlLower, 'vimeo') || str_contains($urlLower, '.mp4')) {
                                        $tipo = 'video';
                                    } elseif (str_contains($urlLower, '.pdf') || str_contains($urlLower, 'drive.google.com') || str_contains($urlLower, '.doc') || str_contains($urlLower, '.zip')) {
                                        $tipo = 'archivo';
                                    }
                                }
                                $url = $contenidoActual->url_contenido;

                                // Configuración del botón
                                $btnClass = 'btn-primary';
                                $btnIcon = 'fa-external-link-alt';
                                $btnText = 'Contenido sugerido';
                                $showBtn = $hasUrl; // Solo mostrar si hay URL

                                // Personalización según tipo
                                if ($tipo == 'video') {
                                    $btnClass = 'btn-danger';
                                    $btnIcon = 'fa-play';
                                } elseif ($tipo == 'archivo') {
                                    $btnClass = 'btn-info text-white';
                                    $btnIcon = 'fa-download';
                                }

                                // Personalización para evaluaciones
                                if ($contenidoActual->es_evaluacion) {
                                    $tipoNombre = $contenidoActual->tipoEvaluacion ? $contenidoActual->tipoEvaluacion->nombre : 'Evaluación';
                                    $tipo = $tipoNombre;

                                    if (isset($esFacilitador) && $esFacilitador) {
                                        $btnClass = 'btn-primary text-white';
                                        $btnIcon = 'fa-check-double';
                                        $btnText = 'Calificar ' . $tipoNombre;
                                        $url = route('taller.calificaciones.index', ['curso' => $curso->crypt_id, 'contenido' => $contenidoActual->crypt_id]);
                                        $showBtn = true; // Siempre mostrar para facilitador evaluando
                                    } else {
                                        if ($tipo == 'video') {
                                            $btnClass = 'btn-danger';
                                            $btnIcon = 'fa-play';
                                            $btnText = 'Ver Video';
                                        } elseif ($tipo == 'archivo') {
                                            $btnClass = 'btn-info text-white';
                                            $btnIcon = 'fa-download';
                                        }
                                    }
                                }
                            @endphp

                            <!-- Encabezado Estilizado -->
                            <div class="row align-items-center mb-5 g-3">
                                <div class="col-md-9">

                                    <div class="d-flex flex-wrap gap-3 mt-3">
                                        <div class="px-3 py-2 bg-light border rounded-3 d-flex align-items-center">
                                            <i class="fas fa-tag text-primary me-2"></i>
                                            <span class="small fw-bold">{{ ucfirst($tipo) }}</span>
                                        </div>
                                        @if($contenidoActual->es_evaluacion)
                                            <div class="px-3 py-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 d-flex align-items-center shadow-sm">
                                                <span class="small text-black fw-bold">Ponderación: <span class="text-white">{{ $contenidoActual->ponderacion ?? '0' }}%</span></span>
                                            </div>
                                        @else
                                            <div class="px-3 py-2 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 d-flex align-items-center">
                                                <span class="small text-black fw-bold">Material didáctico</span>
                                            </div>
                                        @endif
                                        
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    @if($showBtn)
                                        <a href="{{ $url }}" class="btn {{ $btnClass }} btn-lg rounded-pill shadow-sm btn-action w-100">
                                            <i class="fas {{ $btnIcon }} me-2"></i> {{ $btnText }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Resultado de Evaluación (Solo Estudiantes con nota) -->
                            @if(isset($calificacion) && $calificacion)
                                <div
                                    class="alert {{ $calificacion->calificacion >= 60 ? 'alert-success' : 'alert-danger' }} border-0 shadow-sm mt-4 rounded-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="h3 mb-0 me-3">
                                            @if($calificacion->calificacion >= 60)
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="alert-heading fw-bold mb-0">Evaluación Calificada</h5>
                                            <p class="mb-0 text-muted small">Fecha de calificación:
                                                {{ \Carbon\Carbon::parse($calificacion->actualizado_en ?? $calificacion->creado_en)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <span class="display-4 fw-bold">{{ floatval($calificacion->calificacion) }}/100</span>
                                            <span class="text-muted d-block small">Nota Final</span>
                                        </div>
                                    </div>

                                    @if($calificacion->observacion)
                                        <hr>
                                        <p class="mb-1 fw-bold"><i class="fas fa-comment-alt me-2"></i>Feedback del Facilitador:</p>
                                        <p class="mb-0 fst-italic">{{ $calificacion->observacion }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Descripción y Detalles -->
                            <div class="card bg-light border-0 rounded-4 mt-5">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3 text-primary d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2"></i> Sobre este contenido
                                    </h5>
                                    <div class="text-muted" style="font-size: 1rem; line-height: 1.7;">
                                        {!! nl2br(e($contenidoActual->descripcion)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center"
                            style="width: 100px; height: 100px;">
                            <i class="fas fa-play fa-2x text-muted"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Selecciona un contenido</h4>
                        <p class="text-muted text-center" style="max-width: 400px; margin: 0 auto;">Elige una lección del menú
                            de navegación para comenzar tu aprendizaje.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .card {
                border-radius: 20px;
                border: none;
                transition: all 0.3s ease;
            }

            /* Estilo del Sidebar LMS */
            .list-group-item {
                border: none;
                transition: all 0.2s ease;
                background-color: #fff;
            }

            .list-group-item:hover {
                background-color: #f1f4f9;
                transform: scale(1.02);
            }

            .list-group-item .text-dark {
                color: #212529 !important;
            }

            .active-lesson-shadow {
                background-color: #f8fbff !important;
                box-shadow: 0 10px 25px rgba(30, 58, 138, 0.1) !important;
                transform: translateX(5px);
            }

            .lesson-number {
                transition: all 0.3s ease;
            }

            /* Áreas de contenido */
            .btn-action {
                transition: all 0.3s ease;
                font-weight: 700;
            }

            .btn-action:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
            }

            /* Scrollbar personalizado */
            .list-group-flush::-webkit-scrollbar { width: 5px; }
            .list-group-flush::-webkit-scrollbar-track { background: transparent; }
            .list-group-flush::-webkit-scrollbar-thumb { background: #dce1eb; border-radius: 10px; }
            .list-group-flush::-webkit-scrollbar-thumb:hover { background: #cbd5e0; }
        </style>
    @endpush
@endsection