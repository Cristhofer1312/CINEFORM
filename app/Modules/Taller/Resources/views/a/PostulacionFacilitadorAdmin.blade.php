@extends('layouts.kaiadmin-menu')

@section('content')
<div class="row">
    <div class="col-12">

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
            <h4 class="fw-bold text-dark mb-0">
                <i class="fas fa-tasks me-2 text-primary"></i> Requisitos Facilitador
            </h4>
            <div>
                <a href="{{ route('taller.postulacion-facilitador.admin.preview') }}" target="_blank" class="btn btn-outline-info rounded-pill">
                    <i class="fas fa-eye me-1"></i> Vista Previa del Landing
                </a>
                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCrearRequisito">
                    <i class="fas fa-plus me-1"></i> Agregar Requisito
                </button>
            </div>
        </div>

        {{-- Sección 1: Requisitos --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-info text-white border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i> Requisitos Configurados</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Obligatorio</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requisitos as $req)
                            <tr>
                                <td>{{ $req->id_requisito_facilitador }}</td>
                                <td class="fw-bold">{{ $req->titulo }}</td>
                                <td>
                                    @if($req->tipo === 'documento')
                                        <span class="badge bg-danger">Documento</span>
                                    @elseif($req->tipo === 'pregunta')
                                        <span class="badge bg-info">Pregunta</span>
                                    @else
                                        <span class="badge bg-success">Recurso</span>
                                    @endif
                                </td>
                                <td>{{ $req->obligatorio ? 'Sí' : 'No' }}</td>
                                <td>{{ $req->orden }}</td>
                                <td>
                                    @if($req->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarRequisito{{ $req->id_requisito_facilitador }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('taller.postulacion-facilitador.requisitos.toggle', $req->crypt_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $req->activo ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $req->activo ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Modal Editar Requisito --}}
                            <div class="modal fade" id="modalEditarRequisito{{ $req->id_requisito_facilitador }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('taller.postulacion-facilitador.requisitos.update', $req->crypt_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i> Editar Requisito</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Título <span class="text-danger">*</span></label>
                                                    <input type="text" name="titulo" class="form-control" value="{{ $req->titulo }}" required placeholder="Ej: Carta de motivación">
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                                                        <select name="tipo" class="form-select" required>
                                                            <option value="pregunta" {{ $req->tipo === 'pregunta' ? 'selected' : '' }}>Pregunta</option>
                                                            <option value="documento" {{ $req->tipo === 'documento' ? 'selected' : '' }}>Documento</option>
                                                            <option value="recurso" {{ $req->tipo === 'recurso' ? 'selected' : '' }}>Recurso</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Obligatorio</label>
                                                        <select name="obligatorio" class="form-select">
                                                            <option value="1" {{ $req->obligatorio ? 'selected' : '' }}>Sí</option>
                                                            <option value="0" {{ !$req->obligatorio ? 'selected' : '' }}>No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Descripción</label>
                                                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Instrucciones adicionales...">{{ $req->descripcion }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Orden</label>
                                                    <input type="number" name="orden" class="form-control" value="{{ $req->orden }}" min="0" style="max-width: 120px;">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay requisitos configurados. Agrega el primero haciendo clic en "Agregar Requisito".
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sección 2: Postulaciones --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-primary text-white border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i> Postulaciones Recibidas</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Revisada por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($postulaciones as $post)
                            <tr>
                                <td class="fw-bold">{{ $post->persona->primer_nombre }} {{ $post->persona->primer_apellido }}</td>
                                <td>{{ $post->persona->dni }}</td>
                                <td>{{ $post->creado_en->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($post->esPendiente())
                                        <span class="badge bg-warning">Pendiente</span>
                                    @elseif($post->esAprobada())
                                        <span class="badge bg-success">Aprobada</span>
                                    @else
                                        <span class="badge bg-danger">Rechazada</span>
                                    @endif
                                </td>
                                <td>{{ $post->revisor ? $post->revisor->name : '-' }}</td>
                                <td>
                                    @if($post->esPendiente())
                                        <a href="{{ route('taller.postulacion-facilitador.documentos', $post->crypt_id) }}" class="btn btn-sm btn-outline-info" title="Ver documentos">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <form action="{{ route('taller.postulacion-facilitador.aprobar', $post->crypt_id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de aprobar esta postulación? El usuario recibirá el perfil de Facilitador.')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar{{ $post->id_postulacion }}" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        {{-- Modal Rechazar --}}
                                        <div class="modal fade" id="modalRechazar{{ $post->id_postulacion }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('taller.postulacion-facilitador.rechazar', $post->crypt_id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2 text-danger"></i> Rechazar Postulación</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <p>¿Estás seguro de rechazar la postulación de <strong>{{ $post->persona->primer_nombre }} {{ $post->persona->primer_apellido }}</strong>?</p>
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
                                    @else
                                        <a href="{{ route('taller.postulacion-facilitador.documentos', $post->crypt_id) }}" class="btn btn-sm btn-outline-info" title="Ver documentos">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay postulaciones recibidas aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Crear Requisito --}}
<div class="modal fade" id="modalCrearRequisito" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('taller.postulacion-facilitador.requisitos.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Agregar Nuevo Requisito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Carta de motivación">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="pregunta">Pregunta</option>
                                <option value="documento">Documento</option>
                                <option value="recurso">Recurso (informativo)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Obligatorio</label>
                            <select name="obligatorio" class="form-select">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2" placeholder="Instrucciones adicionales..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Orden</label>
                        <input type="number" name="orden" class="form-control" value="0" min="0" style="max-width: 120px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Crear Requisito</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .modal-body .form-label { margin-bottom: 0.4rem; }
    .modal-body .form-control,
    .modal-body .form-select { padding: 0.6rem 0.8rem; }
    .modal-body .row .col-md-6 { padding-left: 0.75rem; padding-right: 0.75rem; }
    .modal-content { border-radius: 12px; overflow: hidden; }
    .modal-header { border-bottom: 1px solid #eee; padding: 1rem 1.25rem; }
    .modal-footer { border-top: 1px solid #eee; padding: 0.75rem 1.25rem; }
</style>
@endpush
@endsection