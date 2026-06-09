@extends('layouts.kaiadmin-menu')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <!-- Hero Header -->
            <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-warning">
                <div class="card-header bg-white py-4 px-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" 
                               class="btn btn-white btn-sm rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center hvr-push" 
                               style="width: 40px; height: 40px;"
                               title="Volver al curso">
                                <i class="fas fa-arrow-left text-primary"></i>
                            </a>
                            <div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.75rem;">
                                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Explorar Cursos</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="text-decoration-none text-muted">Ficha Técnica</a></li>
                                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Postulados y Participantes</li>
                                    </ol>
                                </nav>
                                <h2 class="fw-bold text-dark mb-0">{{ $curso->nombre }}</h2>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold" style="font-size: 0.9rem;">
                                <i class="fas fa-users me-1"></i> {{ $aprobados->count() }} 
                                @if($curso->cantidad_cupos)
                                    / {{ $curso->cantidad_cupos }} Cupos
                                @else
                                    Aprobados
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    {{-- Nav Tabs --}}
                    <ul class="nav nav-tabs nav-line nav-color-primary px-4 border-0 mt-3" id="postulacionTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" id="postulados-tab" data-bs-toggle="tab" href="#postulados" role="tab">
                                <i class="fas fa-hourglass-half me-2"></i> Postulados 
                                <span class="badge bg-primary ms-1">{{ $postulados->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" id="aprobados-tab" data-bs-toggle="tab" href="#aprobados" role="tab">
                                <i class="fas fa-check-circle me-2"></i> Aprobados
                                <span class="badge bg-success ms-1">{{ $aprobados->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" id="rechazados-tab" data-bs-toggle="tab" href="#rechazados" role="tab">
                                <i class="fas fa-exclamation-circle me-2"></i> Rechazados
                                <span class="badge bg-warning ms-1">{{ $rechazados->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" id="denegados-tab" data-bs-toggle="tab" href="#denegados" role="tab">
                                <i class="fas fa-times-circle me-2"></i> Denegados
                                <span class="badge bg-danger ms-1">{{ $denegados->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4 px-4 pb-4" id="postulacionTabsContent">
                        {{-- Tab Postulados --}}
                        <div class="tab-pane fade show active" id="postulados" role="tabpanel">
                            @include('taller::a.partials.participantes.lista', ['items' => $postulados, 'tipo' => 'postulado'])
                        </div>
                        
                        {{-- Tab Aprobados --}}
                        <div class="tab-pane fade" id="aprobados" role="tabpanel">
                            @include('taller::a.partials.participantes.lista', ['items' => $aprobados, 'tipo' => 'aprobado'])
                        </div>

                        {{-- Tab Rechazados --}}
                        <div class="tab-pane fade" id="rechazados" role="tabpanel">
                            @include('taller::a.partials.participantes.lista', ['items' => $rechazados, 'tipo' => 'rechazado'])
                        </div>

                        {{-- Tab Denegados --}}
                        <div class="tab-pane fade" id="denegados" role="tabpanel">
                            @include('taller::a.partials.participantes.lista', ['items' => $denegados, 'tipo' => 'denegado'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .shadow-card { box-shadow: 0 10px 40px rgba(0,0,0,0.06); }
    .shadow-xs { box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .hvr-push { transition: transform 0.2s; }
    .hvr-push:active { transform: scale(0.97); }
    
    .nav-line.nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        padding: 1rem 1.5rem;
    }
    .nav-line.nav-tabs .nav-link.active {
        background: transparent;
        border-bottom-color: #3b82f6;
        color: #3b82f6;
    }
</style>
@endpush

@push('scripts')
<script>
    function updatePostulacion(id, action, title, text, showInput = false) {
        Swal.fire({
            title: title,
            text: text,
            input: showInput ? 'textarea' : null,
            inputPlaceholder: showInput ? 'Escriba la razón de esta decisión...' : null,
            inputValidator: showInput ? (value) => {
                if (!value) return '¡La razón es obligatoria!';
            } : null,
            icon: action === 'aprobar' ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                
                const data = new FormData();
                data.append('_token', '{{ csrf_token() }}');
                if (showInput) data.append('motivo', result.value);

                fetch(`{{ url('taller/inscripciones') }}/${id}/${action}`, {
                    method: 'POST',
                    body: data
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Completado', text: data.message, timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
