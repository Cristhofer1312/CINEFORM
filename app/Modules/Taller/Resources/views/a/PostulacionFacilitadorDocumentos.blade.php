@extends('layouts.kaiadmin-menu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Alertas --}}
        @if(session('success'))
            <x-taller.alert type="success" title="Éxito">
                {{ session('success') }}
            </x-taller.alert>
        @endif
        @if(session('error'))
            <x-taller.alert type="danger" title="Atención">
                {{ session('error') }}
            </x-taller.alert>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-file-alt me-2 text-primary"></i> Documentos de Postulación
                </h4>
                <p class="text-muted mb-0">
                    {{ $postulacion->persona->primer_nombre }} {{ $postulacion->persona->primer_apellido }}
                    — Cédula: {{ $postulacion->persona->dni }}
                </p>
            </div>
            <a href="{{ route('taller.postulacion-facilitador.admin') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        {{-- Estado de la postulación --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted mb-1">Estado</h6>
                        @if($postulacion->esPendiente())
                            <span class="badge bg-warning fs-6">Pendiente</span>
                        @elseif($postulacion->esAprobada())
                            <span class="badge bg-success fs-6">Aprobada</span>
                        @else
                            <span class="badge bg-danger fs-6">Rechazada</span>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            Enviada: {{ $postulacion->creado_en->format('d/m/Y H:i') }}
                            @if($postulacion->fecha_revision)
                                <br>Revisada: {{ $postulacion->fecha_revision->format('d/m/Y H:i') }}
                            @endif
                        </small>
                    </div>
                </div>
                @if($postulacion->motivo_rechazo)
                <div class="alert alert-warning mt-3 mb-0">
                    <strong><i class="fas fa-exclamation-triangle me-1"></i> Motivo de rechazo:</strong>
                    {{ $postulacion->motivo_rechazo }}
                </div>
                @endif
            </div>
        </div>

        {{-- Respuestas y documentos --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-clipboard-list me-2"></i> Requisitos Entregados</h6>
            </div>
            <div class="card-body p-4">
                @forelse($postulacion->respuestas as $respuesta)
                    <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fas fa-{{ $respuesta->requisito->tipo === 'documento' ? 'file-pdf text-danger' : ($respuesta->requisito->tipo === 'pregunta' ? 'question-circle text-info' : 'info-circle text-success') }} me-1"></i>
                            {{ $respuesta->requisito->titulo }}
                        </h6>

                        @if($respuesta->respuesta_texto)
                            <div class="bg-light rounded-3 p-3">
                                <p class="mb-0">{{ $respuesta->respuesta_texto }}</p>
                            </div>
                        @endif

                        @if($respuesta->ruta_archivo)
                            <div class="d-flex align-items-center mt-2">
                                <i class="fas fa-paperclip me-2 text-muted"></i>
                                <span class="text-muted me-3">{{ basename($respuesta->ruta_archivo) }}</span>
                                <a href="{{ route('taller.postulacion-facilitador.documento.descargar', $respuesta->id_respuesta) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Descargar
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Esta postulación no tiene respuestas registradas.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Acciones (solo si está pendiente) --}}
        @if($postulacion->esPendiente())
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3">Acciones</h6>
                <div class="d-flex gap-3">
                    <form action="{{ route('taller.postulacion-facilitador.aprobar', $postulacion->crypt_id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de aprobar esta postulación? El usuario recibirá el perfil de Facilitador.')">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill">
                            <i class="fas fa-check me-1"></i> Aprobar
                        </button>
                    </form>
                    <button class="btn btn-danger fw-bold px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                        <i class="fas fa-times me-1"></i> Rechazar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Rechazar --}}
        <div class="modal fade" id="modalRechazar" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('taller.postulacion-facilitador.rechazar', $postulacion->crypt_id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2 text-danger"></i> Rechazar Postulación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p>¿Estás seguro de rechazar la postulación de <strong>{{ $postulacion->persona->primer_nombre }} {{ $postulacion->persona->primer_apellido }}</strong>?</p>
                            <div class="alert alert-warning mb-3">
                                <small><i class="fas fa-info-circle me-1"></i> Los documentos se eliminarán del storage. El participante podrá volver a postularse.</small>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Motivo del rechazo <span class="text-danger">*</span></label>
                                <textarea name="motivo_rechazo" class="form-control" rows="3" required placeholder="Describe las observaciones..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4">Rechazar Postulación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection