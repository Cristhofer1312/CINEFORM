@extends('layouts.kaiadmin-menu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taller.cursos.show', $curso->id_curso) }}" class="text-decoration-none text-muted">{{ $curso->nombre }}</a></li>
                        <li class="breadcrumb-item active text-info fw-bold">Editar Requisitos</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-dark mb-0">Modificar Requisitos</h2>
                <p class="text-muted small mb-0"><i class="fas fa-list-check me-1 text-info"></i> Editando requisitos para: <strong>{{ $curso->nombre }}</strong></p>
            </div>
            <div class="text-end">
                <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                    class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-2 text-primary"></i> Volver
                </a>
            </div>
        </div>

        @if ($errors->any())
            <x-taller.alert type="danger" title="Errores de validación">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-taller.alert>
        @endif

        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-3 mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Atención:</strong> Si eliminas o cambias requisitos obligatorios y el curso ya tiene participantes inscritos, los nuevos requisitos solo aplicarán para las nuevas inscripciones.
        </div>

        <form action="{{ route('taller.cursos.requisitos.update', $curso->id_curso) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-info me-2"></i> Formulario de Requisitos</h5>
                </div>
                <div class="card-body bg-light bg-opacity-50 p-4">
                    
                    <div id="requisitos-container">
                        <!-- Requisitos existentes -->
                        @foreach($curso->requisitos as $index => $req)
                            <div class="requisito-item bg-white p-4 rounded-4 shadow-sm border border-light-2 mb-4 position-relative">
                                <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute" style="top: -10px; right: -10px; width: 30px; height: 30px;" onclick="this.closest('.requisito-item').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                                
                                <input type="hidden" name="requisitos[{{ $index }}][tipo]" value="{{ $req->tipo }}">
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="req-icon me-3 h3 mb-0">
                                        @if($req->tipo === 'pregunta') <i class="fas fa-question-circle text-info"></i>
                                        @elseif($req->tipo === 'documento') <i class="fas fa-file-upload text-warning"></i>
                                        @else <i class="fas fa-info-circle text-success"></i> @endif
                                    </div>
                                    <h6 class="req-label mb-0 fw-bold text-uppercase 
                                        {{ $req->tipo === 'pregunta' ? 'text-info' : ($req->tipo === 'documento' ? 'text-warning' : 'text-success') }}">
                                        @if($req->tipo === 'pregunta') Pregunta Abierta
                                        @elseif($req->tipo === 'documento') Solicitud de Documento
                                        @else Recurso Informativo @endif
                                    </h6>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-9">
                                        <label class="form-label small fw-bold text-muted">Título / Enunciado <span class="text-danger">*</span></label>
                                        <input type="text" name="requisitos[{{ $index }}][titulo]" class="form-control" required value="{{ $req->titulo }}">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end" @if($req->tipo === 'recurso') style="display:none !important;" @endif>
                                        <div class="form-check form-switch w-100 p-2 bg-light rounded border d-flex align-items-center">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="requisitos[{{ $index }}][obligatorio]" value="1" {{ $req->obligatorio ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold mb-0">Obligatorio</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Descripción (Opcional)</label>
                                        <textarea name="requisitos[{{ $index }}][descripcion]" class="form-control text-muted" rows="2">{{ $req->descripcion }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-5 mb-3">
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-info border-2 px-4 py-2 rounded-pill fw-bold dropdown-toggle shadow-sm" type="button" id="btnAgregarRequisito" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus-circle me-2"></i> Añadir Requisito
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0 rounded-3 mt-2" aria-labelledby="btnAgregarRequisito">
                                <li><a class="dropdown-item py-2" href="#" onclick="agregarRequisito('pregunta', event)"><i class="fas fa-question-circle text-info me-2"></i> Pregunta Abierta</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="agregarRequisito('documento', event)"><i class="fas fa-file-upload text-warning me-2"></i> Solicitud de Documento</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="agregarRequisito('recurso', event)"><i class="fas fa-info-circle text-success me-2"></i> Recurso Informativo</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer de Acciones -->
            <div class="row justify-content-center mt-4 pb-5">
                <div class="col-lg-6">
                    <div class="card border shadow rounded-pill overflow-hidden bg-white">
                        <div class="card-body p-2 d-flex align-items-center justify-content-between">
                            <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                                class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-info text-white px-5 py-3 rounded-pill fw-bold shadow-lg">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Template para Requisito -->
<template id="requisito-template">
    <div class="requisito-item bg-white p-4 rounded-4 shadow-sm border border-light-2 mb-4 position-relative">
        <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute" style="top: -10px; right: -10px; width: 30px; height: 30px;" onclick="this.closest('.requisito-item').remove()">
            <i class="fas fa-times"></i>
        </button>
        
        <input type="hidden" name="requisitos[__INDEX__][tipo]" class="req-tipo">
        
        <div class="d-flex align-items-center mb-3">
            <div class="req-icon me-3 h3 mb-0"></div>
            <h6 class="req-label mb-0 fw-bold text-uppercase"></h6>
        </div>

        <div class="row g-3">
            <div class="col-md-9">
                <label class="form-label small fw-bold text-muted">Título / Enunciado <span class="text-danger">*</span></label>
                <input type="text" name="requisitos[__INDEX__][titulo]" class="form-control" required placeholder="Ej: Foto de Cédula, ¿Por qué quieres participar?">
            </div>
            <div class="col-md-3 d-flex align-items-end req-obligatorio-container">
                <div class="form-check form-switch w-100 p-2 bg-light rounded border d-flex align-items-center">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="requisitos[__INDEX__][obligatorio]" value="1" checked>
                    <label class="form-check-label small fw-bold mb-0">Obligatorio</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold text-muted">Descripción (Opcional)</label>
                <textarea name="requisitos[__INDEX__][descripcion]" class="form-control text-muted" rows="2" placeholder="Instrucciones adicionales para el participante..."></textarea>
            </div>
        </div>
    </div>
</template>

@push('styles')
<style>
    .border-light-2 { border: 1px solid #e2e8f0; }
    .shadow-card { box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
</style>
@endpush

@push('scripts')
<script>
    let reqIndex = {{ count($curso->requisitos) > 0 ? max(array_keys($curso->requisitos->toArray())) + 1 : 0 }};

    function agregarRequisito(tipo, event) {
        if(event) event.preventDefault();
        
        const template = document.getElementById('requisito-template').innerHTML;
        const html = template.replace(/__INDEX__/g, reqIndex++);
        
        const div = document.createElement('div');
        div.innerHTML = html;
        const element = div.firstElementChild;
        
        // Configurar según tipo
        const iconDiv = element.querySelector('.req-icon');
        const labelDiv = element.querySelector('.req-label');
        const inputTipo = element.querySelector('.req-tipo');
        const obligDiv = element.querySelector('.req-obligatorio-container');
        
        inputTipo.value = tipo;
        
        if (tipo === 'pregunta') {
            iconDiv.innerHTML = '<i class="fas fa-question-circle text-info"></i>';
            labelDiv.textContent = 'Pregunta Abierta';
            labelDiv.classList.add('text-info');
        } else if (tipo === 'documento') {
            iconDiv.innerHTML = '<i class="fas fa-file-upload text-warning"></i>';
            labelDiv.textContent = 'Solicitud de Documento';
            labelDiv.classList.add('text-warning');
        } else if (tipo === 'recurso') {
            iconDiv.innerHTML = '<i class="fas fa-info-circle text-success"></i>';
            labelDiv.textContent = 'Recurso Informativo';
            labelDiv.classList.add('text-success');
            // Recursos informativos no son obligatorios en sí mismos
            obligDiv.style.display = 'none';
        }
        
        document.getElementById('requisitos-container').appendChild(element);
    }
</script>
@endpush
@endsection
