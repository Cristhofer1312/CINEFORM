@extends('layouts.kaiadmin-menu')

@section('title', 'Asistencia Individual - ' . $curso->nombre)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-card overflow-hidden">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-primary-soft text-primary me-2 px-3 py-1 rounded-pill" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-user-check me-1"></i> Asistencia Individual
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">{{ $inscripcion->persona->primer_nombre ?? '' }} {{ $inscripcion->persona->primer_apellido ?? '' }}</h3>
                        <small class="text-muted">C.I. {{ $inscripcion->persona->dni ?? 'N/A' }} — {{ $curso->nombre }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('taller.asistencia.consolidado', $curso->crypt_id) }}" class="btn btn-outline-light text-dark border-0 bg-gray-100 hover-lift">
                            <i class="fas fa-arrow-left me-2"></i> Volver a la lista consolidada
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-4">
                <div class="display-6 fw-bold text-primary mb-1">{{ $totalAsistencias }}</div>
                <div class="text-muted small fw-bold">Asistencias</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-4">
                <div class="display-6 fw-bold text-dark mb-1">{{ $totalActividades }}</div>
                <div class="text-muted small fw-bold">Actividades Totales</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-4">
                <div class="display-6 fw-bold {{ asistenciaColorTexto($porcentajeAsistencia) }} mb-1">{{ $porcentajeAsistencia }}%</div>
                <div class="text-muted small fw-bold">Porcentaje de Asistencia</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-4">
                <div class="display-6 fw-bold text-info mb-1">{{ $totalActividades - $totalAsistencias }}</div>
                <div class="text-muted small fw-bold">Ausencias</div>
            </div>
        </div>
    </div>

    <!-- Detalle por actividad -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i> Detalle por Actividad</h5>
        </div>
        <div class="card-body">
            @forelse($curso->contenidos as $contenido)
            @php
                $asistencia = $asistencias->get($contenido->id_contenido_curso);
            @endphp
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 {{ $asistencia && $asistencia->activa ? 'bg-success' : 'bg-light' }}" style="width:40px;height:40px;">
                        <i class="fas {{ $asistencia && $asistencia->activa ? 'fa-check text-white' : 'fa-times text-muted' }}"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">{{ $contenido->titulo }}</h6>
                        <small class="text-muted">{{ $contenido->fecha_contenido ? $contenido->fecha_contenido->format('d/m/Y H:i') : 'Sin fecha' }}</small>
                    </div>
                </div>
                <div class="text-end d-flex align-items-center gap-2">
                    <div>
                        @if($asistencia && $asistencia->activa)
                            <span class="badge bg-success rounded-pill px-3 py-2">Asistió</span>
                            <small class="text-muted d-block" style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($asistencia->fecha_hora_marcado)->format('H:i') }} — {{ $asistencia->metodo_marcado }}</small>
                        @elseif($asistencia && !$asistencia->activa)
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Anulada</span>
                            <small class="text-muted d-block" style="font-size:0.75rem;">{{ $asistencia->motivo_anulacion ?? 'Sin motivo' }}</small>
                        @else
                            <span class="badge bg-light text-muted rounded-pill px-3 py-2">Ausente</span>
                        @endif
                    </div>
                    @can('gestionarAsistencia', $curso)
                        <div class="ms-2">
                            @if($asistencia && $asistencia->activa)
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                    onclick="anularAsistencia('{{ route('taller.asistencia.anular', ['curso' => $curso->crypt_id, 'asistencia' => $asistencia->id_asistencia]) }}', '{{ addslashes($contenido->titulo) }}')" 
                                    title="Anular asistencia">
                                    <i class="fas fa-ban me-1"></i> Anular
                                </button>
                            @elseif($asistencia && !$asistencia->activa)
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" 
                                    onclick="restaurarAsistencia('{{ route('taller.asistencia.restaurar', ['curso' => $curso->crypt_id, 'asistencia' => $asistencia->id_asistencia]) }}', '{{ addslashes($contenido->titulo) }}')" 
                                    title="Restaurar asistencia">
                                    <i class="fas fa-undo me-1"></i> Restaurar
                                </button>
                            @elseif(!$contenido->es_evaluacion)
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                    onclick="marcarManual('{{ route('taller.asistencia.marcar-manual', ['curso' => $curso->crypt_id, 'contenido' => $contenido->id_contenido_curso]) }}', '{{ $inscripcion->id_persona }}', '{{ addslashes($contenido->titulo) }}')" 
                                    title="Marcar asistencia manual">
                                    <i class="fas fa-check-circle me-1"></i> Marcar
                                </button>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
            @empty
            <div class="py-5 text-center text-muted">
                <i class="fas fa-calendar-times mb-3 opacity-25" style="font-size: 3rem;"></i>
                <p>No hay actividades registradas en este curso.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function anularAsistencia(url, titulo) {
    Swal.fire({
        title: '¿Anular asistencia?',
        text: `Indica el motivo de anulación para "${titulo}":`,
        input: 'text',
        inputPlaceholder: 'Ej. Error de marcado, retiro temporal...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Debes ingresar un motivo de anulación.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            peticionConFeedback(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ motivo: result.value.trim() })
            }, {
                loadingTitle: 'Anulando asistencia...',
                successTitle: 'Asistencia Anulada'
            }).then(() => {
                location.reload();
            });
        }
    });
}

function restaurarAsistencia(url, titulo) {
    Swal.fire({
        title: '¿Restaurar asistencia?',
        text: `Se reactivará la asistencia para "${titulo}".`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            peticionConFeedback(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }, {
                loadingTitle: 'Restaurando asistencia...',
                successTitle: 'Asistencia Restaurada'
            }).then(() => {
                location.reload();
            });
        }
    });
}

function marcarManual(url, idPersona, titulo) {
    Swal.fire({
        title: '¿Marcar asistencia manual?',
        text: `Se registrará la asistencia para "${titulo}".`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1572e8',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            peticionConFeedback(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_persona: idPersona })
            }, {
                loadingTitle: 'Registrando asistencia...',
                successTitle: 'Asistencia Registrada'
            }).then(() => {
                location.reload();
            });
        }
    });
}
</script>
@endpush
@endsection

