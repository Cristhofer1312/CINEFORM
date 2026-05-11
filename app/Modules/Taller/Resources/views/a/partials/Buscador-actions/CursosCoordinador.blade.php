@extends('layouts.kaiadmin-menu')

@section('title', 'Gestión de Cursos')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Filters Card -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-3 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-chalkboard-teacher me-2"></i>Todos los Cursos</h5>
                            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                                Total: {{ $cursos->total() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body bg-light py-3">
                        <form action="{{ route('taller.cursos.index') }}" method="GET" class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 shadow-none"
                                        placeholder="Buscar por nombre o descripción..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="fas fa-filter text-muted"></i></span>
                                    <select name="id_estado" class="form-select border-start-0 shadow-none">
                                        <option value="">Todos los estados</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->crypt_id }}" {{ request('id_estado') == $estado->crypt_id || request('id_estado') == $estado->id_estado ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', $estado->nombre) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary fw-bold" style="flex: 1;">
                                    <i class="fas fa-search me-1"></i> Filtrar
                                </button>
                                @if(request()->has('search') || request()->has('id_estado'))
                                    <a href="{{ route('taller.cursos.index') }}" class="btn btn-outline-secondary fw-bold"
                                        style="flex: 1;">
                                        <i class="fas fa-undo me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid Container with White Background -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <div class="card-body pb-0">
                        <div class="row">
                            @forelse($cursos as $curso)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all border">
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
                                            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                                <span class="badge bg-primary">
                                                    <i class="fas {{ $curso->modalidadIcon }} me-1"></i>
                                                    {{ $curso->modalidad }}
                                                </span>
                                                <span class="badge {{ $curso->badgeClass }}">
                                                    {{ $curso->estadoNombre }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title text-truncate fw-bold" title="{{ $curso->nombre }}">
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
                                                        {{ $curso->contenidos_count ?? 0 }} contenidos
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-users me-1"></i>
                                                        {{ $curso->inscripciones->count() ?? 0 }} participantes
                                                    </small>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                                        class="btn btn-success">
                                                        <i class="fas fa-eye me-1"></i> Detalles
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-chalkboard-teacher fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">No hay cursos disponibles que coincidan con los filtros</h5>
                                    <a href="{{ route('taller.cursos.index') }}"
                                        class="btn btn-primary rounded-pill px-4">Limpiar filtros</a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Pagination -->
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
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>
    @endpush

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-3px);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #1572e8 0%, #0b5ed7 100%);
        }
    </style>
@endsection