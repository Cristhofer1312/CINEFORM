@php
    $isExisting = isset($contenido) && $contenido;
    $tipoContenido = $isExisting ? ($contenido->tipo_contenido ?? 'texto') : 'texto';
    $showFileInput = $tipoContenido === 'archivo';
    $showUrlInput = $tipoContenido === 'enlace';
    $filePreview = '';
    $fileUrl = '';
    
    if ($isExisting && $contenido->url_contenido) {
        if ($tipoContenido === 'archivo') {
            $fileUrl = $contenido->url_contenido;
            $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $videoExtensions = ['mp4', 'webm', 'ogg'];
            
            if (in_array(strtolower($extension), $imageExtensions)) {
                $filePreview = '<img src="' . $fileUrl . '" class="img-fluid" style="max-height: 150px;">';
            } elseif (in_array(strtolower($extension), $videoExtensions)) {
                $filePreview = '<video controls class="w-100" style="max-height: 150px;"><source src="' . $fileUrl . '" type="video/' . $extension + '"></video>';
            } elseif (strtolower($extension) === 'pdf') {
                $filePreview = '<div class="alert alert-info p-2"><i class="fas fa-file-pdf fa-2x me-2"></i> Archivo PDF: ' . basename($fileUrl) . '</div>';
            } else {
                $filePreview = '<div class="alert alert-secondary p-2"><i class="fas fa-file-alt me-2"></i> ' . basename($fileUrl) . '</div>';
            }
        } else if ($tipoContenido === 'enlace') {
            $fileUrl = $contenido->url_contenido;
        }
    }
@endphp

<div class="content-item mb-3" data-index="{{ $index }}" @if($isExisting) data-content-id="{{ $contenido->id }}" @endif>
    <div class="content-actions">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeContentItem(this)" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    </div>
    
    <input type="hidden" name="contenidos[{{ $index }}][id]" value="{{ $isExisting ? $contenido->id : '' }}">
    
    <div class="mb-3">
        <label for="titulo_{{ $index }}" class="form-label">Título del Contenido</label>
        <input type="text" class="form-control" id="titulo_{{ $index }}" 
               name="contenidos[{{ $index }}][titulo]" 
               value="{{ $isExisting ? $contenido->titulo : '' }}" required>
    </div>
    
    <div class="mb-3">
        <label for="descripcion_breve_{{ $index }}" class="form-label">Descripción Breve</label>
        <input type="text" class="form-control" id="descripcion_breve_{{ $index }}" 
               name="contenidos[{{ $index }}][descripcion_breve]" 
               value="{{ $isExisting ? ($contenido->descripcion_breve ?? '') : '' }}" 
               placeholder="Una breve descripción que aparecerá en la lista de contenidos">
    </div>
    
    <div class="mb-3">
        <label for="tipo_contenido_{{ $index }}" class="form-label">Tipo de Contenido</label>
        <select class="form-select" id="tipo_contenido_{{ $index }}" 
                name="contenidos[{{ $index }}][tipo_contenido]" 
                onchange="changeContentType(this, {{ $index }})">
            <option value="texto" {{ $tipoContenido === 'texto' ? 'selected' : '' }}>Solo Texto</option>
            <option value="archivo" {{ $tipoContenido === 'archivo' ? 'selected' : '' }}>Archivo (PDF, Imagen, Video)</option>
            <option value="enlace" {{ $tipoContenido === 'enlace' ? 'selected' : '' }}>Enlace Externo</option>
        </select>
    </div>
    
    <!-- Grupo para subir archivo -->
    <div id="fileGroup_{{ $index }}" class="mb-3" style="display: {{ $showFileInput ? 'block' : 'none' }}">
        <label for="archivo_{{ $index }}" class="form-label">Subir Archivo</label>
        <input type="file" class="form-control" id="archivo_{{ $index }}" 
               name="contenidos[{{ $index }}][archivo]" 
               onchange="handleFileSelect(this, 'filePreview_{{ $index }}')"
               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.mp4,.webm,.ogg">
        
        @if($isExisting && $tipoContenido === 'archivo' && $fileUrl)
            <div class="mt-2">
                <p class="mb-1">Archivo actual:</p>
                <div class="d-flex align-items-center">
                    <a href="#" onclick="previewFile('{{ $fileUrl }}', '{{ str_contains($fileUrl, '.pdf') ? 'pdf' : (str_contains($fileUrl, ['.mp4', '.webm', '.ogg']) ? 'video' : 'image') }}')" 
                       class="me-2">
                        Ver archivo actual
                    </a>
                    <a href="{{ $fileUrl }}" download class="text-muted small" title="Descargar">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <input type="hidden" name="contenidos[{{ $index }}][archivo_actual]" value="{{ $fileUrl }}">
            </div>
        @endif
        
        <div id="filePreview_{{ $index }}" class="mt-2">
            @if($isExisting && $tipoContenido === 'archivo' && $filePreview)
                {!! $filePreview !!}
            @endif
        </div>
    </div>
    
    <!-- Grupo para URL de enlace -->
    <div id="urlGroup_{{ $index }}" class="mb-3" style="display: {{ $showUrlInput ? 'block' : 'none' }}">
        <label for="url_contenido_{{ $index }}" class="form-label">URL del Contenido</label>
        <input type="url" class="form-control" id="url_contenido_{{ $index }}" 
               name="contenidos[{{ $index }}][url_contenido]" 
               value="{{ $isExisting && $tipoContenido === 'enlace' ? $contenido->url_contenido : '' }}"
               placeholder="https://ejemplo.com/contenido">
    </div>
    
    <div class="mb-3">
        <label for="descripcion_{{ $index }}" class="form-label">Descripción Detallada</label>
        <textarea class="form-control summernote" id="descripcion_{{ $index }}" 
                  name="contenidos[{{ $index }}][descripcion]" rows="3">
            {{ $isExisting ? ($contenido->descripcion ?? '') : '' }}
        </textarea>
    </div>
    
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="activo_{{ $index }}" 
               name="contenidos[{{ $index }}][activo]" value="1" 
               {{ $isExisting && $contenido->activo ? 'checked' : 'checked' }}>
        <label class="form-check-label" for="activo_{{ $index }}">Contenido Activo</label>
    </div>
    
    <hr class="my-4">
</div>
