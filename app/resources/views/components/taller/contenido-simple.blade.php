@props(['index', 'contenido' => null])

@php
    $isOld = is_array($contenido);
    $titulo = $isOld ? ($contenido['titulo'] ?? '') : ($contenido->titulo ?? '');
    $fechaRaw = $isOld ? ($contenido['fecha_contenido'] ?? '') : ($contenido->fecha_contenido ?? '');
    $fechaContenido = $fechaRaw instanceof \Carbon\Carbon ? $fechaRaw->format('Y-m-d') : $fechaRaw;
    $horaInicio = $isOld ? ($contenido['hora_inicio'] ?? '') : ($contenido->hora_inicio ?? '');
    $horaInicioVal = $horaInicio instanceof \Carbon\Carbon ? $horaInicio->format('H:i') : $horaInicio;
    $horaFin = $isOld ? ($contenido['hora_fin'] ?? '') : ($contenido->hora_fin ?? '');
    $horaFinVal = $horaFin instanceof \Carbon\Carbon ? $horaFin->format('H:i') : $horaFin;
    $descripcionBreve = $isOld ? ($contenido['descripcion_breve'] ?? '') : ($contenido->descripcion_breve ?? '');
    $urlContenido = $isOld ? ($contenido['url_contenido'] ?? '') : ($contenido->url_contenido ?? '');
@endphp

<div class="col contenido-item" data-index="{{ $index }}">
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
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

            <div class="rounded-3 p-3 mb-3" style="background-color: #f1f5f9 !important;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                        <input type="date" name="contenidos[{{ $index }}][fecha_contenido]" class="form-control border-light-2 py-2" value="{{ $fechaContenido }}">
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted fw-bold mb-1">HORA DE INICIO</label>
                        <input type="time" name="contenidos[{{ $index }}][hora_inicio]" class="form-control border-light-2 py-2" value="{{ $horaInicioVal }}" step="60" min="00:00" max="23:59">
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted fw-bold mb-1">HORA DE FIN</label>
                        <input type="time" name="contenidos[{{ $index }}][hora_fin]" class="form-control border-light-2 py-2" value="{{ $horaFinVal }}" step="60" min="00:00" max="23:59">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="small text-muted fw-bold mb-1"><i class="fas fa-link me-1"></i> ENLACE DEL RECURSO (OPCIONAL)</label>
                    <input type="url" name="contenidos[{{ $index }}][url_contenido]" class="form-control border-light-2 py-2" placeholder="https://..." value="{{ $urlContenido }}">
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <textarea name="contenidos[{{ $index }}][descripcion_breve]" class="form-control border-light-2" rows="2" placeholder="Breve explicación del bloque...">{{ $descripcionBreve }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
