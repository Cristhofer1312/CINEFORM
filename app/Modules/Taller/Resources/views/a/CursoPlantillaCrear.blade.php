@extends('layouts.kaiadmin-menu')

@section('title', 'Calibración de Certificado')

@section('content')
<div class="container-fluid py-4">
    @if(!empty($esWizard))
        <div class="row"><div class="col-12">
            <x-taller.wizard-steps :actual="$pasoActual ?? 4" :completados="$completados ?? []" />
        </div></div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-arrows-alt me-2"></i> Paso 2: Calibración de Plantilla</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="btn-group shadow-sm bg-white rounded">
                            <button type="button" class="btn btn-light btn-sm border-end" id="btn-undo" onclick="undo()" title="Deshacer (Ctrl+Z)" disabled>
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm" id="btn-redo" onclick="redo()" title="Rehacer (Ctrl+Y)" disabled>
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                        <div class="btn-group shadow-sm bg-white rounded" role="group" aria-label="Zoom del lienzo">
                            <button type="button" class="btn btn-light btn-sm border-end" onclick="zoomCanvas(-25)" title="Reducir zoom">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm border-end" id="zoom-label" title="Nivel de zoom" style="min-width:52px;">100%</button>
                            <button type="button" class="btn btn-light btn-sm" onclick="zoomCanvas(25)" title="Ampliar zoom">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm shadow-sm active" id="btn-snap" onclick="toggleSnap()" title="Ajustar a rejilla de 5 mm">
                            <i class="fas fa-magnet me-1"></i> Rejilla 5mm
                        </button>
                        <span class="badge bg-white text-primary">A4 (297 x 210 mm)</span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-4">
                        {{-- Columna: Controles (Se muestra abajo en móviles) --}}
                        <div class="col-12 col-lg-3 order-2 order-lg-1">
                            {{-- novalidate: los campos numéricos de precisión viven en un
                                 acordeón colapsado (display:none). Si el navegador los marca
                                 inválidos no puede enfocarlos y bloquea el submit con
                                 "An invalid form control ... is not focusable". La validación
                                 real la hacen el JS (clamp) y el servidor (coords/archivos). --}}
                            <form @if(!empty($esWizard)) action="{{ route('taller.cursos.wizard.plantilla.store') }}" @else action="{{ route('taller.cursos.plantilla.store', $curso->crypt_id) }}" @endif method="POST" enctype="multipart/form-data" id="form-plantilla" novalidate>
                                @csrf
                                <input type="hidden" name="coords" id="coords-input">

                                <div class="accordion accordion-flush border rounded mb-3" id="controlsAccordion">
                                    {{-- Plantilla --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlantilla">
                                                <i class="fas fa-image me-2"></i> Imagen de Fondo
                                            </button>
                                        </h2>
                                        <div id="collapsePlantilla" class="accordion-collapse collapse show">
                                            <div class="accordion-body py-2 px-3">
                                                <p class="small text-muted mb-2">
                                                    Debe ser <strong>apaisada</strong> (A4 horizontal, 297 x 210 mm),
                                                    de hasta 4 MB y 4000 px por lado.
                                                </p>
                                                <input type="file" class="form-control form-control-sm" name="plantilla" accept="image/png,image/jpeg" onchange="previewImage(this)">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tipografía --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFont">
                                                <i class="fas fa-font me-2"></i> Tamaños de Letra
                                            </button>
                                        </h2>
                                        <div id="collapseFont" class="accordion-collapse collapse">
                                            <div class="accordion-body py-2 px-3">
                                                <div class="mb-2">
                                                    <label class="small fw-bold d-flex justify-content-between">
                                                        <span>Nombre:</span>
                                                        <span id="val-nombre" class="text-primary">{{ $coords['nombre']['size'] ?? 24 }}pt</span>
                                                    </label>
                                                    <input type="range" class="form-range font-slider" min="10" max="60" step="1" data-key="nombre" value="{{ $coords['nombre']['size'] ?? 24 }}" oninput="updateFontSize(this)">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small fw-bold d-flex justify-content-between">
                                                        <span>DNI:</span>
                                                        <span id="val-dni" class="text-primary">{{ $coords['dni']['size'] ?? 12 }}pt</span>
                                                    </label>
                                                    <input type="range" class="form-range font-slider" min="8" max="30" step="1" data-key="dni" value="{{ $coords['dni']['size'] ?? 12 }}" oninput="updateFontSize(this)">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small fw-bold d-flex justify-content-between">
                                                        <span>Código:</span>
                                                        <span id="val-code" class="text-primary">{{ $coords['code']['size'] ?? 8 }}pt</span>
                                                    </label>
                                                    <input type="range" class="form-range font-slider" min="6" max="20" step="1" data-key="code" value="{{ $coords['code']['size'] ?? 8 }}" oninput="updateFontSize(this)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Precisión: posición numérica del elemento seleccionado --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrecision">
                                                <i class="fas fa-crosshairs me-2"></i> Posición Exacta
                                            </button>
                                        </h2>
                                        <div id="collapsePrecision" class="accordion-collapse collapse">
                                            <div class="accordion-body py-2 px-3">
                                                <p class="small text-muted mb-2">Haz clic en un elemento del lienzo (o usa <kbd>←↑↓→</kbd>, <kbd>Shift</kbd> = 5 mm) y ajusta aquí al milímetro.</p>
                                                <div class="mb-2">
                                                    <label class="small fw-bold" for="precision-key">Elemento</label>
                                                    <select id="precision-key" class="form-select form-select-sm" onchange="seleccionarElemento(this.value)">
                                                        <option value="nombre">Nombre</option>
                                                        <option value="dni">Cédula</option>
                                                        <option value="qr">Código QR</option>
                                                        <option value="code">Código</option>
                                                        <option value="firma">Firma</option>
                                                    </select>
                                                </div>
                                                <div class="row g-2" id="precision-campos">
                                                    <div class="col-6">
                                                        <label class="small fw-bold" for="precision-x">X (mm)</label>
                                                        <input type="number" id="precision-x" class="form-control form-control-sm" step="any" min="0" max="297" oninput="posicionExacta()">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small fw-bold" for="precision-y">Y (mm)</label>
                                                        <input type="number" id="precision-y" class="form-control form-control-sm" step="any" min="0" max="210" oninput="posicionExacta()">
                                                    </div>
                                                    <div class="col-6 precision-dim">
                                                        <label class="small fw-bold" for="precision-w">Ancho (mm)</label>
                                                        <input type="number" id="precision-w" class="form-control form-control-sm" step="any" min="5" max="297" oninput="posicionExacta()">
                                                    </div>
                                                    <div class="col-6 precision-dim">
                                                        <label class="small fw-bold" for="precision-h">Alto (mm)</label>
                                                        <input type="number" id="precision-h" class="form-control form-control-sm" step="any" min="5" max="210" oninput="posicionExacta()">
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" onclick="restablecerElemento()">
                                                    <i class="fas fa-undo me-1"></i> Restablecer este elemento
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Firma Digital --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFirma">
                                                <i class="fas fa-signature me-2"></i> Firma Digital
                                            </button>
                                        </h2>
                                        <div id="collapseFirma" class="accordion-collapse collapse">
                                            <div class="accordion-body py-2 px-3">
                                                <p class="small text-muted mb-2">PNG con fondo transparente recomendado. Hasta 2 MB y 3000 px por lado. Arrástrela y redimensione en el lienzo.</p>
                                                <input type="file" class="form-control form-control-sm mb-2" name="firma" accept="image/png,image/jpeg" onchange="previewFirma(this)" id="firma-file-input">
                                                <div id="firma-preview-wrap" style="display:none;" class="text-center">
                                                    <img id="firma-preview-img" src="" alt="Vista previa" class="img-fluid border rounded mb-1" style="max-height:55px;">
                                                    <div class="small text-success"><i class="fas fa-check-circle"></i> Firma cargada</div>
                                                </div>
                                                @if(!empty($firmaUrl))
                                                <div class="text-center mt-2 p-2 border rounded bg-light">
                                                    <img src="{{ $firmaUrl }}" alt="Firma guardada" class="img-fluid" style="max-height:50px;">
                                                    <div class="small text-muted mt-1">Firma guardada actualmente</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Coordenadas --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCoords">
                                                <i class="fas fa-crosshairs me-2"></i> Posiciones (mm)
                                            </button>
                                        </h2>
                                        <div id="collapseCoords" class="accordion-collapse collapse">
                                            <div class="accordion-body py-2 px-3 small" id="coord-list">
                                                {{-- Se llena con JS --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary shadow-sm">
                                        <i class="fas fa-save me-1"></i> @if(!empty($esWizard)) Continuar a Requisitos @else Guardar Diseño @endif
                                    </button>

                                    <button type="button" class="btn btn-outline-dark shadow-sm" onclick="abrirPruebaPdf()">
                                        <i class="fas fa-file-pdf me-1"></i> Vista previa PDF
                                    </button>
                                    <div class="small text-muted text-center" id="autosave-aviso" style="display:none;"></div>
                                    
                                    @if(empty($esWizard))
                                    {{-- Guarda primero: la prueba se genera desde los archivos
                                         en disco, así que un cambio sin guardar no saldría. --}}
                                    <button type="submit" name="ir_a_prueba" value="1" formtarget="_blank"
                                            class="btn btn-dark shadow-sm"
                                            title="Guarda el diseño y abre el PDF de prueba en otra pestaña">
                                        <i class="fas fa-file-pdf me-1"></i> Probar Emisión
                                    </button>
                                    @endif

                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="guardar_default" id="guardar-default" value="1">
                                        <label class="form-check-label small" for="guardar-default" style="cursor: pointer;">
                                            <i class="fas fa-star me-1 text-warning"></i>
                                            Guardar predeterminado
                                        </label>
                                    </div>
                                    <a href="{{ $esWizard ? route('taller.cursos.wizard.requisitos.create') : route('taller.cursos.requisitos.create', $curso->crypt_id) }}" class="btn btn-outline-secondary btn-sm">
                                        Omitir calibración
                                    </a>
                                    @if(!empty($esWizard))
                                    <a href="{{ route('taller.cursos.wizard.paso3.create') }}" class="btn btn-link btn-sm text-muted fw-bold text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i> Atrás (temario)
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>

                        {{-- Columna: Lienzo Interactivo (Se muestra arriba en móviles) --}}
                        <div class="col-12 col-lg-9 order-1 order-lg-2">
                            @unless($plantillaUrl)
                                <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <div>
                                        <strong class="d-block">Todavía no hay una imagen de plantilla</strong>
                                        <span class="small">
                                            Sin una plantilla el certificado se emitiría sin diseño de fondo.
                                            Súbela en <em>Imagen de Fondo</em> o acepta que se use una plantilla global.
                                            Mientras tanto puedes igual calibrar las posiciones: el lienzo
                                            representa el A4 horizontal (297 × 210 mm).
                                        </span>
                                    </div>
                                </div>
                            @endunless
                            <div class="preview-wrapper position-relative border shadow-sm rounded bg-light overflow-hidden mx-auto" id="canvas-wrapper">
                                <div id="certificate-canvas" class="position-relative" 
                                     style="aspect-ratio: 297/210; background-image: url('{{ $plantillaUrl }}'); background-size: 100% 100%; background-repeat: no-repeat;">
                                    
                                    {{-- Elementos Arrastrables --}}
                                    <div id="drag-nombre" class="draggable-field text-center fw-bold" data-key="nombre"
                                         style="width: 74%; height: 6%; border: 1px dashed rgba(0,0,0,0.3);">
                                        NOMBRE COMPLETO DEL PARTICIPANTE
                                        <div class="field-label">Nombre</div>
                                        <div class="resize-handle" data-key="nombre"></div>
                                    </div>

                                    <div id="drag-dni" class="draggable-field fw-bold" data-key="dni"
                                         style="width: 15%; height: 5%; border: 1px dashed rgba(0,0,0,0.3);">
                                        12.345.678
                                        <div class="field-label">Cédula</div>
                                        <div class="resize-handle" data-key="dni"></div>
                                    </div>

                                    <div id="drag-qr" class="draggable-field bg-white d-flex align-items-center justify-content-center border border-dark" data-key="qr"
                                         style="width: 6.7%; height: 9.5%;">
                                        <i class="fas fa-qrcode" style="font-size: 1.5vw;"></i>
                                        <div class="field-label">Código QR</div>
                                        {{-- Handle derecho: solo ancho --}}
                                        <div class="resize-handle" data-key="qr" data-dir="right"></div>
                                        {{-- Handle inferior: solo alto --}}
                                        <div class="resize-handle-bottom" data-key="qr" data-dir="bottom"></div>
                                        {{-- Handle esquina: proporcional --}}
                                        <div class="resize-handle-corner" data-key="qr" data-dir="corner"></div>
                                    </div>

                                    <div id="drag-code" class="draggable-field text-center fw-bold" data-key="code"
                                         style="width: 17%; height: 3%; border: 1px dashed rgba(0,0,0,0.3);">
                                        {{ $curso->codigo }}-12345678
                                        <div class="field-label">Código</div>
                                        <div class="resize-handle" data-key="code"></div>
                                    </div>

                                    {{-- Firma Digital (arrastrable) --}}
                                    <div id="drag-firma" class="draggable-field firma-field" data-key="firma"
                                         style="width: 18%; height: 8%; border: 1px dashed rgba(180,0,0,0.5);">
                                        @if(!empty($firmaUrl))
                                        <img id="firma-canvas-img" src="{{ $firmaUrl }}" alt="Firma" style="max-width:100%; max-height:100%; object-fit:contain; pointer-events:none;">
                                        @else
                                        <span id="firma-canvas-placeholder" style="font-size:0.7em; color:#888; pointer-events:none;"><i class="fas fa-signature me-1"></i>Firma</span>
                                        @endif
                                        <div class="field-label" style="background:#dc3545;">Firma Prof.</div>
                                        {{-- Handle derecho: solo ancho --}}
                                        <div class="resize-handle" data-key="firma" data-dir="right" style="background:#dc3545;"></div>
                                        {{-- Handle inferior: solo alto --}}
                                        <div class="resize-handle-bottom" data-key="firma" data-dir="bottom"></div>
                                        {{-- Handle esquina: proporcional --}}
                                        <div class="resize-handle-corner" data-key="firma" data-dir="corner"></div>
                                    </div>

                                </div>
                            </div>
                            <div class="mt-2 text-center small text-muted">
                                <i class="fas fa-arrows-alt me-1"></i> Mover &nbsp;·&nbsp;
                                <i class="fas fa-arrows-alt-h me-1"></i> Cuadrito <span style="color:#0d6efd;"><b>azul</b></span> = extender ancho &nbsp;·&nbsp;
                                <i class="fas fa-signature me-1" style="color:#dc3545;"></i> Cuadrito <span style="color:#dc3545;"><b>rojo</b></span> = redimensionar firma
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .preview-wrapper {
        width: 100%;
        max-width: 1000px;
        margin: auto;
        overflow: visible;
    }
    #certificate-canvas {
        width: 100%;
        user-select: none;
        background-color: white;
    }
    .draggable-field {
        position: absolute;
        cursor: move;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-family: 'Arial', sans-serif;
        z-index: 10;
        white-space: nowrap;
        background-color: rgba(255, 255, 255, 0.4);
        user-select: none;
        -webkit-user-drag: none;
        pointer-events: auto !important;
    }
    .draggable-field:hover {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.1);
        z-index: 20;
    }
    .draggable-field.dragging {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.15);
        box-shadow: 0 4px 16px rgba(13,110,253,0.35);
        cursor: grabbing !important;
        z-index: 1000;
        outline: 2px solid #0d6efd;
    }
    .draggable-field .field-label {
        position: absolute;
        top: -18px;
        left: 0;
        font-size: 10px;
        background: #0d6efd;
        color: white;
        padding: 0 4px;
        border-radius: 2px;
        pointer-events: none;
        display: none;
        white-space: nowrap;
    }
    .draggable-field:hover .field-label,
    .draggable-field.dragging .field-label {
        display: block;
    }
    
    /* Handles de redimensionamiento */
    .resize-handle {
        position: absolute;
        right: -6px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 22px;
        background: #0d6efd;
        border-radius: 3px;
        cursor: ew-resize;
        z-index: 30;
        opacity: 0;
        transition: opacity 0.15s;
        pointer-events: auto;
    }
    .resize-handle::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 2px;
        height: 12px;
        background: rgba(255,255,255,0.7);
        border-radius: 1px;
        box-shadow: -3px 0 0 rgba(255,255,255,0.7), 3px 0 0 rgba(255,255,255,0.7);
    }
    
    .resize-handle-bottom {
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 22px;
        height: 12px;
        background: #dc3545;
        border-radius: 3px;
        cursor: ns-resize;
        z-index: 30;
        opacity: 0;
        transition: opacity 0.15s;
        pointer-events: auto;
    }
    .resize-handle-bottom::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 12px;
        height: 2px;
        background: rgba(255,255,255,0.7);
        border-radius: 1px;
        box-shadow: 0 -3px 0 rgba(255,255,255,0.7), 0 3px 0 rgba(255,255,255,0.7);
    }
    
    .resize-handle-corner {
        position: absolute;
        bottom: -6px;
        right: -6px;
        width: 14px;
        height: 14px;
        background: #6f42c1;
        border-radius: 3px;
        cursor: nwse-resize;
        z-index: 31;
        opacity: 0;
        transition: opacity 0.15s;
        pointer-events: auto;
    }
    .resize-handle-corner::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-right: 2px solid rgba(255,255,255,0.8);
        border-bottom: 2px solid rgba(255,255,255,0.8);
        border-radius: 1px;
    }
    
    .draggable-field:hover .resize-handle,
    .draggable-field.resizing .resize-handle,
    .draggable-field:hover .resize-handle-bottom,
    .draggable-field.resizing .resize-handle-bottom,
    .draggable-field:hover .resize-handle-corner,
    .draggable-field.resizing .resize-handle-corner {
        opacity: 1;
    }
    
    .draggable-field.resizing {
        border-color: #198754 !important;
        background-color: rgba(25,135,84,0.1);
        box-shadow: 0 4px 16px rgba(25,135,84,0.3);
        cursor: ew-resize !important;
        z-index: 1000;
        outline: 2px solid #198754;
    }
    
    .firma-field {
        background-color: transparent !important;
        overflow: hidden;
    }
    .firma-field:hover {
        background-color: rgba(220, 53, 69, 0.07) !important;
        border-color: #dc3545 !important;
    }
    .firma-field.dragging {
        outline-color: #dc3545 !important;
        border-color: #dc3545 !important;
        box-shadow: 0 4px 16px rgba(220,53,69,0.3) !important;
    }

    /* Elemento seleccionado con clic o con el panel de precisión */
    .draggable-field.selected {
        outline: 2px solid #198754;
        z-index: 25;
    }
    .draggable-field.selected .field-label {
        display: block;
        background: #198754;
    }

    /* Rejilla de 5 mm (snap visual). 5mm en % del lienzo A4. */
    #certificate-canvas.grid-on::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 5;
        background-image:
            linear-gradient(to right, rgba(13,110,253,0.12) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(13,110,253,0.12) 1px, transparent 1px);
        background-size: calc(100% / (297 / 5)) calc(100% / (210 / 5));
    }

    /* En táctil no hay hover: handles siempre visibles y más grandes. */
    @media (pointer: coarse) {
        .resize-handle,
        .resize-handle-bottom,
        .resize-handle-corner {
            opacity: 1;
            width: 20px;
            height: 20px;
        }
        .resize-handle {
            right: -10px;
            height: 30px;
        }
        .resize-handle-bottom {
            bottom: -10px;
            width: 30px;
        }
        .resize-handle-corner {
            bottom: -10px;
            right: -10px;
        }
    }
    
    #coord-list .coord-item {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px solid #eee;
    }
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
</style>

