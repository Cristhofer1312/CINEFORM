@extends('layouts.kaiadmin-menu')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            
            <div class="row g-4">
                <!-- Columna Principal (8) -->
                <div class="col-lg-8">
                    <!-- Tarjeta Hero de Información Principal -->
                    <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                        <div class="card-header bg-white py-4 px-4 border-bottom">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('taller.cursos.index') }}" 
                                       class="btn btn-white btn-sm rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center hvr-push" 
                                       style="width: 40px; height: 40px;"
                                       title="Volver al listado">
                                        <i class="fas fa-arrow-left text-primary"></i>
                                    </a>
                                    <div>
                                        <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.75rem;">
                                            <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Explorar Cursos</a></li>
                                            <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">Ficha Técnica</li>
                                        </ol>
                                    </nav>
                                    <h2 class="fw-bold text-dark mb-0">{{ $curso->nombre }}</h2>
                                </div>
                            </div>
                                
                            @if(isset($inscripcion) && $inscripcion && $debeMostrarPromedio)
                                <div class="d-flex align-items-center bg-light p-2 px-3 rounded-pill border shadow-xs animate__animated animate__fadeInRight">
                                        <div class="text-end me-3">
                                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">Tu Promedio</small>
                                            <span class="fw-bold text-primary fs-5">{{ number_format($promedioEstudiante, 2) }}</span><small class="text-muted">/100</small>
                                        </div>
                                        <div class="icon-box {{ $promedioEstudiante >= 80 ? 'bg-success' : 'bg-warning' }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fas {{ $promedioEstudiante >= 80 ? 'fa-chart-line' : 'fa-exclamation-triangle' }} small"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Descripción -->
                            <div class="mb-5">
                                <h6 class="text-muted small fw-bold mb-3 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 text-primary opacity-50"></i> SÍNTESIS DEL PROGRAMA
                                </h6>
                                <p class="text-black mb-0 fs-5" style="line-height: 1.7;">
                                    {{ $curso->descripcion ?? 'Este programa académico no cuenta con una descripción detallada en este momento.' }}
                                </p>
                            </div>

                            <!-- Información Rápida en Columnas -->
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="p-3 rounded-4 bg-primary-soft border border-primary-soft h-100 d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-3 me-3 shadow-xs">
                                            <i class="fas fa-clock text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Duración</small>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $curso->duracion }} Días</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-4 bg-success-soft border border-success-soft h-100 d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-3 me-3 shadow-xs">
                                            <i class="fas fa-hourglass-half text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Intensidad</small>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $curso->horas }} Horas</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-4 bg-info-soft border border-info-soft h-100 d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-3 me-3 shadow-xs">
                                            <i class="fas fa-laptop text-info fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Modalidad</small>
                                            <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $curso->modalidad->nombre_modalidad ?? 'No especificada' }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Contenidos: Estilo Learning Path -->
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-layer-group me-2 text-primary opacity-50"></i> Itinerario del Programa
                            </h5>
                            <span class="badge bg-light text-muted border py-2 px-3 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                {{ $curso->contenidos->count() }} Módulos
                            </span>
                        </div>
                        <div class="card-body p-4 pt-1">
                            @if($curso->contenidos && $curso->contenidos->count() > 0)
                                <div class="curriculum-scroll-container pe-2" style="max-height: 450px; overflow-y: auto; scroll-behavior: smooth; overscroll-behavior: contain;">
                                    <div class="curriculum-path mt-3 position-relative ps-5 pb-2">
                                        <!-- Línea de conexión vertical -->
                                        <div class="path-line position-absolute h-100 bg-light rounded-pill" style="width: 4px; left: 42px; top: 0; opacity: 0.5;"></div>

                                    @foreach($curso->contenidos as $index => $contenido)
                                        <div class="path-item position-relative mb-4 animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 0.1 }}s;">
                                            <!-- Indicador de Punto -->
                                            <div class="path-marker position-absolute bg-white border border-4 rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center fw-bold transition-all z-index-1 {{ $contenido->es_evaluacion ? 'border-success text-success' : 'border-primary text-primary' }}" 
                                                 style="width: 32px; height: 32px; left: -30px; top: 10px; font-size: 0.75rem;">
                                                {{ $index + 1 }}
                                            </div>

                                            <!-- Card del Contenido -->
                                            <div class="card border-0 shadow-xs rounded-4 ms-3 transition-all hover-translate overflow-hidden border-start border-4 {{ $contenido->es_evaluacion ? 'border-success' : 'border-primary' }}">
                                                <div class="card-body p-3 ps-4">
                                                    <div class="row align-items-center g-3">
                                                        <div class="col-md-9 overflow-hidden">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <h6 class="mb-0 fw-bold text-dark fs-5 text-truncate">{{ $contenido->titulo }}</h6>
                                                                @if($contenido->es_evaluacion)
                                                                    <span class="badge bg-success text-white rounded-pill scale-80 ms-2 px-2" style="font-size: 0.6rem;">EVAL</span>
                                                                @else
                                                                    <span class="badge bg-primary text-white rounded-pill scale-80 ms-2 px-2" style="font-size: 0.6rem;">CONTENIDO</span>
                                                                @endif
                                                            </div>
                                                            <p class="mb-0 text-muted small opacity-75" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                                                {{ $contenido->descripcion_breve ?: 'Sin descripción del módulo.' }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            @if($contenido->fecha_contenido)
                                                                <span class="badge bg-white text-primary border rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                                                                    {{ $contenido->fecha_contenido->format('d/m/Y') }}
                                                                </span>
                                                            @endif
                                                            @if($contenido->es_evaluacion)
                                                                <div class="text-success fw-bold small mt-1">{{ $contenido->ponderacion }}%</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-route fa-3x text-muted opacity-25 mb-3"></i>
                                    <h6 class="fw-bold text-muted mb-0">Ruta de aprendizaje no definida.</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>



                <!-- Barra Lateral (4) -->
                <div class="col-lg-4">
                    <!-- Instructor -->
                    <div class="card border-0 shadow-card rounded-4 mb-4 text-center">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark">Facilitador</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mx-auto mb-3">
                                {!! renderAvatar($curso->persona, 'avatar-xxl', 'mx-auto') !!}
                            </div>
                            <h5 class="fw-bold text-dark mb-1">
                                @if($curso->persona)
                                    {{ $curso->persona->primer_nombre }} {{ $curso->persona->primer_apellido }}
                                @else
                                    Pendiente por asignar
                                @endif
                            </h5>
                            <p class="text-muted small mb-4">Instructor Titular</p>
                            
                            @if($curso->persona)
                                <button onclick="mostrarContactoProfesor('{{ $curso->persona->nombre_completo }}', '{{ $curso->persona->user->email ?? 'N/D' }}', '{{ $curso->persona->telefono ?? 'N/D' }}', '{{ $curso->persona->hasPhoto() ? route('show_avatar', $curso->persona->user->crypt_id) : '' }}')"
                                        class="btn btn-primary rounded-pill px-3 w-100 fw-bold shadow-sm transition-all hvr-push">
                                    <i class="fas fa-paper-plane me-2"></i> Contactar al Instructor
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Otros Detalles y Acciones -->
                    <div class="card border-0 shadow-card rounded-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark">Detalles Logísticos</h6>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small fw-bold text-uppercase"><i class="far fa-calendar-alt me-2 text-primary opacity-50"></i> Inicio</span>
                                    <span class="fw-bold text-dark">{{ $curso->fecha_inicio ? \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') : '--/--/----' }}</span>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small fw-bold text-uppercase"><i class="far fa-calendar-check me-2 text-success opacity-50"></i> Cierre</span>
                                    <span class="fw-bold text-dark">{{ $curso->fecha_fin ? \Carbon\Carbon::parse($curso->fecha_fin)->format('d/m/Y') : '--/--/----' }}</span>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3 d-flex flex-column border-bottom-light">
                                    <span class="text-muted small fw-bold text-uppercase mb-2"><i class="fas fa-map-marker-alt me-2 text-danger opacity-50"></i> Ámbito Geográfico</span>
                                    
                                    @if($curso->es_nacional)
                                        <div class="alert alert-info border-0 shadow-xs rounded-3 d-flex align-items-center mb-0 py-2">
                                            <i class="fas fa-globe-americas me-2"></i>
                                            <span class="fw-bold small">ALCANCE NACIONAL</span>
                                        </div>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($curso->localidades as $loc)
                                                <span class="badge bg-light text-dark border fw-normal">{{ $loc->description }}</span>
                                            @empty
                                                <span class="text-muted small italic">No definido</span>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center border-bottom-light">
                                    <span class="text-muted small fw-bold text-uppercase"><i class="fas fa-users me-2 text-warning opacity-50"></i> Cupos</span>
                                    @if($curso->cantidad_cupos !== null)
                                        <span class="badge {{ $CuposDisponibles > 0 ? 'bg-primary' : 'bg-danger' }} rounded-pill px-3">{{ $CuposDisponibles }} disponibles de {{ $curso->cantidad_cupos }}</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3">Cupos Ilimitados</span>
                                    @endif
                                </div>
                                @if($curso->telegram)
                                <div class="list-group-item border-0 px-0 py-3 d-flex flex-column align-items-start border-bottom-light">
                                    <span class="text-muted small fw-bold text-uppercase mb-2"><i class="fab fa-telegram-plane me-2 text-info opacity-50"></i> Comunicación</span>
                                    <a href="{{ $curso->telegram }}" target="_blank" class="btn btn-info btn-sm w-100 rounded-pill fw-bold text-white shadow-sm transition-all hvr-push">
                                        Unirse al Telegram
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-light bg-opacity-50 border-0 p-4 rounded-bottom-4">
                            @auth
                                    @include('taller::a.partials.curso-actions.actions-container', [
                                        'curso' => $curso,
                                        'capacidades' => $capacidades,
                                        'inscripcion' => $inscripcion
                                    ])
                                <div class="text-center mt-3">
                                    <a href="{{ route('taller.cursos.index') }}" class="text-muted text-decoration-none small fw-bold hvr-underline-from-left">
                                        Cursos disponibles <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-primary-soft { background-color: rgba(30, 58, 138, 0.05); }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.05); }
    .bg-info-soft { background-color: rgba(14, 165, 233, 0.05); }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1); }
    .border-bottom-light { border-bottom: 1px solid #f1f5f9; }
    .hover-bg-light:hover { background-color: #fafbfc; }
    
    .shadow-card { box-shadow: 0 10px 40px rgba(0,0,0,0.06); }
    .shadow-xs { box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    
    .hvr-push { transition: transform 0.2s; }
    .hvr-push:active { transform: scale(0.97); }
    
    .hvr-underline-from-left:hover { color: #1e3a8a !important; }
    
    .hover-translate:hover { transform: translateX(8px); background-color: #fff !important; box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important; }
    .scale-80 { transform: scale(0.85); transform-origin: left; }
    .path-item { transition: all 0.3s ease; }

    /* Custom scrollbar para itinerario */
    .curriculum-scroll-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(13, 110, 253, 0.4) transparent;
    }
    .curriculum-scroll-container::-webkit-scrollbar { width: 5px !important; }
    .curriculum-scroll-container::-webkit-scrollbar-track { background: transparent !important; }
    .curriculum-scroll-container::-webkit-scrollbar-thumb { background: rgba(13, 110, 253, 0.4) !important; border-radius: 10px !important; }
    .curriculum-scroll-container::-webkit-scrollbar-thumb:hover { background: rgba(13, 110, 253, 0.8) !important; }

    /* ── Modal Contacto Facilitador (SweetAlert2) ── */
    .swal-wide-card {
        padding: 0 !important;
        border-radius: 20px !important;
        overflow: visible !important;
        background: transparent !important;
        box-shadow: none !important;
        max-width: 92vw !important;
    }
    .swal-wide-card .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }
    .swal-close-btn-custom {
        color: white !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: 24px !important;
        z-index: 10 !important;
        position: absolute !important;
        top: 8px !important;
        right: 8px !important;
    }
    .swal-close-btn-custom:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        border-radius: 50% !important;
    }
    /* Padding del contenedor para que no se corte en zoom alto */
    .swal2-container {
        padding: 10px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Datos de observaciones inyectados desde el servidor
    const observacionesCurso = @json($curso->observaciones ?? []);

    function verObservaciones(cursoId) {
        if (!observacionesCurso || observacionesCurso.length === 0) {
            Swal.fire({
                html: `<div class="p-2">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <h4 class="fw-bold text-dark">Sin Observaciones</h4>
                    <p class="text-muted">No hay observaciones registradas para este curso.</p>
                </div>`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#1e3a8a',
                customClass: { popup: 'rounded-4' }
            });
            return;
        }
        mostrarListaObservaciones();
    }

    function mostrarListaObservaciones() {
        let tarjetasHtml = observacionesCurso.map((obs, i) => {
            const fecha = obs.created_at ? new Date(obs.created_at).toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
            const preview = obs.observacion.length > 80 ? obs.observacion.substring(0, 80) + '...' : obs.observacion;
            return `
                <div class="obs-card d-flex align-items-center p-3 mb-2 rounded-3 border bg-white text-start" 
                     style="cursor: pointer; transition: all 0.2s ease;" 
                     onclick="mostrarDetalleObservacion(${i})"
                     onmouseenter="this.style.transform='translateX(6px)'; this.style.boxShadow='0 4px 15px rgba(30,58,138,0.12)'; this.style.borderColor='#2563eb';"
                     onmouseleave="this.style.transform='none'; this.style.boxShadow='none'; this.style.borderColor='#dee2e6';">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.75rem;">
                        ${i + 1}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="mb-0 text-dark small fw-semibold text-truncate" style="line-height: 1.4;">${preview}</p>
                        <small class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i>${fecha}</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted ms-2 opacity-50" style="font-size: 0.7rem;"></i>
                </div>`;
        }).join('');

        Swal.fire({
            html: `<div class="p-2">
                <i class="fas fa-clipboard-list fa-3x text-warning mb-3"></i>
                <h4 class="fw-bold text-dark">Historial de Revisiones</h4>
                <p class="text-muted small mb-3">Selecciona una observación para ver el detalle completo.</p>
                <div style="max-height: 350px; overflow-y: auto; padding-right: 4px;">
                    ${tarjetasHtml}
                </div>
                <small class="text-muted d-block mt-3 opacity-50">${observacionesCurso.length} observación(es) registrada(s)</small>
            </div>`,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-4' },
            width: 480
        });
    }

    function mostrarDetalleObservacion(index) {
        const obs = observacionesCurso[index];
        const fecha = obs.created_at ? new Date(obs.created_at).toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
        const autor = obs.autor ? (obs.autor.username || obs.autor.name || '') : '';

        Swal.fire({
            html: `<div class="p-2">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 0.9rem;">
                        ${index + 1}
                    </div>
                </div>
                <h5 class="fw-bold text-dark mb-1">Observación #${index + 1}</h5>
                <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                    <small class="text-muted"><i class="far fa-clock me-1"></i>${fecha}</small>
                    ${autor ? `<small class="text-muted"><i class="far fa-user me-1"></i>${autor}</small>` : ''}
                </div>
                <div class="text-start bg-light p-4 rounded-4 mt-2" style="border: 1px solid #eee;">
                    <p class="mb-0 text-dark" style="line-height: 1.7; font-size: 0.95rem;">${obs.observacion}</p>
                </div>
            </div>`,
            showCancelButton: observacionesCurso.length > 1,
            confirmButtonText: 'Cerrar',
            cancelButtonText: '<i class="fas fa-arrow-left me-1"></i> Volver al listado',
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#6c757d',
            customClass: { popup: 'rounded-4' },
            width: 500
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.cancel) {
                mostrarListaObservaciones();
            }
        });
    }

    // Legacy: mantener compatibilidad con partials viejos que aún llaman verMotivoRechazo
    function verMotivoRechazo(cursoId, motivo, cursoNombre = '') {
        Swal.fire({
            html: `<div class="p-2">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h4 class="fw-bold text-dark">Propuesta Declinada</h4>
                <div class="text-start bg-light p-3 rounded-4 mt-3" style="border: 1px solid #eee;">
                    <small class="text-muted fw-bold d-block mb-1">OBSERVACIÓN:</small>
                    <p class="mb-0 text-secondary">${motivo || 'No se suministró motivo.'}</p>
                </div>
            </div>`,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#1e3a8a',
            customClass: { popup: 'rounded-4' }
        });
    }

    function mostrarContactoProfesor(nombre, email, telefono, avatarUrl = '') {
    // Generar iniciales del nombre
    const partes = nombre.trim().split(' ');
    const iniciales = partes.length >= 2
        ? (partes[0][0] + partes[partes.length - 1][0]).toUpperCase()
        : nombre.substring(0, 2).toUpperCase();

    // Determinar qué mostrar en el avatar (Foto o Iniciales)
    const avatarHtml = avatarUrl 
        ? `<img src="${avatarUrl}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.6);">`
        : `<span style="color: #fff; font-size: 1.4rem; font-weight: 800;">${iniciales}</span>`;

    Swal.fire({
        width: 'min(500px, 95vw)',
        padding: 0,
        background: 'transparent',
        heightAuto: false,
        showConfirmButton: false,
        showCloseButton: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        html: `
            <div style="border-radius: 24px; overflow: hidden; font-family: 'Inter', -apple-system, sans-serif; background: #fff; box-shadow: 0 30px 70px rgba(0,0,0,0.2); width: 100%; position: relative; max-height: 90vh; overflow-y: auto;">

                <!-- Header con gradiente + Avatar integrado -->
                <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding: 35px 24px 40px; text-align: center;">
                    <p style="color: rgba(255,255,255,0.8); font-size: 0.75rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; margin: 0 0 6px;">Información de Contacto</p>
                    <h4 style="color: #fff; font-weight: 800; margin: 0 0 20px; font-size: 1.2rem;">Facilitador del Curso</h4>

                    <!-- Avatar dentro del header -->
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: ${avatarUrl ? 'transparent' : 'rgba(255,255,255,0.2)'}; border: ${avatarUrl ? 'none' : '4px solid rgba(255,255,255,0.6)'}; display: inline-flex; align-items: center; justify-content: center; backdrop-filter: ${avatarUrl ? 'none' : 'blur(4px)'}; margin: 0 auto; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                        ${avatarHtml}
                    </div>
                </div>

                <!-- Nombre y título -->
                <div style="text-align: center; padding: 20px 24px 15px;">
                    <h5 style="font-weight: 800; color: #0f172a; margin: 0 0 6px; font-size: 1.3rem;">${nombre}</h5>
                    <span style="background: #eff6ff; color: #2563eb; border-radius: 25px; font-size: 0.75rem; font-weight: 700; padding: 4px 16px; letter-spacing: 0.5px;">INSTRUCTOR TITULAR</span>
                </div>

                <!-- Datos de contacto -->
                <div style="padding: 0 30px 30px;">

                    <!-- Email -->
                    <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px 20px; margin-bottom: 12px; transition: all 0.2s;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 15px;">
                            <i class="fas fa-envelope" style="color: #2563eb; font-size: 1rem;"></i>
                        </div>
                        <div style="overflow: hidden; flex: 1; text-align: left;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Correo electrónico</div>
                            <div style="font-weight: 600; color: #1e293b; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${email}</div>
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px 20px; margin-bottom: 5px; transition: all 0.2s;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 15px; font-size: 1.3rem;">
                            📱
                        </div>
                        <div style="flex: 1; text-align: left;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Teléfono de Contacto</div>
                            <div style="font-weight: 600; color: #1e293b; font-size: 1.15rem;">${telefono}</div>
                        </div>
                    </div>

                </div>
            </div>
        `,
        customClass: {
            popup: 'swal-wide-card',
            closeButton: 'swal-close-btn-custom'
        },
        didOpen: () => {
            // Ajustar posición del botón de cierre
            const closeBtn = Swal.getPopup().querySelector('.swal2-close');
            if (closeBtn) {
                closeBtn.style.zIndex = '9999';
                closeBtn.style.color = '#fff';
                closeBtn.style.fontSize = '24px';
                closeBtn.style.padding = '10px';
                closeBtn.style.right = '5px';
                closeBtn.style.top = '5px';
            }
        }
    });
}

    function processStatusAction(idEstado, title, successMsg) {
        fetch('{{ route("taller.cursos.updateStatus", ["curso" => $curso->crypt_id]) }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ id_estado: idEstado })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: title, text: successMsg, timer: 3000, showConfirmButton: false }).then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        });
    }

    function aprobarCurso(id) { processStatusAction(6, 'Aprobado', 'El curso ahora está público.'); }
    function finalizarEdicion(id) { processStatusAction(5, 'Solicitud Enviada', 'La propuesta ha sido enviada.'); }
    function finalizarInscripciones(id) { processStatusAction(7, 'Inscripciones Finalizadas', 'Proceso de captación cerrado.'); }
    function finalizarCurso(id) { processStatusAction(8, 'Curso Finalizado', 'Programa concluido.'); }
    function cerrarCurso(id) { processStatusAction(9, 'Curso Cerrado', 'Programa archivado.'); }

    function aceptarCursoFacilitador(id) {
        if(event) event.target.disabled = true;
        processStatusAction(4, 'Aceptado', 'Has aceptado la asignación.');
    }

    function rechazarContenido(idCurso) {
        Swal.fire({
            title: 'Declinar Propuesta',
            input: 'textarea',
            inputPlaceholder: 'Motivos...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            preConfirm: (motivo) => {
                if (!motivo) return Swal.showValidationMessage('Debe ingresar un motivo');
                return fetch('{{ route("taller.cursos.updateStatus", ["curso" => $curso->crypt_id]) }}', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_estado: 3, motivo: motivo })
                }).then(r => r.json()).then(d => { if(!d.success) throw new Error(d.message); return d; });
            }
        }).then((result) => { if (result.isConfirmed) window.location.reload(); });
    }

    function inscribirAlCurso(id) {
        Swal.fire({
            title: '¿Confirmar inscripción?',
            text: "Se registrará tu participación en este programa.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, inscribirme',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('{{ route("taller.inscripciones.store") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ id_curso: '{{ $curso->crypt_id }}' })
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Inscripción Exitosa!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo procesar',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo comunicar con el servidor.'
                    });
                });
            }
        });
    }

    function cancelarInscripcion(id) {
        Swal.fire({
            title: '¿Cancelar inscripción?',
            text: "Podrás inscribirte de nuevo si aún hay cupos disponibles.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Mantener'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('taller/inscripciones') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Inscripción Cancelada',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo procesar la cancelación.'
                    });
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar tooltips y listeners de botones por clase
        document.querySelectorAll('.inscribir-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-curso-id');
                inscribirAlCurso(id);
            });
        });
    });


</script>
@endpush
@endsection