/**
 * TallerModule: Lógica central para la gestión de cursos en CINEFORM
 * Maneja la generación de códigos, validaciones de trimestres y gestión de contenidos dinámicos.
 */

const TallerModule = (() => {
    
    // Configuración interna
    let config = {
        tiposEvaluacion: [],
        containerId: 'contenidos-container',
        totalPonderacionValorId: 'total-ponderacion-valor',
        ponderacionProgressBarId: 'ponderacion-progress',
        saveBtnId: 'btn-save-curso'
    };

    /**
     * Inicializa el módulo con la configuración necesaria
     */
    const init = (options = {}) => {
        config = { ...config, ...options };
        setupEventListeners();
        actualizarPonderacionTotal();
    };

    /**
     * Configura los listeners globales
     */
    const setupEventListeners = () => {
        // Delegación de eventos para eliminar contenidos
        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-contenido')) {
                const item = e.target.closest('.contenido-item');
                if (item) {
                    item.classList.add('animate__zoomOut');
                    setTimeout(() => {
                        item.remove();
                        actualizarPonderacionTotal();
                        renumerarContenidos();
                    }, 300);
                }
            }
        });

        // Evento para añadir contenido
        const addBtn = document.getElementById('agregar-contenido');
        if (addBtn) {
            addBtn.addEventListener('click', agregarBloqueContenido);
        }

        // Listener para cambios en ponderación
        document.addEventListener('input', (e) => {
            if (e.target.matches('input[name*="[ponderacion]"]')) {
                actualizarPonderacionTotal();
            }
        });
    };

    /**
     * Genera el código PROCINEC dinámicamente
     * Formato original: LAB-{actividad}{trimestre}{correlativo}{aspecto}{modalidad}{modalidadEspecial}-{anio}
     */
    const generateProcinecCode = () => {
        const getAbbr = (id) => {
            const el = document.getElementById(id);
            if(!el) return '';
            return el.options[el.selectedIndex]?.getAttribute('data-abreviatura') || '';
        };

        const actividad = getAbbr('id_actividad_formativa');
        const aspecto = getAbbr('id_aspecto');
        const modalidadEsp = getAbbr('id_modalidad_especial');
        const modalidad = getAbbr('id_modalidad');
        
        const trimestre = document.getElementById('trimestre')?.value || '';
        const anio = document.getElementById('anio')?.value || new Date().getFullYear();
        const correlativo = document.getElementById('correlativo')?.value || '0';

        // Construcción del código según nomenclatura PROCINEC: LAB-ABC11XYZ-2026
        const middle = actividad + trimestre + correlativo + aspecto + modalidad + modalidadEsp;
        const code = `LAB-${middle}-${anio}`;

        const inputCodigo = document.getElementById('codigo');
        if (inputCodigo) {
            inputCodigo.value = code.toUpperCase();
        }
    };

    /**
     * Valida la coherencia de las fechas con el trimestre
     */
    const validateTrimestre = () => {
        const fInicio = document.getElementById('fecha_inicio')?.value;
        const fFin = document.getElementById('fecha_fin')?.value;
        const trimestre = document.getElementById('trimestre')?.value;
        const errorDiv = document.getElementById('trimestre-error');

        if (!fInicio || !fFin || !trimestre) return;

        const dateIni = new Date(fInicio);
        const dateFin = new Date(fFin);
        
        // Mapeo simple de trimestres: 1(Ene-Mar), 2(Abr-Jun), 3(Jul-Sep), 4(Oct-Dic)
        const monthStart = dateIni.getMonth() + 1;
        const expectedTrim = Math.ceil(monthStart / 3);

        if (parseInt(trimestre) !== expectedTrim) {
            if (errorDiv) errorDiv.style.display = 'block';
        } else {
            if (errorDiv) errorDiv.style.display = 'none';
        }
    };

    /**
     * Gestiona el toggle de campos de evaluación
     */
    const toggleEvaluacion = (el) => {
        const container = el.closest('.contenido-item');
        const evalFields = container.querySelector('.evaluacion-fields');
        if (el.checked) {
            evalFields.style.display = 'flex';
            evalFields.style.opacity = 0;
            setTimeout(() => evalFields.style.opacity = 1, 50);
        } else {
            evalFields.style.display = 'none';
            evalFields.querySelectorAll('input, select').forEach(input => input.value = '');
        }
        actualizarPonderacionTotal();
    };

    /**
     * Gestiona el toggle de material de apoyo
     */
    const toggleMaterial = (el) => {
        const container = el.closest('.contenido-item');
        const materialFields = container.querySelector('.material-fields');
        if (el.checked) {
            materialFields.style.display = 'flex';
        } else {
            materialFields.style.display = 'none';
            materialFields.querySelector('input').value = '';
        }
    };

    /**
     * Recalcula la ponderación total y actualiza la UI
     */
    const actualizarPonderacionTotal = () => {
        let total = 0;
        let hasEvaluacion = false;
        const items = document.querySelectorAll('.contenido-item');
        
        items.forEach(item => {
            const switchEval = item.querySelector('input[name*="[es_evaluacion]"][type="checkbox"]');
            if (switchEval && switchEval.checked) {
                hasEvaluacion = true;
                const inputPonderacion = item.querySelector('input[name*="[ponderacion]"]');
                total += parseFloat(inputPonderacion.value) || 0;
            }
        });

        updateUIBalance(total, hasEvaluacion);
    };

    const updateUIBalance = (total, hasEvaluacion) => {
        const valorDisplay = document.getElementById(config.totalPonderacionValorId);
        const progressBar = document.getElementById(config.ponderacionProgressBarId);
        const btnSave = document.getElementById(config.saveBtnId);
        
        const alert100 = document.getElementById('alert-100');
        const alertInsuff = document.getElementById('alert-insufficient');
        const alertOver = document.getElementById('alert-over');

        if (valorDisplay) valorDisplay.innerText = total;
        if (progressBar) {
            progressBar.style.width = Math.min(total, 100) + '%';
            
            // Lógica de colores y alertas
            if (hasEvaluacion) {
                if (total > 100) {
                    progressBar.className = 'progress-bar rounded-pill bg-danger';
                    if (alertOver) alertOver.style.display = 'block';
                    if (alert100) alert100.style.display = 'none';
                    if (alertInsuff) alertInsuff.style.display = 'none';
                    if (btnSave) btnSave.disabled = true;
                } else if (total < 100) {
                    progressBar.className = 'progress-bar rounded-pill bg-warning';
                    if (alertInsuff) alertInsuff.style.display = 'block';
                    if (alert100) alert100.style.display = 'none';
                    if (alertOver) alertOver.style.display = 'none';
                    if (btnSave) btnSave.disabled = true;
                } else {
                    progressBar.className = 'progress-bar rounded-pill bg-success';
                    if (alert100) alert100.style.display = 'block';
                    if (alertInsuff) alertInsuff.style.display = 'none';
                    if (alertOver) alertOver.style.display = 'none';
                    if (btnSave) btnSave.disabled = false;
                }
            } else {
                progressBar.className = 'progress-bar rounded-pill bg-secondary';
                if (btnSave) btnSave.disabled = false;
            }
        }
    };

    /**
     * Añade un nuevo bloque de contenido al contenedor
     */
    const agregarBloqueContenido = () => {
        const container = document.getElementById(config.containerId);
        const index = container.querySelectorAll('.contenido-item').length;
        
        const optionsEvaluacion = config.tiposEvaluacion.map(t => 
            `<option value="${t.id_tipo_evaluacion}">${t.nombre}</option>`
        ).join('');

        const html = `
            <div class="col contenido-item animate__animated animate__zoomIn" data-index="${index}">
                <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                            <div class="col-md-auto">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                    ${index + 1}
                                </div>
                            </div>
                            <div class="col">
                                <input type="text" name="contenidos[${index}][titulo]" class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" required placeholder="Título del bloque...">
                            </div>
                            <div class="col-md-auto">
                                <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                                    <input type="hidden" name="contenidos[${index}][es_evaluacion]" value="0">
                                    <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" id="evalSwitch_${index}" name="contenidos[${index}][es_evaluacion]" value="1" onchange="TallerModule.toggleEvaluacion(this)">
                                    <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_${index}">Evaluable</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>

                        <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3" style="display:none; background-color: #0d6efd !important;">
                            <div class="col-md-7">
                                <label class="small text-white fw-bold mb-1">METODOLOGÍA</label>
                                <select name="contenidos[${index}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    ${optionsEvaluacion}
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="small text-white fw-bold mb-1">PESO (%)</label>
                                <input type="number" name="contenidos[${index}][ponderacion]" class="form-control border-0 text-center fw-bold text-primary" placeholder="0">
                            </div>
                        </div>

                        <div class="material-fields row g-3 rounded-3 p-3 mb-3" style="display:none; background-color: #4f46e5 !important;">
                            <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                                <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                            </div>
                            <div class="col-12">
                                <label class="small text-white fw-bold mb-1 opacity-75">ENLACE DEL RECURSO (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-0 opacity-50"><i class="fas fa-link small"></i></span>
                                    <input type="url" name="contenidos[${index}][url_contenido]" class="form-control border-0 py-2" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                <input type="date" name="contenidos[${index}][fecha_contenido]" class="form-control border-light-2 py-2">
                            </div>
                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                                    <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle" id="matSwitch_${index}" onchange="TallerModule.toggleMaterial(this)">
                                    <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_${index}">
                                        <i class="fas fa-paperclip me-1"></i> ¿Tiene material?
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <textarea name="contenidos[${index}][descripcion_breve]" class="form-control border-light-2" rows="2" placeholder="Breve explicación..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    };

    const renumerarContenidos = () => {
        const items = document.querySelectorAll('.contenido-item');
        items.forEach((item, i) => {
            const badge = item.querySelector('.bg-dark, .bg-primary');
            if (badge) badge.innerText = i + 1;
        });
    };

    /**
     * Gestiona la visibilidad del selector de localidades según el alcance nacional
     */
    const toggleLocalidades = (checkbox) => {
        const container = document.getElementById('container-localidades');
        
        if (checkbox.checked) {
            if (container) {
                $(container).fadeOut(300);
            }
            // Opcional: Deseleccionar todos al marcar nacional para limpiar el request
            selectAllLocalidades(false);
        } else {
            if (container) {
                $(container).fadeIn(300);
            }
        }
    };

    /**
     * Selecciona o deselecciona todas las localidades visibles
     */
    const selectAllLocalidades = (bool) => {
        const grid = document.getElementById('localidades-grid');
        if (!grid) return;

        const checkboxes = grid.querySelectorAll('.localidad-checkbox');
        checkboxes.forEach(cb => {
            // Solo afectar a los que están visibles actualmente por el filtro
            if (cb.closest('.localidad-item').style.display !== 'none') {
                cb.checked = bool;
            }
        });
    };

    /**
     * Filtra la lista de localidades por nombre
     */
    const filterLocalidades = () => {
        const query = document.getElementById('search-localidad')?.value.toLowerCase() || '';
        const items = document.querySelectorAll('.localidad-item');

        items.forEach(item => {
            const nombre = item.getAttribute('data-nombre') || '';
            if (nombre.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    };

    // Public API
    return {
        init,
        generateProcinecCode,
        validateTrimestre,
        toggleEvaluacion,
        toggleMaterial,
        actualizarPonderacionTotal,
        toggleLocalidades,
        selectAllLocalidades,
        filterLocalidades
    };

})();