<!-- Modal: vista previa PDF en vivo -->
<div class="modal fade" id="pdf-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1100px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white py-3 px-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-pdf me-2"></i> Vista previa del certificado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div id="pdf-preview-aviso" class="alert alert-info mb-3"></div>
                @if(!empty($esWizard))
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="fas fa-info-circle me-1"></i> Muestra con tus datos y un QR de ejemplo (el curso aún no existe).
                </div>
                @endif
                <iframe id="pdf-preview-frame" title="Vista previa PDF" style="width:100%; height:min(65vh, 600px); border:0; background:#fff;" class="rounded shadow-sm"></iframe>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="actualizarPruebaPdf()">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar vista
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ─── Constantes FPDF ───────────────────────────────────────────────
    const FPDF_W = 297;   // mm ancho A4 landscape
    const FPDF_H = 210;   // mm alto  A4 landscape
    const PT_TO_MM = 0.3527;

    // ─── Estado mutable de coordenadas ─────────────────────────────────
    let coords = @json($coords);

    // Base institucional para "Restablecer elemento" (nunca se muta).
    const COORDS_BASE = @json(\Modules\Taller\Services\CertificadoConfig::coordsBase());

    // ─── Snap a rejilla + selección + zoom ─────────────────────────────
    const SNAP_MM = 5;
    let snapActivo = true;
    let selectedKey = null;
    let zoomPct = 100;
    let teclaTimer = null;

    const snap = (v) => snapActivo ? Math.round(v / SNAP_MM) * SNAP_MM : v;

    function toggleSnap() {
        snapActivo = !snapActivo;
        canvas.classList.toggle('grid-on', snapActivo);
        const btn = document.getElementById('btn-snap');
        if (btn) {
            btn.classList.toggle('active', snapActivo);
            btn.classList.toggle('btn-primary', snapActivo);
            btn.classList.toggle('btn-light', !snapActivo);
        }
    }

    function zoomCanvas(delta) {
        zoomPct = Math.max(50, Math.min(150, zoomPct + delta));
        const wrap = document.getElementById('canvas-wrapper');
        if (wrap) wrap.style.maxWidth = (1000 * zoomPct / 100) + 'px';
        const lbl = document.getElementById('zoom-label');
        if (lbl) lbl.textContent = zoomPct + '%';
        updatePositions();
    }

    function seleccionarElemento(key) {
        selectedKey = (key && coords[key]) ? key : null;
        canvas.querySelectorAll('.draggable-field').forEach((el) => {
            el.classList.toggle('selected', el.getAttribute('data-key') === selectedKey);
        });
        const sel = document.getElementById('precision-key');
        if (sel && selectedKey) sel.value = selectedKey;
        syncPrecisionPanel();
    }

    function restablecerElemento() {
        const key = selectedKey || document.getElementById('precision-key')?.value;
        if (!key || !COORDS_BASE[key]) return;
        coords[key] = JSON.parse(JSON.stringify(COORDS_BASE[key]));
        updatePositions();
        updatePanel();
        saveState();
    }

    // Guarda con coalescencia (teclas/flechas): evita inflar el historial.
    function saveStateSoon() {
        clearTimeout(teclaTimer);
        teclaTimer = setTimeout(saveState, 500);
    }

    function syncPrecisionPanel() {
        const key = selectedKey || document.getElementById('precision-key')?.value || 'nombre';
        const c = coords[key];
        if (!c) return;
        const set = (id, v) => { const i = document.getElementById(id); if (i) i.value = (v ?? ''); };
        set('precision-x', (+c.x).toFixed(1));
        set('precision-y', (+c.y).toFixed(1));
        set('precision-w', c.w != null ? (+c.w).toFixed(1) : '');
        set('precision-h', c.h != null ? (+c.h).toFixed(1) : '');
    }

    function posicionExacta() {
        const key = document.getElementById('precision-key')?.value;
        if (!key || !coords[key]) return;
        const get = (id, min, max, actual) => {
            const v = parseFloat(document.getElementById(id)?.value);
            if (Number.isNaN(v)) return actual;
            return Math.max(min, Math.min(v, max));
        };
        coords[key].x = get('precision-x', 0, FPDF_W, coords[key].x);
        coords[key].y = get('precision-y', 0, FPDF_H, coords[key].y);
        if (coords[key].w != null) coords[key].w = get('precision-w', 5, FPDF_W, coords[key].w);
        if (coords[key].h != null) coords[key].h = get('precision-h', 5, FPDF_H, coords[key].h);
        if (!selectedKey) seleccionarElemento(key);
        updatePositions();
        updatePanel();
        saveStateSoon();
    }

    // ─── Referencias DOM ───────────────────────────────────────────────
    const canvas       = document.getElementById('certificate-canvas');
    const coordsInput  = document.getElementById('coords-input');
    const coordList    = document.getElementById('coord-list');
    const btnUndo      = document.getElementById('btn-undo');
    const btnRedo      = document.getElementById('btn-redo');

    // ─── Historial para Deshacer/Rehacer (Undo/Redo) ───────────────────
    const history = [];
    let historyIndex = -1;

    // Autoguardado del wizard: persiste coords.json sin salir del editor.
    const ES_WIZARD = @json(!empty($esWizard));
    const AUTOSAVE_URL = @json(!empty($esWizard) ? route('taller.cursos.wizard.plantilla.store') : '');
    let autosaveTimer = null;
    let lastSavedCoords = '';

    function programarAutoguardado() {
        if (!ES_WIZARD || !AUTOSAVE_URL) return;
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(enviarAutoguardado, 800);
    }

    async function enviarAutoguardado() {
        const serial = JSON.stringify(coords);
        if (serial === lastSavedCoords) return;
        const tokenInput = document.querySelector('#form-plantilla input[name="_token"]');
        try {
            const resp = await fetch(AUTOSAVE_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenInput ? tokenInput.value : '',
                },
                body: JSON.stringify({ coords: serial }),
            });
            if (resp.ok) {
                lastSavedCoords = serial;
                const aviso = document.getElementById('autosave-aviso');
                if (aviso) {
                    const hora = new Date().toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    aviso.textContent = 'Borrador guardado ' + hora;
                    aviso.style.display = '';
                }
            }
        } catch (e) {
            // Sin red o sesión expirada: el submit clásico sigue disponible.
        }
    }

    function saveState() {
        const serialized = JSON.stringify(coords);
        if (historyIndex >= 0 && history[historyIndex] === serialized) return;
        if (historyIndex < history.length - 1) {
            history.splice(historyIndex + 1);
        }
        history.push(serialized);
        historyIndex = history.length - 1;
        updateUndoRedoButtons();
        programarAutoguardado();
    }

    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            applyState(history[historyIndex]);
        }
    }

    function redo() {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            applyState(history[historyIndex]);
        }
    }

    function applyState(serializedState) {
        const state = JSON.parse(serializedState);
        Object.keys(state).forEach(key => {
            coords[key] = state[key];
        });
        
        // Actualizar sliders
        Object.keys(coords).forEach(key => {
            if (coords[key] && coords[key].size) {
                const slider = document.querySelector(`.font-slider[data-key="${key}"]`);
                if (slider) slider.value = coords[key].size;
            }
        });

        updatePositions();
        updatePanel();
        updateUndoRedoButtons();
    }

    function updateUndoRedoButtons() {
        if (btnUndo) btnUndo.disabled = (historyIndex <= 0);
        if (btnRedo) btnRedo.disabled = (historyIndex >= history.length - 1);
    }

    // ─── updatePositions: mueve elementos del canvas ────────────────────
    function updatePositions() {
        const cw = canvas.offsetWidth;
        const ch = canvas.offsetHeight;
        const pxPerMm = cw / FPDF_W;

        Object.keys(coords).forEach(key => {
            const el = canvas.querySelector(`.draggable-field[data-key="${key}"]`);
            if (!el) return;
            el.style.left = (coords[key].x / FPDF_W * 100) + '%';
            el.style.top  = (coords[key].y / FPDF_H * 100) + '%';
            if (coords[key].w) {
                el.style.width = (coords[key].w / FPDF_W * 100) + '%';
            }
            if (coords[key].h) {
                el.style.height = (coords[key].h / FPDF_H * 100) + '%';
            }
            if (coords[key].size) {
                el.style.fontSize = (coords[key].size * PT_TO_MM * pxPerMm) + 'px';
            }
        });

        coordsInput.value = JSON.stringify(coords);
    }

    // ─── updatePanel: refresca el panel lateral ──────────────────────────
    function updatePanel() {
        Object.keys(coords).forEach(key => {
            const lbl = document.getElementById(`val-${key}`);
            if (lbl && coords[key].size) lbl.innerText = coords[key].size + 'pt';
        });

        syncPrecisionPanel();

        coordList.innerHTML = Object.keys(coords).map(key => `
            <div class="coord-item">
                <span class="text-capitalize fw-bold">${key}</span>
                <span>
                    <span class="badge bg-light text-dark border">X:${coords[key].x.toFixed(1)}</span>
                    <span class="badge bg-light text-dark border">Y:${coords[key].y.toFixed(1)}</span>
                    ${coords[key].w    ? `<span class="badge bg-success ms-1">W:${coords[key].w.toFixed(0)}mm</span>` : ''}
                    ${coords[key].h    ? `<span class="badge bg-info ms-1">H:${coords[key].h.toFixed(0)}mm</span>` : ''}
                    ${coords[key].size ? `<span class="badge bg-primary ms-1">${coords[key].size}pt</span>` : ''}
                </span>
            </div>`).join('');
    }

    // ─── Actualizar fuente desde slider ────────────────────────────────
    function updateFontSize(input) {
        const key = input.getAttribute('data-key');
        coords[key].size = parseInt(input.value);
        updatePositions();
        updatePanel();
    }

    // ─── Drag & Drop (mouse + touch) ───────────────────────────────────
    let drag = null; // { el, key, ox, oy }

    function getPointer(e) {
        if (e.touches && e.touches.length > 0) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
        return { x: e.clientX, y: e.clientY };
    }

    function onDragStart(e) {
        const el = e.currentTarget;
        e.preventDefault();
        e.stopPropagation();

        const key = el.getAttribute('data-key');
        const p   = getPointer(e);
        const cr  = canvas.getBoundingClientRect();
        const er  = el.getBoundingClientRect();

        drag = {
            el,
            key,
            ox: p.x - er.left,
            oy: p.y - er.top,
        };

        el.classList.add('dragging');

        document.addEventListener('mousemove', onDragMove, { passive: false });
        document.addEventListener('mouseup',   onDragEnd);
        document.addEventListener('touchmove', onDragMove, { passive: false });
        document.addEventListener('touchend',  onDragEnd);
    }

    function onDragMove(e) {
        if (!drag) return;
        e.preventDefault();

        const p  = getPointer(e);
        const cr = canvas.getBoundingClientRect();

        let newLeft = p.x - cr.left - drag.ox;
        let newTop  = p.y - cr.top  - drag.oy;

        const maxL = cr.width  - drag.el.offsetWidth;
        const maxT = cr.height - drag.el.offsetHeight;
        newLeft = Math.max(0, Math.min(newLeft, maxL));
        newTop  = Math.max(0, Math.min(newTop,  maxT));

        drag.el.style.left = newLeft + 'px';
        drag.el.style.top  = newTop  + 'px';

        coords[drag.key].x = snap((newLeft / cr.width)  * FPDF_W);
        coords[drag.key].y = snap((newTop  / cr.height) * FPDF_H);

        coordsInput.value = JSON.stringify(coords);
        updatePanel();
    }

    function onDragEnd() {
        if (!drag) return;
        drag.el.classList.remove('dragging');
        updatePositions();
        saveState();
        drag = null;
        document.removeEventListener('mousemove', onDragMove);
        document.removeEventListener('mouseup',   onDragEnd);
        document.removeEventListener('touchmove', onDragMove);
        document.removeEventListener('touchend',  onDragEnd);
    }

    // ─── Resize (ancho / alto / esquina proporcional) ────────────────
    let resz = null; // { el, key, dir, startX, startY, startW, startH }

    function onResizeStart(e) {
        e.preventDefault();
        e.stopPropagation();

        const handle = e.currentTarget;
        const key = handle.getAttribute('data-key');
        const dir = handle.getAttribute('data-dir') || 'right'; // right | bottom | corner
        const el  = canvas.querySelector(`.draggable-field[data-key="${key}"]`);
        const p   = getPointer(e);
        const cr  = canvas.getBoundingClientRect();

        // Inicializar w/h en coords si no existen
        if (!coords[key].w) coords[key].w = (el.offsetWidth  / cr.width)  * FPDF_W;
        if (!coords[key].h) coords[key].h = (el.offsetHeight / cr.height) * FPDF_H;

        resz = {
            el, key, dir,
            startX : p.x,
            startY : p.y,
            startW : coords[key].w,
            startH : coords[key].h,
        };

        el.classList.add('resizing');
        document.addEventListener('mousemove', onResizeMove, { passive: false });
        document.addEventListener('mouseup',   onResizeEnd);
        document.addEventListener('touchmove', onResizeMove, { passive: false });
        document.addEventListener('touchend',  onResizeEnd);
    }

    function onResizeMove(e) {
        if (!resz) return;
        e.preventDefault();

        const p  = getPointer(e);
        const cr = canvas.getBoundingClientRect();
        const pxPerMmW = cr.width  / FPDF_W;
        const pxPerMmH = cr.height / FPDF_H;

        const dX = (p.x - resz.startX) / pxPerMmW;
        const dY = (p.y - resz.startY) / pxPerMmH;

        const maxW = FPDF_W - coords[resz.key].x;
        const maxH = FPDF_H - coords[resz.key].y;

        if (resz.dir === 'corner') {
            const ratio = resz.startW / resz.startH;
            let newW = Math.max(10, Math.min(resz.startW + dX, maxW));
            let newH = newW / ratio;

            if (newH > maxH) {
                newH = maxH;
                newW = newH * ratio;
            }
            if (newH < 5) {
                newH = 5;
                newW = newH * ratio;
            }

            coords[resz.key].w = snap(newW);
            coords[resz.key].h = snap(newH);
            resz.el.style.width = (newW / FPDF_W * 100) + '%';
            resz.el.style.height = (newH / FPDF_H * 100) + '%';
        } else {
            if (resz.dir === 'right') {
                const newW = Math.max(10, Math.min(resz.startW + dX, maxW));
                coords[resz.key].w = snap(newW);
                resz.el.style.width = (newW / FPDF_W * 100) + '%';
            }
            if (resz.dir === 'bottom') {
                const newH = Math.max(5, Math.min(resz.startH + dY, maxH));
                coords[resz.key].h = snap(newH);
                resz.el.style.height = (newH / FPDF_H * 100) + '%';
            }
        }

        coordsInput.value = JSON.stringify(coords);
        updatePanel();
    }

    function onResizeEnd() {
        if (!resz) return;
        resz.el.classList.remove('resizing');
        updatePositions();
        saveState();
        resz = null;
        document.removeEventListener('mousemove', onResizeMove);
        document.removeEventListener('mouseup',   onResizeEnd);
        document.removeEventListener('touchmove', onResizeMove);
        document.removeEventListener('touchend',  onResizeEnd);
    }

    function initDrag() {
        canvas.querySelectorAll('.draggable-field').forEach(el => {
            el.addEventListener('mousedown', e => {
                seleccionarElemento(el.getAttribute('data-key'));
                if (e.target.classList.contains('resize-handle') ||
                    e.target.classList.contains('resize-handle-bottom') ||
                    e.target.classList.contains('resize-handle-corner')) return;
                onDragStart(e);
            });
            el.addEventListener('touchstart', e => {
                seleccionarElemento(el.getAttribute('data-key'));
                if (e.target.classList.contains('resize-handle') ||
                    e.target.classList.contains('resize-handle-bottom') ||
                    e.target.classList.contains('resize-handle-corner')) return;
                onDragStart(e);
            }, { passive: false });
            el.addEventListener('dragstart', e => e.preventDefault());
        });

        canvas.querySelectorAll('.resize-handle').forEach(h => {
            h.addEventListener('mousedown',  onResizeStart);
            h.addEventListener('touchstart', onResizeStart, { passive: false });
        });
        canvas.querySelectorAll('.resize-handle-bottom, .resize-handle-corner').forEach(h => {
            h.addEventListener('mousedown',  onResizeStart);
            h.addEventListener('touchstart', onResizeStart, { passive: false });
        });
    }

    // ─── Validación de imágenes antes de subirlas ──────────────────────
    // Espejo en JS de Modules\Taller\Rules\ImagenCertificado. Avisa con Swal en
    // el momento de elegir el archivo, sin subir nada ni recargar la página.
    const LIMITES_CERTIFICADO = {
        plantilla: { etiqueta: 'La plantilla', maxMB: 4,    maxPx: 4000, maxAltoSobreAncho: 1, obligatorioHorizontal: true },
        firma:     { etiqueta: 'La firma',     maxMB: 2,    maxPx: 3000, maxAltoSobreAncho: 4, obligatorioHorizontal: false }
    };

    function avisarImagenInvalida(titulo, texto) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire({
                icon: 'warning',
                title: titulo,
                text: texto,
                confirmButtonColor: '#f0a500',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert(titulo + '\n\n' + texto);
        }
    }

    function validarImagenCertificado(file, tipo) {
        return new Promise(resolve => {
            const L = LIMITES_CERTIFICADO[tipo];
            if (!file || !L) { resolve(null); return; }

            // 1) Peso
            const mb = file.size / (1024 * 1024);
            if (mb > L.maxMB) {
                resolve(`"${L.etiqueta}" pesa ${mb.toFixed(1).replace('.', ',')} MB y el máximo es ${L.maxMB} MB. `
                      + `Optimiza la imagen (por ejemplo, expórtala como JPG con calidad 80) e inténtalo de nuevo.`);
                return;
            }

            // 2) Dimensiones reales
            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                URL.revokeObjectURL(url);
                const ancho = img.naturalWidth;
                const alto = img.naturalHeight;

                if (ancho > L.maxPx || alto > L.maxPx) {
                    resolve(`"${L.etiqueta}" mide ${ancho} x ${alto} px y el máximo es ${L.maxPx} px por lado. `
                          + `Reduce la resolución e inténtalo de nuevo.`);
                    return;
                }

                if (alto > ancho * L.maxAltoSobreAncho) {
                    resolve(L.obligatorioHorizontal
                        ? `"${L.etiqueta}" es demasiado larga: mide ${ancho} x ${alto} px y es más alta que ancha. `
                          + `El certificado es horizontal (A4, 297 x 210 mm), así que necesitas una imagen apaisada.`
                        : `"${L.etiqueta}" es demasiado larga: mide ${ancho} x ${alto} px. `
                          + `Usa una firma más horizontal.`);
                    return;
                }

                resolve(null);
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                resolve(`No pudimos leer "${L.etiqueta}". Verifica que sea un archivo PNG o JPG válido.`);
            };

            img.src = url;
        });
    }

    async function revisarImagen(input, tipo) {
        if (!input.files || !input.files[0]) return true;

        const error = await validarImagenCertificado(input.files[0], tipo);
        if (error) {
            avisarImagenInvalida('Imagen no válida', error);
            input.value = '';   // evita que se envíe un archivo que ya rechazamos
            return false;
        }
        return true;
    }

    // ─── Previews ──────────────────────────────────────────────────────
    async function previewImage(input) {
        if (!(await revisarImagen(input, 'plantilla'))) return;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                canvas.style.backgroundImage = `url('${e.target.result}')`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    async function previewFirma(input) {
        if (!(await revisarImagen(input, 'firma'))) return;
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const src = e.target.result;
            const prevImg  = document.getElementById('firma-preview-img');
            const prevWrap = document.getElementById('firma-preview-wrap');
            if (prevImg)  prevImg.src = src;
            if (prevWrap) prevWrap.style.display = 'block';

            const firmaEl = canvas.querySelector('[data-key="firma"]');
            if (firmaEl) {
                const ph = firmaEl.querySelector('#firma-canvas-placeholder');
                if (ph) ph.remove();

                let img = firmaEl.querySelector('#firma-canvas-img');
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'firma-canvas-img';
                    img.alt = 'Firma';
                    img.style.cssText = 'max-width:100%; max-height:100%; object-fit:contain; pointer-events:none;';
                    firmaEl.prepend(img);
                }
                img.src = src;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }

        // ─── Vista previa PDF en vivo (modal) ─────────────────────────────
    const ES_WIZARD_PDF = @json(!empty($esWizard));
    const PRUEBA_WIZARD_URL = @json(!empty($esWizard) ? route('taller.certificados.prueba.wizard') : '');
    const PRUEBA_CURSO_URL = @json(empty($esWizard) && !empty($curso->crypt_id) ? route('taller.certificados.probar', $curso->crypt_id) : '');
    let pdfObjUrl = null;

    function abrirPruebaPdf() {
        const modalEl = document.getElementById('pdf-preview-modal');
        if (!modalEl) return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        actualizarPruebaPdf();
    }

    async function actualizarPruebaPdf() {
        const frame = document.getElementById('pdf-preview-frame');
        const aviso = document.getElementById('pdf-preview-aviso');
        if (!frame) return;
        frame.removeAttribute('src');
        if (aviso) {
            aviso.style.display = '';
            aviso.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generando vista previa...';
        }
        try {
            let resp;
            if (ES_WIZARD_PDF && PRUEBA_WIZARD_URL) {
                const tokenInput = document.querySelector('#form-plantilla input[name="_token"]');
                const datos = new FormData();
                datos.append('_token', tokenInput ? tokenInput.value : '');
                datos.append('coords', JSON.stringify(coords));

                // Las imágenes elegidas aún no se han guardado en el servidor:
                // se envían con la prueba para que el PDF las dibuje igual que
                // el lienzo, sin obligar a pulsar antes "Continuar a Requisitos".
                const plantillaInput = document.querySelector('#form-plantilla input[name="plantilla"]');
                if (plantillaInput && plantillaInput.files && plantillaInput.files[0]) {
                    datos.append('plantilla', plantillaInput.files[0]);
                }

                const firmaInput = document.querySelector('#form-plantilla input[name="firma"]');
                if (firmaInput && firmaInput.files && firmaInput.files[0]) {
                    datos.append('firma', firmaInput.files[0]);
                }

                resp = await fetch(PRUEBA_WIZARD_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/pdf, application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': tokenInput ? tokenInput.value : '',
                    },
                    body: datos,
                });
            } else if (PRUEBA_CURSO_URL) {
                resp = await fetch(PRUEBA_CURSO_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            } else {
                throw new Error('sin-origen');
            }
            if (!resp.ok) {
                let detalle = '';
                try {
                    const error = await resp.json();
                    detalle = error.error || error.message || '';
                } catch (_) {
                    // Respuesta no JSON (419, 500, etc.): se usa el mensaje genérico.
                }
                throw new Error(detalle || ('http-' + resp.status));
            }
            const blob = await resp.blob();
            if (pdfObjUrl) URL.revokeObjectURL(pdfObjUrl);
            pdfObjUrl = URL.createObjectURL(blob);
            frame.src = pdfObjUrl;
            if (aviso) aviso.style.display = 'none';
        } catch (e) {
            const detalle = (e && e.message && e.message !== 'sin-origen' && !e.message.startsWith('http-'))
                ? e.message
                : 'No se pudo generar la vista previa. Guarda el diseño e inténtalo de nuevo.';

            if (aviso) {
                aviso.textContent = '';
                const texto = document.createElement('span');
                texto.className = 'text-danger';

                const icono = document.createElement('i');
                icono.className = 'fas fa-exclamation-triangle me-1';
                texto.appendChild(icono);
                texto.appendChild(document.createTextNode(detalle));

                aviso.appendChild(texto);
            }
        }
    }

    // ─── Inicialización ────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {        // QR: cuadrado por defecto
        if (!coords.qr)    coords.qr    = { x: 15.0,  y: 135.0, w: 20, h: 20 };
        if (!coords.qr.h)  coords.qr.h  = coords.qr.w ?? 20;

        // Firma: inicializar si no existe
        if (!coords.firma) {
            coords.firma = { x: 200.0, y: 170.0, w: 50, h: 20 };
        } else {
            if (!coords.firma.h) coords.firma.h = coords.firma.w * 0.4;
        }

        initDrag();
        updatePositions();
        updatePanel();

        saveState(); // Guardar estado inicial

        // Registrar cambios en sliders
        document.querySelectorAll('.font-slider').forEach(slider => {
            slider.addEventListener('change', () => {
                saveState();
            });
        });

        // Atajos de teclado
        document.addEventListener('keydown', e => {
            // No robar teclas mientras se escribe en inputs del panel
            const tag = (e.target?.tagName || '').toLowerCase();
            const escribiendo = tag === 'input' || tag === 'textarea' || tag === 'select';
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey) {
                if (e.key.toLowerCase() === 'z') {
                    e.preventDefault();
                    undo();
                } else if (e.key.toLowerCase() === 'y') {
                    e.preventDefault();
                    redo();
                }
            } else if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'z') {
                e.preventDefault();
                redo();
            } else if (!escribiendo && selectedKey && coords[selectedKey]) {
                // Mover el elemento seleccionado con flechas (Shift = 5 mm)
                const paso = (e.shiftKey ? SNAP_MM : 1);
                const c = coords[selectedKey];
                let movio = true;
                if (e.key === 'ArrowLeft')       c.x = Math.max(0, (c.x ?? 0) - paso);
                else if (e.key === 'ArrowRight') c.x = Math.min(FPDF_W, (c.x ?? 0) + paso);
                else if (e.key === 'ArrowUp')    c.y = Math.max(0, (c.y ?? 0) - paso);
                else if (e.key === 'ArrowDown')  c.y = Math.min(FPDF_H, (c.y ?? 0) + paso);
                else if (e.key === 'Escape')     seleccionarElemento(null);
                else movio = false;
                if (movio && e.key !== 'Escape') {
                    e.preventDefault();
                    updatePositions();
                    updatePanel();
                    saveStateSoon();
                }
            } else if (!escribiendo && e.key === 'Escape') {
                seleccionarElemento(null);
            }
        });

        canvas.classList.add('grid-on');

        window.addEventListener('resize', updatePositions);
    });
</script>
@endsection
