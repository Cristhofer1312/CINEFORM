/**
 * WizardPaso3: temario simple (sin evaluaciones).
 *
 * Bloques con título, fecha (acotada al rango del curso), horario y
 * enlace/descripción. Elimina, renumera y reindexa names[] al borrar.
 */
(() => {
    const containerId = 'contenidos-container';

    const renumerar = () => {
        document.querySelectorAll(`#${containerId} .contenido-item`).forEach((item, idx) => {
            item.dataset.index = idx;
            const badge = item.querySelector('.rounded-circle.fw-bold');
            if (badge) badge.textContent = idx + 1;
            item.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/contenidos\[\d+\]/, `contenidos[${idx}]`);
            });
        });
    };

    const formatearFecha = (iso) => {
        if (!iso) return '';
        const partes = String(iso).split('-');
        if (partes.length !== 3) return iso;
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    };

    const aplicarRango = (inputFecha) => {
        const cont = document.getElementById(containerId);
        if (!cont || !inputFecha) return;
        if (cont.dataset.rangoInicio) inputFecha.min = cont.dataset.rangoInicio;
        if (cont.dataset.rangoFin) inputFecha.max = cont.dataset.rangoFin;
    };

    const validarFechaInput = (inputFecha) => {
        const cont = document.getElementById(containerId);
        if (!cont || !inputFecha || !inputFecha.value) {
            inputFecha?.setCustomValidity('');
            return;
        }
        const rIni = cont.dataset.rangoInicio;
        const rFin = cont.dataset.rangoFin;
        const val = inputFecha.value;

        if (rIni && val < rIni) {
            inputFecha.setCustomValidity(`La fecha no puede ser anterior al inicio del curso (${formatearFecha(rIni)}).`);
        } else if (rFin && val > rFin) {
            inputFecha.setCustomValidity(`La fecha no puede ser posterior a la finalización del curso (${formatearFecha(rFin)}).`);
        } else {
            inputFecha.setCustomValidity('');
        }
    };

    const plantilla = (idx) => {
        const cont = document.getElementById(containerId);
        const rIni = cont?.dataset.rangoInicio || '';
        const rFin = cont?.dataset.rangoFin || '';
        const minAttr = rIni ? `min="${rIni}"` : '';
        const maxAttr = rFin ? `max="${rFin}"` : '';

        return `
        <div class="col contenido-item" data-index="${idx}">
            <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                        <div class="col-md-auto">
                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">${idx + 1}</div>
                        </div>
                        <div class="col">
                            <input type="text" name="contenidos[${idx}][titulo]" class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" required placeholder="Título del bloque...">
                        </div>
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <div class="rounded-3 p-3 mb-3" style="background-color: #f1f5f9 !important;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold mb-1">FECHA DE CLASE <span class="text-danger">*</span></label>
                                <input type="date" name="contenidos[${idx}][fecha_contenido]" class="form-control border-light-2 py-2" ${minAttr} ${maxAttr} required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold mb-1">HORA DE INICIO <span class="text-danger">*</span></label>
                                <input type="time" name="contenidos[${idx}][hora_inicio]" class="form-control border-light-2 py-2" step="60" min="00:00" max="23:59" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold mb-1">HORA DE FIN <span class="text-danger">*</span></label>
                                <input type="time" name="contenidos[${idx}][hora_fin]" class="form-control border-light-2 py-2" step="60" min="00:00" max="23:59" required>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="small text-muted fw-bold mb-1">ENLACE DEL RECURSO (OPCIONAL)</label>
                            <input type="url" name="contenidos[${idx}][url_contenido]" class="form-control border-light-2 py-2" placeholder="https://...">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <textarea name="contenidos[${idx}][descripcion_breve]" class="form-control border-light-2" rows="2" placeholder="Breve explicación del bloque..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    };

    const initTemario = () => {
        const cont = document.getElementById(containerId);
        if (!cont) return;

        const configurarFechaInput = (inputFecha) => {
            aplicarRango(inputFecha);
            validarFechaInput(inputFecha);
            inputFecha.addEventListener('input', () => validarFechaInput(inputFecha));
            inputFecha.addEventListener('change', () => validarFechaInput(inputFecha));
        };

        cont.querySelectorAll('input[type="date"]').forEach(configurarFechaInput);

        document.getElementById('agregar-contenido')?.addEventListener('click', () => {
            const idx = cont.querySelectorAll('.contenido-item').length;
            cont.insertAdjacentHTML('beforeend', plantilla(idx));
            const nuevo = cont.querySelectorAll('.contenido-item')[idx];
            const fecha = nuevo?.querySelector('input[type="date"]');
            if (fecha) configurarFechaInput(fecha);
        });

        // Validación en submit
        const formPaso3 = document.getElementById('form-paso3') || cont.closest('form');
        formPaso3?.addEventListener('submit', (e) => {
            const submitter = e.submitter;
            const esSalir = submitter && (submitter.value === 'salir' || submitter.name === 'guardar_y_salir');
            if (esSalir) return;

            const inputsFecha = cont.querySelectorAll('input[name*="[fecha_contenido]"]');
            for (const inputFecha of inputsFecha) {
                validarFechaInput(inputFecha);
                if (!inputFecha.checkValidity()) {
                    e.preventDefault();
                    inputFecha.reportValidity();
                    return false;
                }
            }
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-contenido');
            if (!btn || !cont.contains(btn)) return;
            const item = btn.closest('.contenido-item');
            if (!item) return;

            const titulo = item.querySelector('input[name*="[titulo]"]')?.value?.trim() || 'este bloque';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar bloque?',
                    text: `Se eliminará "${titulo}".`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        item.remove();
                        renumerar();
                    }
                });
            } else {
                if (confirm(`¿Eliminar ${titulo}?`)) {
                    item.remove();
                    renumerar();
                }
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemario);
    } else {
        initTemario();
    }
})();
