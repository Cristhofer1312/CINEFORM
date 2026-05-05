<div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas de Sesión -->
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show" role="alert">
                    <div class="icon-shape icon-sm bg-success-light text-success rounded-circle me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div><h6 class="mb-0 fw-bold">¡Guardado con éxito!</h6><small>{{ session('success') }}</small></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Encabezado de la Página -->
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Configuración Maestra</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Edición de Curso </h2>
                    <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1 text-primary"></i> Gestiona la descripción y el currículo de tu programa educativo.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Volver al Panel
                    </a>
                </div>
            </div>

                {{-- ══ Historial de Revisiones (visible solo si existen observaciones) ══ --}}
                @if($curso->observaciones && $curso->observaciones->count() > 0)
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-start border-4 border-warning">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <div class="icon-box bg-warning text-white rounded-3 me-3 p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-clipboard-list fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Historial de Revisiones</h5>
                            <p class="text-muted small mb-0">Observaciones de la coordinación sobre este curso. Revísalas para mejorar tu propuesta.</p>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill ms-auto px-3 py-2 fw-bold">{{ $curso->observaciones->count() }}</span>
                    </div>
                    <div class="card-body p-4">
                        @foreach($curso->observaciones as $obs)
                        <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="mb-1 text-dark" style="line-height: 1.6;">{{ $obs->observacion }}</p>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>{{ $obs->created_at->format('d/m/Y H:i') }}
                                    @if($obs->autor) — <i class="far fa-user me-1"></i>{{ $obs->autor->username ?? $obs->autor->name ?? '' }} @endif
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            <form action="{{ route('taller.cursos.update', $curso->id_curso) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                        <div class="fw-bold mb-2 small uppercase"><i class="fas fa-exclamation-circle me-2"></i> Errores detectados:</div>
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Datos Generales Card -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                            <i class="fas fa-cog fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Parámetros Maestros</h5>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <!-- Fila 1: Identidad -->
                            <div class="col-md-7">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-heading me-2 opacity-50"></i> NOMBRE OFICIAL DEL CURSO
                                </label>
                                <div class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2" style="height: auto; min-height: 58px;">
                                    {{ $curso->nombre ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-chalkboard-teacher me-2 opacity-50"></i> MODALIDAD
                                </label>
                                <div class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2 text-center" style="height: auto; min-height: 58px;">
                                    {{ $curso->modalidad->nombre_modalidad ?? 'N/A' }}
                                </div>
                            </div>

                            <!-- Fila 2: Métricas Rápidas -->
                            <div class="col-md-3">  
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-calendar-week me-2 opacity-50"></i> DURACIÓN
                                </label>
                                <div class="input-group">
                                    <div class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3 text-primary fs-5">
                                        {{ $curso->duracion ?? '0' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-clock me-2 opacity-50"></i> HORAS
                                </label>
                                <div class="input-group">
                                    <div class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3 text-primary fs-5">
                                        {{ $curso->horas ?? '0' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-play-circle me-2 opacity-50 text-success"></i> Apertura de curso
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-check"></i></span>
                                    <div class="form-control border-2 border-start-0 py-2 border-light-2 fw-bold">
                                        {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-stop-circle me-2 opacity-50 text-danger"></i> CIERRE DE CURSO
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-minus"></i></span>
                                    <div class="form-control border-2 border-start-0 py-2 border-light-2 fw-bold">
                                        {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 3: Descripción Editable -->
                            <div class="col-12">
                                <label for="descripcion" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 opacity-50"></i> SÍNTESIS CURRICULAR DEL PROGRAMA
                                </label>
                                <textarea class="form-control border-2 shadow-none rounded-4 bg-white p-3 border-light-2" id="descripcion" name="descripcion"
                                    rows="4" placeholder="Describe brevemente de qué trata este curso..." style="font-size: 1rem;">{{ old('descripcion', $curso->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>
                          <!-- Sección: Currículo del Curso -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white py-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                                    <i class="fas fa-list-ul fs-5"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark">Estructura de Contenidos</h4>
                                    <p class="text-muted small mb-0">Configura los módulos y el sistema de evaluación promediado.</p>
                                </div>
                            </div>
                            
                            <!-- Contador de Ponderación Dinámico -->
                            <div id="total-ponderacion-container" class="card border-2 border-dashed shadow-xs p-2 px-3 rounded-4 bg-light" style="min-width: 250px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-muted uppercase">Balance de Notas</span>
                                    <span class="badge bg-white text-dark fw-bold border"><span id="total-ponderacion-valor">0</span>%</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div id="ponderacion-progress" class="progress-bar rounded-pill" role="progressbar" style="width: 0%; transition: width 0.5s ease;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-25">

                    <div id="contenidos-container" class="row row-cols-1 g-5">
                        @php $tiposEvaluacionJson = json_encode($tiposEvaluacion); @endphp
                        @foreach($contenidos as $index => $contenido)
                            <div class="col contenido-item animate__animated animate__fadeIn" data-index="{{ $index }}">
                                <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit active-card mb-2">
                                    <div class="card-body p-4">
                                        <input type="hidden" name="contenidos[{{ $index }}][id]" value="{{ $contenido->id_contenido_curso }}">
                                        
                                        <!-- Cabecera del Item -->
                                        <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                                            <div class="col-md-auto">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                    {{ $index + 1 }}
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mb-0">
                                                    <input type="text" name="contenidos[{{ $index }}][titulo]"
                                                        class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" 
                                                        value="{{ $contenido->titulo }}" required
                                                        placeholder="Escribe el nombre del tema o actividad...">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                                                    <input type="hidden" name="contenidos[{{ $index }}][es_evaluacion]" value="0">
                                                    <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" 
                                                           id="evalSwitch_{{ $index }}" 
                                                           name="contenidos[{{ $index }}][es_evaluacion]" 
                                                           value="1" 
                                                           onchange="toggleEvaluacion(this)"
                                                           {{ $contenido->es_evaluacion ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_{{ $index }}">
                                                        ¿Es evaluable?
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 opacity-50 hover-opacity-100 remove-contenido" title="Eliminar este contenido">
                                                    <i class="fas fa-trash-alt fs-6"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Campos de Evaluación (Condicionales) -->
                                        <div class="row g-3 evaluacion-fields rounded-3 p-3 mb-3 shadow-sm"
                                            style="{{ !$contenido->es_evaluacion ? 'display:none' : 'display:flex' }}; background-color: #0d6efd !important;">
                                            <div class="col-md-6 text-white fw-bold small uppercase d-flex align-items-center mb-2">
                                                <i class="fas fa-star me-2"></i> Ajustes de Calificación
                                            </div>
                                            <div class="col-12 h-0"></div>
                                            <div class="col-md-7">
                                                <div class="form-group mb-0">
                                                    <label class="small text-white fw-bold mb-1 opacity-75">TIPO DE EVALUACIÓN</label>
                                                    <select name="contenidos[{{ $index }}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                                        <option value="">Seleccione el método...</option>
                                                        @foreach($tiposEvaluacion as $tipo)
                                                            <option value="{{ $tipo->id_tipo_evaluacion }}" {{ $contenido->id_tipo_evaluacion == $tipo->id_tipo_evaluacion ? 'selected' : '' }}>
                                                                {{ $tipo->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group mb-0">
                                                    <label class="small text-white fw-bold mb-1 opacity-75">PESO SOBRE LA NOTA (%)</label>
                                                    <div class="input-group shadow-sm">
                                                        <input type="number" name="contenidos[{{ $index }}][ponderacion]"
                                                            class="form-control border-0 text-center fw-bold text-primary" value="{{ $contenido->ponderacion }}"
                                                            min="0" max="100" placeholder="0">
                                                        <span class="input-group-text bg-white border-0 fw-bold text-primary">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel Material de Apoyo (Toggle) -->
                                        <div class="material-fields row g-3 rounded-3 p-3 mb-3 shadow-sm"
                                            style="{{ $contenido->url_contenido ? 'display:flex' : 'display:none' }}; background-color: #4f46e5 !important;">
                                            <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                                                <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                                            </div>
                                            <div class="col-12">
                                                <label class="small text-white fw-bold mb-1 opacity-75">ENLACE O RECURSO EXTERNO (URL)</label>
                                                <div class="input-group bg-white rounded-3 px-2 border shadow-none">
                                                    <span class="input-group-text bg-transparent border-0 opacity-40"><i class="fas fa-link small"></i></span>
                                                    <input type="url" name="contenidos[{{ $index }}][url_contenido]"
                                                        class="form-control bg-transparent border-0 py-2 small" value="{{ $contenido->url_contenido }}"
                                                        placeholder="https://ejemplo.com/material">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Detalles y Meta -->
                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <div class="form-group mb-0">
                                                    <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                                    <input type="date" name="contenidos[{{ $index }}][fecha_contenido]"
                                                        class="form-control border-light-2 shadow-xs py-2"
                                                        value="{{ $contenido->fecha_contenido ? $contenido->fecha_contenido->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-5 d-flex align-items-end">
                                                <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                                                    <input type="hidden" name="contenidos[{{ $index }}][_has_material]" value="0">
                                                    <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle"
                                                        id="matSwitch_{{ $index }}"
                                                        onchange="toggleMaterial(this)"
                                                        {{ $contenido->url_contenido ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_{{ $index }}">
                                                        <i class="fas fa-paperclip me-1"></i> ¿Tiene material?
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group mb-0 mt-3">
                                                    <label class="small text-muted fw-bold mb-1">RESUMEN DEL CONTENIDO</label>
                                                    <textarea name="contenidos[{{ $index }}][descripcion_breve]"
                                                        class="form-control border-light-2 shadow-xs" rows="2"
                                                        placeholder="Proporciona una breve descripción o instrucciones para este contenido..." style="font-size: 0.85rem;">{{ $contenido->descripcion_breve }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-5">
                        <button type="button" id="agregar-contenido"
                            class="btn btn-outline-dark border-2 px-5 py-3 rounded-pill fw-bold hvr-push shadow-sm">
                            <i class="fas fa-plus-circle me-2"></i> Añadir Bloque de Contenido
                        </button>
                    </div>
                    </div>
                </div>

                <!-- Footer de Acciones Fijo/Sustentado -->
                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <div class="ponderacion-alerts-container me-3 text-end">
                                        <div id="alert-100" class="text-success small fw-bold" style="display: none;">
                                            <i class="fas fa-check-double me-1"></i> Lista para guardar
                                        </div>
                                        <div id="alert-insufficient" class="text-warning small fw-bold" style="display: none;">
                                            <i class="fas fa-clock me-1"></i> Falta carga ({{ $totalPonderacion ?? '0' }}%)
                                        </div>
                                        <div id="alert-over" class="text-danger small fw-bold" style="display: none;">
                                            <i class="fas fa-times-circle me-1"></i> Exceso de carga
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-save-curso" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg" style="background: #1e3a8a;">
                                        <i class="fas fa-save me-2 text-white"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
                    @push('scripts')
                        <script>
                            const tiposEvaluacion = {!! $tiposEvaluacionJson !!};

                            function getTipoOptions() {
                                return tiposEvaluacion.map(t => `<option value="${t.id_tipo_evaluacion}">${t.nombre}</option>`).join('');
                            }
                            function toggleEvaluacion(inputElement) {
                                const container = inputElement.closest('.contenido-item');
                                const evalFields = container.querySelector('.evaluacion-fields');
                                
                                // Verificar si es checkbox (checked) o select (value)
                                const isActive = inputElement.type === 'checkbox' ? inputElement.checked : inputElement.value == '1';

                                if(isActive) {
                                    evalFields.style.display = 'flex';
                                    evalFields.style.opacity = 0;
                                    setTimeout(() => { evalFields.style.opacity = 1; }, 50);
                                } else {
                                    evalFields.style.display = 'none';
                                    evalFields.querySelectorAll('input, select').forEach(el => el.value = '');
                                }
                            }

                             document.addEventListener('DOMContentLoaded', function () {
                                let contadorContenidos = {{ count($contenidos) }};

                                // Función para recalcular ponderación total
                                function actualizarPonderacionTotal() {
                                    let total = 0;
                                    let hasEvaluacion = false;
                                    const items = document.querySelectorAll('.contenido-item');
                                    
                                    items.forEach(item => {
                                        const isEval = item.querySelector('.custom-control-input').checked;
                                        if (isEval) {
                                            hasEvaluacion = true;
                                            const ponderacion = parseFloat(item.querySelector('input[name*="[ponderacion]"]').value) || 0;
                                            total += ponderacion;
                                        }
                                    });

                                    const valorDisplay = document.getElementById('total-ponderacion-valor');
                                    const progressBar = document.getElementById('ponderacion-progress');
                                    const btnSave = document.getElementById('btn-save-curso');
                                    
                                    const alert100 = document.getElementById('alert-100');
                                    const alertInsuff = document.getElementById('alert-insufficient');
                                    const alertOver = document.getElementById('alert-over');

                                    valorDisplay.innerText = total;
                                    progressBar.style.width = Math.min(total, 100) + '%';
                                    
                                    // Reset alerts
                                    alert100.style.display = 'none';
                                    alertInsuff.style.display = 'none';
                                    alertOver.style.display = 'none';

                                    if (hasEvaluacion) {
                                        if (total > 100) {
                                            progressBar.className = 'progress-bar rounded-pill bg-danger';
                                            alertOver.style.display = 'block';
                                            if (btnSave) btnSave.disabled = true;
                                        } else if (total < 100) {
                                            progressBar.className = 'progress-bar rounded-pill bg-warning';
                                            alertInsuff.style.display = 'block';
                                            if (btnSave) btnSave.disabled = true;
                                        } else {
                                            progressBar.className = 'progress-bar rounded-pill bg-success';
                                            alert100.style.display = 'block';
                                            if (btnSave) btnSave.disabled = false;
                                        }
                                    } else {
                                        progressBar.className = 'progress-bar rounded-pill bg-secondary';
                                        if (btnSave) btnSave.disabled = false;
                                    }
                                }

                                // Escuchar cambios en ponderaciones y switches
                                document.addEventListener('input', function(e) {
                                    if (e.target.matches('input[name*="[ponderacion]"]') || e.target.matches('.custom-control-input')) {
                                        actualizarPonderacionTotal();
                                    }
                                });

                                // Delegación para toggleEvaluacion (ya existente pero integrada con el cálculo)
                            window.toggleEvaluacion = function(inputElement) {
                                    const container = inputElement.closest('.contenido-item');
                                    const evalFields = container.querySelector('.evaluacion-fields');
                                    const isActive = inputElement.checked;

                                    if(isActive) {
                                        evalFields.style.display = 'flex';
                                        evalFields.style.opacity = 0;
                                        setTimeout(() => { evalFields.style.opacity = 1; }, 50);
                                    } else {
                                        evalFields.style.display = 'none';
                                        evalFields.querySelectorAll('input, select').forEach(el => el.value = '');
                                    }
                                    actualizarPonderacionTotal();
                                }

                                window.toggleMaterial = function(inputElement) {
                                    const container = inputElement.closest('.contenido-item');
                                    const materialFields = container.querySelector('.material-fields');
                                    const urlInput = materialFields ? materialFields.querySelector('input[type="url"]') : null;
                                    const isActive = inputElement.checked;

                                    if (isActive) {
                                        materialFields.style.display = 'flex';
                                        materialFields.style.opacity = 0;
                                        setTimeout(() => { materialFields.style.opacity = 1; }, 50);
                                    } else {
                                        materialFields.style.display = 'none';
                                        if (urlInput) urlInput.value = '';
                                    }
                                }

                                // Inicializar cálculo
                                actualizarPonderacionTotal();

                                // Agregar nuevo contenido
                                document.getElementById('agregar-contenido').addEventListener('click', function () {
                                    const contenedor = document.getElementById('contenidos-container');
                                    const nuevoIndice = contadorContenidos++;
                                    const nuevoContenido = `
                                    <div class="col contenido-item animate__animated animate__zoomIn" data-index="${nuevoIndice}">
                                        <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                                            <div class="card-body p-4">
                                                <input type="hidden" name="contenidos[${nuevoIndice}][id]" value="">
                                                
                                                <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                                                    <div class="col-md-auto">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                            ${nuevoIndice + 1}
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="form-group mb-0">
                                                            <input type="text" name="contenidos[${nuevoIndice}][titulo]"
                                                                class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" 
                                                                required placeholder="Título del nuevo contenido...">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-auto">
                                                        <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                                                            <input type="hidden" name="contenidos[${nuevoIndice}][es_evaluacion]" value="0">
                                                            <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" 
                                                                   id="evalSwitch_${nuevoIndice}" 
                                                                   name="contenidos[${nuevoIndice}][es_evaluacion]" 
                                                                   value="1" 
                                                                   onchange="toggleEvaluacion(this)">
                                                            <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_${nuevoIndice}">
                                                                ¿Es evaluable?
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-auto">
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 remove-contenido" title="Eliminar">
                                                            <i class="fas fa-trash-alt fs-6"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="row g-3 evaluacion-fields rounded-3 p-3 mb-3 shadow-sm" style="display:none; background-color: #0d6efd !important;">
                                                    <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-2">
                                                        <i class="fas fa-star me-2"></i> Ajustes de Calificación
                                                    </div>
                                                    <div class="col-md-7">
                                                        <div class="form-group mb-0">
                                                            <label class="small text-white fw-bold mb-1 opacity-75">TIPO DE EVALUACIÓN</label>
                                                            <select name="contenidos[${nuevoIndice}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                                                <option value="">Seleccione...</option>
                                                                ${getTipoOptions()}
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group mb-0">
                                                            <label class="small text-white fw-bold mb-1 opacity-75">PESO (%)</label>
                                                            <div class="input-group shadow-sm">
                                                                <input type="number" name="contenidos[${nuevoIndice}][ponderacion]" class="form-control border-0 text-center fw-bold text-primary" min="0" max="100" placeholder="0">
                                                                <span class="input-group-text bg-white border-0 fw-bold text-primary">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="material-fields row g-3 rounded-3 p-3 mb-3 shadow-sm" style="display:none; background-color: #4f46e5 !important;">
                                                    <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                                                        <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="small text-white fw-bold mb-1 opacity-75">ENLACE O RECURSO EXTERNO (URL)</label>
                                                        <div class="input-group bg-white rounded-3 px-2 border shadow-none">
                                                            <span class="input-group-text bg-transparent border-0 opacity-40"><i class="fas fa-link small"></i></span>
                                                            <input type="url" name="contenidos[${nuevoIndice}][url_contenido]" class="form-control bg-transparent border-0 py-2 small" placeholder="https://...">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-7">
                                                        <div class="form-group mb-0">
                                                            <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                                            <input type="date" name="contenidos[${nuevoIndice}][fecha_contenido]" class="form-control border-light-2 shadow-xs py-2">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 d-flex align-items-end">
                                                        <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                                                            <input type="hidden" name="contenidos[${nuevoIndice}][_has_material]" value="0">
                                                            <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle"
                                                                id="matSwitch_${nuevoIndice}"
                                                                onchange="toggleMaterial(this)">
                                                            <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_${nuevoIndice}">
                                                                <i class="fas fa-paperclip me-1"></i> ¿Tiene material?
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group mb-0 mt-3">
                                                            <label class="small text-muted fw-bold mb-1">RESUMEN</label>
                                                            <textarea name="contenidos[${nuevoIndice}][descripcion_breve]" class="form-control border-light-2 shadow-xs" rows="2" placeholder="Breve descripción..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                    contenedor.insertAdjacentHTML('beforeend', nuevoContenido);
                                });

                                // Eliminar contenido
                                document.addEventListener('click', function (e) {
                                    if (e.target.closest('.remove-contenido')) {
                                        if (confirm('¿Estás seguro de eliminar este contenido?')) {
                                            const item = e.target.closest('.contenido-item');
                                            item.style.opacity = '0';
                                            setTimeout(() => {
                                                item.remove();
                                                actualizarPonderacionTotal();
                                            }, 300);
                                        }
                                    }
                                });
                            });
                        </script>
                    @endpush

    @push('styles')
        <style>
            .bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
            .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }
            .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
            .bg-gray-100 { background-color: #f8fafc; }
            .border-light-2 { border-color: #f1f4f8 !important; }
            .shadow-xs { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .shadow-card { box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
            
            .content-card-edit {
                border: 1px solid #e2e8f0;
                border-left: 5px solid #1e3a8a;
                transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
                box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            }
            .content-card-edit:hover {
                transform: translateY(-4px);
                box-shadow: 0 15px 35px rgba(30, 58, 138, 0.12) !important;
                border-color: rgba(30, 58, 138, 0.2);
            }
            
            .focus-none:focus { outline: none; border: none; box-shadow: none; }
            .hover-opacity-100:hover { opacity: 1 !important; }
            
            .hvr-push { transition: transform 0.2s; }
            .hvr-push:active { transform: scale(0.98); }
            
            .transition-hover { transition: all 0.2s ease; }
            .transition-hover:hover { background-color: #f1f5f9; transform: translateY(-1px); }

            .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }

            .progress-bar { transition: width 0.6s cubic-bezier(0.65, 0, 0.35, 1); }
        </style>
    @endpush