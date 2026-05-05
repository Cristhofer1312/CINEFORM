    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show">
                    <div class="icon-shape icon-sm bg-success-light text-success rounded-circle me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div><h6 class="mb-0 fw-bold">¡Actualizado!</h6><small>{{ session('success') }}</small></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Encabezado Administrativo -->
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Configuración Maestra</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Edición de Curso (Coordinador)</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-shield-alt me-1 text-primary"></i> Posees permisos totales para modificar los parámetros base de este programa.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Volver
                    </a>
                </div>
            </div>

            <form action="{{ route('taller.cursos.update', $curso->id_curso) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Panel de Control del Curso -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                            <i class="fas fa-cog fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Parámetros Maestros</h5>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-10">
                        <div class="row g-4">
                            <!-- Sección: Identidad del Programa -->
                            <div class="col-md-7">
                                <label for="nombre" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-heading me-2 opacity-50"></i> NOMBRE OFICIAL DEL CURSO
                                </label>
                                <input type="text" class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2" id="nombre" name="nombre"
                                    value="{{ old('nombre', $curso->nombre) }}" placeholder="Ej: Especialización en Cine Digital">
                            </div>

                            <!-- Facilitador Asignado -->
                            <div class="col-md-5">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-tie me-2 opacity-50 text-primary"></i> FACILITADOR ASIGNADO
                                </label>
                                <div class="bg-white border-2 rounded-3 p-2 px-3 shadow-xs border-light-2 d-flex align-items-center" style="height: 58px;">
                                    <div class="avatar-sm me-3">
                                        <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-chalkboard-teacher small"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" id="selected-facilitator-name">
                                            @php
                                                $facilitadorActual = $facilitadores->first(fn($f) => $f->personalData->id_persona == $curso->id_persona);
                                            @endphp
                                            {{ $facilitadorActual ? ($facilitadorActual->personalData->primer_nombre . ' ' . $facilitadorActual->personalData->primer_apellido) : 'No asignado' }}
                                        </h6>
                                        <small class="text-muted d-block" id="selected-facilitator-doc">
                                            {{ $facilitadorActual ? 'C.I: ' . $facilitadorActual->personalData->dni : 'Seleccione un docente' }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0 ms-2" data-bs-toggle="modal" data-bs-target="#modalFacilitadores">
                                        <i class="fas fa-exchange-alt small"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="id_persona" id="id_persona" value="{{ old('id_persona', $curso->id_persona) }}" required>
                            </div>

                            <!-- Sección: Configuración Académica -->
                            <div class="col-md-4">
                                <label for="id_modalidad" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-laptop-house me-2 opacity-50"></i> MODALIDAD
                                </label>
                                <select name="id_modalidad" id="id_modalidad" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2">
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id_modalidad }}" {{ $curso->id_modalidad == $mod->id_modalidad ? 'selected' : '' }}>
                                            {{ $mod->nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-users-cog me-2 opacity-50"></i> CUPO MÁXIMO
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="fas fa-user-plus small"></i></span>
                                    <input type="number" class="form-control border-2 border-start-0 py-2 fw-bold border-light-2" name="cantidad_cupos" value="{{ old('cantidad_cupos', $curso->cantidad_cupos) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-calendar-week me-2 opacity-50"></i> DURACIÓN
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3" name="duracion" value="{{ old('duracion', $curso->duracion) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-clock me-2 opacity-50"></i> HORAS
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3" name="horas" value="{{ old('horas', $curso->horas) }}">
                                </div>
                            </div>

                            <!-- Sección: Temporalidad -->
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-play-circle me-2 opacity-50 text-success"></i> Apertura de curso
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-plus"></i></span>
                                    <input type="date" class="form-control border-2 border-start-0 py-2 border-light-2" name="fecha_inicio" 
                                        value="{{ old('fecha_inicio', $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-stop-circle me-2 opacity-50 text-danger"></i> CIERRE DE CURSO
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-minus"></i></span>
                                    <input type="date" class="form-control border-2 border-start-0 py-2 border-light-2" name="fecha_fin" 
                                        value="{{ old('fecha_fin', $curso->fecha_fin ? $curso->fecha_fin->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <!-- Área Descriptiva -->
                            <div class="col-12 mt-2">
                                <label for="descripcion" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 opacity-50"></i> SÍNTESIS CURRICULAR DEL PROGRAMA
                                </label>
                                <textarea class="form-control border-2 shadow-none rounded-4 bg-white p-3 border-light-2" id="descripcion" name="descripcion"
                                    rows="4" placeholder="Breve descripción del programa académico para los estudiantes...">{{ old('descripcion', $curso->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sección: Currículo Académico -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
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
                            
                            <!-- Barra de Ponderación Dinámica -->
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
                                <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                                    <div class="card-body p-4">
                                        <input type="hidden" name="contenidos[{{ $index }}][id]" value="{{ $contenido->id_contenido_curso }}">
                                        
                                        <!-- Cabecera del Contenido -->
                                        <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                                            <div class="col-md-auto">
                                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                    {{ $index + 1 }}
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mb-0">
                                                    <input type="text" name="contenidos[{{ $index }}][titulo]"
                                                        class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" 
                                                        value="{{ $contenido->titulo }}" required
                                                        placeholder="Título del módulo o actividad">
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
                                                    <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_{{ $index }}">Evaluable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 remove-contenido opacity-50 hover-opacity-100">
                                                    <i class="fas fa-trash-alt fs-6"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Campos de Evaluación (Modo Coordinador) -->
                                        <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3 shadow-sm"
                                            style="{{ !$contenido->es_evaluacion ? 'display:none' : 'display:flex' }}; background-color: #0d6efd !important;">
                                            <div class="col-md-7">
                                                <div class="form-group mb-0">
                                                    <label class="small text-white fw-bold mb-1">METODOLOGÍA DE EVALUACIÓN</label>
                                                    <select name="contenidos[{{ $index }}][id_tipo_evaluacion]" class="form-select border-light-2 shadow-xs">
                                                        <option value="">Seleccione el tipo...</option>
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
                                                    <label class="small text-white fw-bold mb-1">PESO CURRICULAR (%)</label>
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
                                                <label class="small text-white fw-bold mb-1 opacity-75">RECURSO O ENLACE (URL)</label>
                                                <div class="input-group bg-white rounded-3 px-2 border shadow-none">
                                                    <span class="input-group-text bg-transparent border-0 opacity-40"><i class="fas fa-link small"></i></span>
                                                    <input type="url" name="contenidos[{{ $index }}][url_contenido]"
                                                        class="form-control bg-transparent border-0 py-2 small" value="{{ $contenido->url_contenido }}"
                                                        placeholder="https://cnac.gob.ve/recurso">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Meta y URL -->
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
                                                    <label class="small text-muted fw-bold mb-1">DESCRIPCIÓN BREVE</label>
                                                    <textarea name="contenidos[{{ $index }}][descripcion_breve]"
                                                        class="form-control border-light-2 shadow-xs" rows="2"
                                                        placeholder="Instrucciones o detalles del contenido...">{{ $contenido->descripcion_breve }}</textarea>
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

                <!-- Footer de Acciones -->
                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    Cancelar
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <div class="me-3 text-end">
                                        <div id="alert-100" class="text-success small fw-bold" style="display: none;">
                                            <i class="fas fa-check-circle me-1"></i> Balance correcto
                                        </div>
                                        <div id="alert-insufficient" class="text-warning small fw-bold" style="display: none;">
                                            <i class="fas fa-exclamation-circle me-1"></i> Incompleto
                                        </div>
                                        <div id="alert-over" class="text-danger small fw-bold" style="display: none;">
                                            <i class="fas fa-times-circle me-1"></i> Sobrecarga
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-save-curso" class="btn btn-dark px-5 py-3 rounded-pill fw-bold shadow-lg">
                                        <i class="fas fa-save me-2"></i> Guardar Cambios Maestros
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
                                                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
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
                                                            <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_${nuevoIndice}">Evaluable</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-auto">
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 remove-contenido" title="Eliminar">
                                                            <i class="fas fa-trash-alt fs-6"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3 shadow-sm" style="display:none; background-color: #0d6efd !important;">
                                                    <div class="col-md-7">
                                                        <div class="form-group mb-0">
                                                            <label class="small text-white fw-bold mb-1">TIPO DE EVALUACIÓN</label>
                                                            <select name="contenidos[${nuevoIndice}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                                                <option value="">Seleccione el tipo...</option>
                                                                ${getTipoOptions()}
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group mb-0">
                                                            <label class="small text-white fw-bold mb-1">PESO (%)</label>
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
                                                        <label class="small text-white fw-bold mb-1 opacity-75">RECURSO O ENLACE (URL)</label>
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
                                                            <label class="small text-muted fw-bold mb-1">DESCRIPCIÓN</label>
                                                            <textarea name="contenidos[${nuevoIndice}][descripcion_breve]" class="form-control border-light-2 shadow-xs" rows="2" placeholder="Breve resumen del contenido..."></textarea>
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
                                        const item = e.target.closest('.contenido-item');
                                        Swal.fire({
                                            title: '¿Eliminar contenido?',
                                            text: 'Esta acción no se puede deshacer hasta que guardes los cambios.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#dc3545',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                item.style.opacity = '0';
                                                item.style.transition = 'opacity 0.3s';
                                                setTimeout(() => {
                                                    item.remove();
                                                    actualizarPonderacionTotal();
                                                }, 300);
                                            }
                                        });
                                    }
                                });
                                // Confirmación SweetAlert al guardar
                                const form = document.querySelector('form[action*="cursos"]');
                                if (form) {
                                    form.addEventListener('submit', function (e) {
                                        e.preventDefault();
                                        Swal.fire({
                                            title: '¿Guardar cambios?',
                                            text: 'Se actualizarán los datos del curso y sus contenidos.',
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonColor: '#1572e8',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: '<i class="fas fa-save me-1"></i> Sí, guardar',
                                            cancelButtonText: 'Cancelar',
                                            reverseButtons: true
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                Swal.fire({
                                                    title: 'Guardando...',
                                                    text: 'Por favor espera.',
                                                    icon: 'info',
                                                    allowOutsideClick: false,
                                                    showConfirmButton: false,
                                                    didOpen: () => Swal.showLoading()
                                                });
                                                form.submit();
                                            }
                                        });
                                    });
                                }
                             });
                        </script>
                    @endpush

    @push('styles')
        <style>
            .bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
            .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
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

    <!-- Modal para Selección de Facilitadores -->
    <div class="modal fade" id="modalFacilitadores" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-user-graduate me-2"></i> Seleccionar Facilitador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    <!-- Buscador -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted border-light-2"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-start-0 py-2 border-light-2" id="filtro_nombre_cedula_modal" onkeyup="filterFacilitators()" placeholder="Buscar por nombre o cédula...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <select id="filtro_especializacion_modal" class="form-select shadow-sm py-2 border-light-2" onchange="filterFacilitators()">
                                <option value="">Todas las especializaciones</option>
                                @foreach($especializaciones as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Lista -->
                    <div id="facilitator-container" class="bg-white rounded-4 border border-light-2 overflow-auto shadow-sm" style="max-height: 400px;">
                        @foreach($facilitadores as $facilitador)
                            @php $p = $facilitador->personalData @endphp
                            <div class="facilitator-item p-3 border-bottom cursor-pointer transition-all hover-bg-light {{ old('id_persona', $curso->id_persona) == $p->id_persona ? 'bg-primary-soft border-primary border-start border-4' : '' }}"
                                onclick="selectFacilitator(this, '{{ $p->id_persona }}', '{{ $p->primer_nombre }} {{ $p->primer_apellido }}', '{{ $p->dni }}')"
                                data-name="{{ strtolower($p->primer_nombre . ' ' . $p->primer_apellido) }}"
                                data-doc="{{ $p->dni }}"
                                data-specializations="{{ $p->especializaciones->pluck('id')->join(',') }}">
                                
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        {!! renderAvatar($p, 'avatar-sm') !!}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $p->primer_nombre }} {{ $p->primer_apellido }}</h6>
                                                <div class="small text-muted mt-1 d-flex align-items-center">
                                                    <i class="fas fa-id-card me-2 opacity-50"></i> C.I: {{ $p->dni }}
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-primary border border-primary-soft small fw-normal rounded-pill px-3">
                                                Docente
                                            </span>
                                        </div>
                                        <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                                            @if($p->especializaciones->count() > 0)
                                                <i class="fas fa-graduation-cap me-2 opacity-50"></i>
                                                <span class="text-primary-emphasis fw-medium">{{ $p->especializaciones->pluck('nombre')->join(', ') }}</span>
                                            @else
                                                <span class="fst-italic opacity-50">Sin especializaciones registradas</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-3 check-icon {{ old('id_persona', $curso->id_persona) == $p->id_persona ? '' : 'd-none' }}">
                                        <i class="fas fa-check-circle text-primary fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div id="no-results-facilitators" class="p-5 text-center text-muted d-none">
                            <div class="mb-3"><i class="fas fa-running fs-1 opacity-20"></i></div>
                            <p class="mb-0">No se encontraron docentes que coincidan.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold w-100 py-3" data-bs-dismiss="modal">
                        Confirmar Facilitador
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function selectFacilitator(element, id, name, doc) {
                // Remover clase activa de todos
                document.querySelectorAll('.facilitator-item').forEach(item => {
                    item.classList.remove('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
                    item.querySelector('.check-icon').classList.add('d-none');
                });

                // Agregar clase activa al seleccionado
                element.classList.add('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
                element.querySelector('.check-icon').classList.remove('d-none');

                // Actualizar UI principal
                document.getElementById('id_persona').value = id;
                document.getElementById('selected-facilitator-name').innerText = name;
                const docElement = document.getElementById('selected-facilitator-doc');
                if(docElement) docElement.innerText = 'C.I: ' + doc;
                
                // Cerrar modal tras selección (opcional)
                const modalEl = document.getElementById('modalFacilitadores');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) setTimeout(() => modal.hide(), 250);
            }

            function filterFacilitators() {
                const searchText = document.getElementById('filtro_nombre_cedula_modal')?.value.toLowerCase() || '';
                const specId = document.getElementById('filtro_especializacion_modal')?.value || '';
                const items = document.querySelectorAll('.facilitator-item');
                let hasVisible = false;

                items.forEach(item => {
                    const name = item.getAttribute('data-name');
                    const doc = item.getAttribute('data-doc');
                    const specs = item.getAttribute('data-specializations').split(',');

                    const matchesSearch = name.includes(searchText) || doc.includes(searchText);
                    const matchesSpec = specId === "" || specs.includes(specId);

                    if (matchesSearch && matchesSpec) {
                        item.classList.remove('d-none');
                        hasVisible = true;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                const noResults = document.getElementById('no-results-facilitators');
                if (hasVisible) {
                    noResults?.classList.add('d-none');
                } else {
                    noResults?.classList.remove('d-none');
                }
            }
        </script>
    @endpush