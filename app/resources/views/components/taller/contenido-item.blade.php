@props(['index', 'contenido' => null, 'tiposEvaluacion' => []])

@php
    $isOld = is_array($contenido);
    $titulo = $isOld ? ($contenido['titulo'] ?? '') : ($contenido->titulo ?? '');
    $esEvaluacion = $isOld ? ($contenido['es_evaluacion'] ?? false) : ($contenido->es_evaluacion ?? false);
    $idTipoEvaluacion = $isOld ? ($contenido['id_tipo_evaluacion'] ?? '') : ($contenido->id_tipo_evaluacion ?? '');
    $ponderacion = $isOld ? ($contenido['ponderacion'] ?? '') : ($contenido->ponderacion ?? '');
    $fechaRaw = $isOld ? ($contenido['fecha_contenido'] ?? '') : ($contenido->fecha_contenido ?? '');
    $fechaContenido = $fechaRaw instanceof \Carbon\Carbon ? $fechaRaw->format('Y-m-d') : $fechaRaw;
    $descripcionBreve = $isOld ? ($contenido['descripcion_breve'] ?? '') : ($contenido->descripcion_breve ?? '');
    $urlContenido = $isOld ? ($contenido['url_contenido'] ?? '') : ($contenido->url_contenido ?? '');
    $hasMaterial = $isOld ? ($contenido['_has_material'] ?? false) : (!empty($urlContenido));
@endphp

<div class="col contenido-item animate__animated animate__zoomIn" data-index="{{ $index }}">
    <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
        <div class="card-body p-4">
            <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                <div class="col-md-auto">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                        {{ $index + 1 }}
                    </div>
                </div>
                <div class="col">
                    <input type="text" name="contenidos[{{ $index }}][titulo]" class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" required placeholder="Título del bloque..." value="{{ $titulo }}">
                </div>
                <div class="col-md-auto">
                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                        <input type="hidden" name="contenidos[{{ $index }}][es_evaluacion]" value="0">
                        <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" id="evalSwitch_{{ $index }}" name="contenidos[{{ $index }}][es_evaluacion]" value="1" onchange="toggleEvaluacion(this)" {{ $esEvaluacion ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_{{ $index }}">Evaluable</label>
                    </div>
                </div>
                <div class="col-md-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

            <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3" style="{{ $esEvaluacion ? 'display:flex;' : 'display:none;' }} background-color: #0d6efd !important;">
                <div class="col-md-7">
                    <label class="small text-white fw-bold mb-1">METODOLOGÍA</label>
                    <select name="contenidos[{{ $index }}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                        <option value="">Seleccione...</option>
                        @foreach($tiposEvaluacion as $tipo)
                            <option value="{{ $tipo->id_tipo_evaluacion }}" {{ $idTipoEvaluacion == $tipo->id_tipo_evaluacion ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="small text-white fw-bold mb-1">PESO (%)</label>
                    <input type="number" name="contenidos[{{ $index }}][ponderacion]" class="form-control border-0 text-center fw-bold text-primary" oninput="actualizarPonderacionTotal()" placeholder="0" value="{{ $ponderacion }}">
                </div>
            </div>

            <div class="material-fields row g-3 rounded-3 p-3 mb-3" style="{{ $hasMaterial ? 'display:flex;' : 'display:none;' }} background-color: #4f46e5 !important;">
                <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                    <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                </div>
                <div class="col-12">
                    <label class="small text-white fw-bold mb-1 opacity-75">ENLACE DEL RECURSO (URL)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 opacity-50"><i class="fas fa-link small"></i></span>
                        <input type="url" name="contenidos[{{ $index }}][url_contenido]" class="form-control border-0 py-2" placeholder="https://..." value="{{ $urlContenido }}">
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                    <input type="date" name="contenidos[{{ $index }}][fecha_contenido]" class="form-control border-light-2 py-2" value="{{ $fechaContenido }}">
                </div>
                <div class="col-md-5 d-flex align-items-end">
                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                        <input type="hidden" name="contenidos[{{ $index }}][_has_material]" value="0">
                        <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle"
                            id="matSwitch_{{ $index }}"
                            onchange="toggleMaterial(this)" {{ $hasMaterial ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_{{ $index }}">
                            <i class="fas fa-paperclip me-1"></i> ¿Tiene material?
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <textarea name="contenidos[{{ $index }}][descripcion_breve]" class="form-control border-light-2" rows="2" placeholder="Breve explicación...">{{ $descripcionBreve }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
