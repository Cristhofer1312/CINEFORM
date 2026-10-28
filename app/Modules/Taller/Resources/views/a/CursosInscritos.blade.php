@extends('layouts.kaiadmin-menu')

@section('title', ($esFacilitador ?? false) ? 'Mis Cursos Asignados' : 'Mis Cursos Inscritos')

@section('content')
<div class="container-fluid py-4">

    {{-- Header & Filters Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card mb-3 shadow-sm overflow-hidden">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0">
                            <i class="fas {{ ($esFacilitador ?? false) ? 'fa-chalkboard-teacher' : 'fa-book-reader' }} me-2"></i> {{ ($esFacilitador ?? false) ? 'Mis Cursos Asignados' : 'Mis Cursos Inscritos' }}
                        </h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($puedeFacilitar ?? false)
                                <div class="btn-group btn-group-sm" role="group" aria-label="Vista de mis cursos">
                                    <a href="{{ route('taller.mis-cursos') }}"
                                        class="btn {{ $esFacilitador ? 'btn-light text-primary fw-bold' : 'btn-outline-light' }}"
                                        @if($esFacilitador) aria-current="page" @endif>Asignados</a>
                                    <a href="{{ route('taller.mis-cursos', ['vista' => 'inscritos']) }}"
                                        class="btn {{ $esFacilitador ? 'btn-outline-light' : 'btn-light text-primary fw-bold' }}"
                                        @unless($esFacilitador) aria-current="page" @endunless>Inscritos</a>
                                </div>
                            @endif
                            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                                Total: {{ $cursosInscritos->total() }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light py-3">
                    <form action="{{ route('taller.mis-cursos') }}" method="GET" class="row g-3">
                        @unless($esFacilitador)<input type="hidden" name="vista" value="inscritos">@endunless
                        {{-- Buscador --}}
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 shadow-none"
                                    placeholder="Buscar por nombre o descripción..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Filtro por estado --}}
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-filter text-muted"></i>
                                </span>
                                <select name="id_estado" class="form-select border-start-0 shadow-none">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado->crypt_id }}"
                                            {{ request('id_estado') == $estado->crypt_id || request('id_estado') == $estado->id_estado ? 'selected' : '' }}>
                                            {{ str_replace('_', ' ', $estado->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold" style="flex: 1;">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                            @if(request()->has('search') || request()->has('id_estado'))
                                <a href="{{ route('taller.mis-cursos', $esFacilitador ? [] : ['vista' => 'inscritos']) }}"
                                    class="btn btn-outline-secondary fw-bold" style="flex: 1;">
                                    <i class="fas fa-undo me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de cursos --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm overflow-hidden">
                <div class="card-body pb-0">
                    <div class="row">
                        @forelse($cursosInscritos as $curso)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all border">
                                    <div class="position-relative">
                                        @if($curso->imagen)
                                            <img src="{{ asset($curso->imagen) }}" class="card-img-top"
                                                alt="{{ $curso->nombre }}" style="height: 180px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                style="height: 180px;">
                                                <i class="fas fa-book-open fa-4x text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1">
                                            <span class="badge bg-primary">
                                                <i class="fas {{ $curso->modalidadIcon }} me-1"></i>
                                                {{ $curso->modalidadNombre }}
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
                                            {{ Str::limit($curso->descripcion ?? 'Sin descripción', 100) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                            <div>
                                                <small class="text-muted me-3">
                                                    <i class="fas fa-book me-1"></i>
                                                    {{ $curso->contenidos_count ?? 0 }} contenidos
                                                </small>
                                                @if($curso->fecha_inicio)
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        {{ \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2">
                                                @if($esFacilitador ?? false)
                                                    @if(in_array($curso->estado_actual->id_estado ?? 0, [7]))
                                                        <a href="{{ route('taller.asistencia.consolidado', $curso->crypt_id) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           data-bs-toggle="tooltip"
                                                           title="Gestionar Asistencia">
                                                            <i class="fas fa-qrcode"></i>
                                                        </a>
                                                    @endif
                                                    @if(in_array($curso->estado_actual->id_estado ?? 0, [6, 7]))
                                                        <a href="{{ route('taller.cursos.participantes', $curso->crypt_id) }}"
                                                           class="btn btn-sm btn-outline-secondary"
                                                           data-bs-toggle="tooltip"
                                                           title="Ver Participantes">
                                                            <i class="fas fa-users"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i> Ver Curso
                                                    </a>
                                                @else
                                                    @if(in_array($curso->estado_actual->id_estado ?? 0, [8, 9]))
                                                        @if($curso->certificado_aprobado)
                                                            @if($curso->ya_respondio)
                                                                <a href="{{ route('taller.certificados.descargar', $curso->crypt_id) }}"
                                                                   class="btn btn-sm btn-outline-primary"
                                                                   data-bs-toggle="tooltip"
                                                                   title="Descargar Certificado">
                                                                    <i class="fas fa-certificate"></i>
                                                                </a>
                                                            @else
                                                                <a href="{{ route('taller.cursos.satisfaccion.create', $curso->crypt_id) }}"
                                                                   class="btn btn-sm btn-outline-info"
                                                                   data-bs-toggle="tooltip"
                                                                   title="Responder encuesta para descargar el certificado">
                                                                    <i class="fas fa-clipboard-check"></i>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @endif
                                                    <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-eye me-1"></i> Ver Curso
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 py-5 text-center">
                                <div class="mb-3">
                                    <i class="fas {{ ($esFacilitador ?? false) ? 'fa-chalkboard-teacher' : 'fa-book-reader' }} fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-3">
                                    @if(request()->filled('search') || request()->filled('id_estado'))
                                        No hay cursos que coincidan con los filtros
                                    @elseif($esFacilitador ?? false)
                                        No tienes cursos asignados como facilitador actualmente
                                    @else
                                        No estás inscrito en ningún curso aún
                                    @endif
                                </h5>
                                <p class="text-muted mb-4">
                                    @if($esFacilitador ?? false)
                                        Los programas de formación asignados a tu perfil aparecerán aquí para su gestión académica.
                                    @else
                                        Explora nuestros cursos disponibles y comienza a aprender hoy mismo.
                                    @endif
                                </p>
                                @if(!($esFacilitador ?? false))
                                <a href="{{ route('taller.cursos.index') }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-search me-1"></i> Explorar Cursos
                                </a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Paginación --}}
                @if($cursosInscritos->hasPages())
                    <div class="card-footer bg-transparent border-top">
                        <div class="d-flex justify-content-center">
                            {{ $cursosInscritos->links() }}
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
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
    });
</script>
@endpush

<style>
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
@endsection