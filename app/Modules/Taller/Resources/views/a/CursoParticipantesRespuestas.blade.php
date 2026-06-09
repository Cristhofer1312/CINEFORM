@extends('layouts.kaiadmin-menu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Explorar Cursos</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="text-decoration-none text-muted">Ficha Técnica</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.participantes', $curso->id_curso) }}" class="text-decoration-none text-muted">Participantes</a></li>
                        <li class="breadcrumb-item active text-info fw-bold">Requisitos Entregados</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-dark mb-0">Requisitos del Participante</h2>
                <p class="text-muted small mb-0"><i class="fas fa-user text-info me-1"></i> Participante: <strong>{{ $inscripcion->persona->primer_nombre }} {{ $inscripcion->persona->primer_apellido }}</strong></p>
            </div>
            <div class="text-end">
                <a href="{{ route('taller.cursos.participantes', $curso->id_curso) }}"
                    class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-2 text-primary"></i> Volver a Participantes
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
            <div class="card-header text-white border-bottom py-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                <h5 class="mb-0 fw-bold"><i class="fas fa-tasks text-white me-2"></i> Requisitos Presentados para: {{ $curso->nombre }}</h5>
            </div>
            <div class="card-body bg-light bg-opacity-50 p-4">
                
                @if($inscripcion->respuestas->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                        <h5 class="fw-bold text-dark">Sin Requisitos</h5>
                        <p class="text-muted">Este participante no ha entregado respuestas o documentos adicionales.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($inscripcion->respuestas as $resp)
                            @php $req = $resp->requisito; @endphp
                            @if($req)
                                <div class="col-12">
                                    <div class="card border border-light-2 shadow-sm rounded-4 bg-white">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($req->tipo === 'pregunta')
                                                    <i class="fas fa-question-circle text-info fs-4 me-3"></i>
                                                @elseif($req->tipo === 'documento')
                                                    <i class="fas fa-file-alt text-warning fs-4 me-3"></i>
                                                @endif
                                                <h5 class="fw-bold text-dark mb-0">{{ $req->titulo }}</h5>
                                            </div>
                                            
                                            <div class="bg-light p-4 rounded-3 mt-3 border">
                                                @if($req->tipo === 'pregunta')
                                                    <p class="mb-0 text-secondary fs-6" style="white-space: pre-wrap;">{{ $resp->respuesta_texto ?: 'No respondió' }}</p>
                                                @elseif($req->tipo === 'documento')
                                                    @if($resp->ruta_archivo)
                                                        @php
                                                            $ext = strtolower(pathinfo($resp->ruta_archivo, PATHINFO_EXTENSION));
                                                            $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                            $urlArchivo = route('taller.inscripciones.respuestas.ver', $resp->id_respuesta);
                                                        @endphp
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas {{ $esImagen ? 'fa-image text-info' : 'fa-file-pdf text-danger' }} fa-2x me-3"></i>
                                                                <div>
                                                                    <h6 class="mb-0 fw-bold">{{ $esImagen ? 'Imagen Adjunta' : 'Documento PDF Adjunto' }}</h6>
                                                                    <span class="text-muted small">Cargado exitosamente · {{ strtoupper($ext) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="button"
                                                                    class="btn btn-outline-primary rounded-pill hvr-push px-4 fw-bold shadow-sm"
                                                                    onclick="abrirPreview('{{ $urlArchivo }}', '{{ $esImagen ? 'imagen' : 'pdf' }}', '{{ $req->titulo }}')"
                                                                    title="Vista previa en pantalla">
                                                                    <i class="fas fa-eye me-2"></i> Ver Documento
                                                                </button>
                                                                <a href="{{ route('taller.inscripciones.respuestas.descargar', $resp->id_respuesta) }}"
                                                                   class="btn btn-primary rounded-pill shadow-sm hvr-push px-4 fw-bold"
                                                                   title="Forzar descarga al dispositivo">
                                                                    <i class="fas fa-download me-2"></i> Descargar
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <p class="mb-0 text-muted fst-italic">No se subió archivo.</p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</div>

{{-- Modal de Vista Previa --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 90vw; width: 90vw;">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                <h5 class="modal-title text-white fw-bold" id="previewModalLabel">
                    <i class="fas fa-eye me-2"></i> <span id="preview-title">Vista Previa</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 80vh;">
                {{-- Contenido inyectado por JS --}}
                <div id="preview-container" class="w-100 h-100 d-flex align-items-center justify-content-center" style="min-height: 80vh;">
                    <div class="text-white opacity-50">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-light-2 { border: 1px solid #e2e8f0; }
    .shadow-card { box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    #previewModal .modal-body {
        background: #1a1a2e;
    }
    #preview-container iframe,
    #preview-container img {
        max-height: 80vh;
        max-width: 100%;
    }
</style>
@endpush

@push('scripts')
<script>
    function abrirPreview(url, tipo, titulo) {
        document.getElementById('preview-title').textContent = titulo;
        const container = document.getElementById('preview-container');
        
        if (tipo === 'imagen') {
            container.innerHTML = `
                <div class="text-center p-4">
                    <img src="${url}" 
                         class="rounded-3 shadow-lg" 
                         style="max-height: 78vh; max-width: 100%; object-fit: contain;"
                         alt="${titulo}">
                </div>`;
        } else {
            // PDF usando iframe
            container.innerHTML = `
                <iframe src="${url}#toolbar=1&navpanes=0&scrollbar=1&view=FitH"
                        style="width: 100%; height: 80vh; border: none;"
                        title="${titulo}">
                    <div class="p-5 text-center text-white">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3 d-block text-warning"></i>
                        <p>Tu navegador no puede previsualizar este PDF.</p>
                        <a href="${url}" target="_blank" class="btn btn-outline-light rounded-pill">Abrir en nueva pestaña</a>
                    </div>
                </iframe>`;
        }

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        // Limpiar al cerrar
        document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
            container.innerHTML = '<div class="text-white opacity-50"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        }, { once: true });

        modal.show();
    }
</script>
@endpush

@endsection
