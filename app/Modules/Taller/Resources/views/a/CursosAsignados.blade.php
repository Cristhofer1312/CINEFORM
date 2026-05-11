@extends('layouts.kaiadmin-menu')

@section('title', 'Mis Cursos Asignados')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Mis Cursos Asignados</h5>
                            <span class="badge bg-white text-primary">{{ $cursos->count() }} cursos asignados</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($cursos->isEmpty())
                            <div class="text-center p-5">
                                <div class="mb-3">
                                    <i class="fas fa-chalkboard-teacher fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-3">No tienes cursos asignados</h5>
                            </div>
                        @else
                            <div class="row">
                                @foreach($cursos as $curso)
                                    @php
                                        $modalidad = $curso->modalidad->nombre_modalidad ?? 'No especificada';
                                        $modalidadIcon = $modalidad === 'Presencial' ? 'fa-building' : 'fa-laptop';
                                        $estado = $curso->estado_id;
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="position-relative">
                                                @if($curso->imagen)
                                                    <img src="{{ asset($curso->imagen) }}" class="card-img-top"
                                                        alt="{{ $curso->nombre }}" style="height: 180px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                                        style="height: 180px;">
                                                        <i class="fas fa-image fa-4x text-muted"></i>
                                                    </div>
                                                @endif
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-primary">
                                                        <i class="fas {{ $modalidadIcon }} me-1"></i>
                                                        {{ $modalidad }}
                                                    </span>

                                                    @if($curso->estado_actual)
                                                        @php
                                                            $estadoEnum = \App\Enums\EstadoCurso::tryFrom($curso->estado_actual->id_estado);
                                                        @endphp
                                                        @if($estadoEnum)
                                                            <span class="badge bg-{{ $estadoEnum->color() }}">{{ $estadoEnum->label() }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $curso->estado_actual->nombre }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">Sin estado</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <h5 class="card-title text-truncate" title="{{ $curso->nombre }}">
                                                    {{ $curso->nombre }}
                                                </h5>

                                                <p class="card-text text-muted">
                                                    {{ Str::limit($curso->descripcion ?? 'Sin descripción', 120) }}
                                                    @if(isset($curso->descripcion) && strlen($curso->descripcion) > 120)
                                                        <a href="#" class="text-primary" data-bs-toggle="tooltip"
                                                            title="{{ $curso->descripcion }}">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
                                                    @endif
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div>
                                                        <small class="text-muted me-3">
                                                            <i class="fas fa-book me-1"></i>
                                                            {{ $curso->total_contenidos ?? 0 }} contenidos
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-users me-1"></i>
                                                            {{ $curso->inscripciones_count ?? 0 }} participantes
                                                        </small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                                            class="btn btn-secondary btn-sm" title="Ver detalles">
                                                            <i class="fas fa-info-circle"></i> Detalles
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($cursos->hasPages())
                        <div class="card-footer bg-transparent border-top">
                            <div class="d-flex justify-content-center">
                                {{ $cursos->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Inicializar tooltips de Bootstrap
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>
    @endpush
@endsection