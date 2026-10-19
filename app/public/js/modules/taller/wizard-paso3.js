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

    const aplicarRango = (inputFecha) => {
        const cont = document.getElementById(containerId);
        if (!cont) return;
        if (cont.dataset.rangoInicio) inputFecha.min = cont.dataset.rangoInicio;
        if (cont.dataset.rangoFin) inputFecha.max = cont.dataset.rangoFin;
    };

    const plantilla = (idx) => `
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
                                <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                <input type="date" name="contenidos[${idx}][fecha_contenido]" class="form-control border-light-2 py-2">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold mb-1">HORA DE INICIO</label>
                                <input type="time" name="contenidos[${idx}][hora_inicio]" class="form-control border-light-2 py-2" step="60" min="00:00" max="23:59">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold mb-1">HORA DE FIN</label>
                                <input type="time" name="contenidos[${idx}][hora_fin]" class="form-control border-light-2 py-2" step="60" min="00:00" max="23:59">
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

    document.addEventListener('DOMContentLoaded', () => {
        const cont = document.getElementById(containerId);
        if (!cont) return;
        cont.querySelectorAll('input[type="date"]').forEach(aplicarRango);

        document.getElementById('agregar-contenido')?.addEventListener('click', () => {
            const idx = cont.querySelectorAll('.contenido-item').length;
            cont.insertAdjacentHTML('beforeend', plantilla(idx));
            const nuevo = cont.querySelectorAll('.contenido-item')[idx];
            const fecha = nuevo?.querySelector('input[type="date"]');
            if (fecha) aplicarRango(fecha);
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-contenido');
            if (!btn || !cont.contains(btn)) return;
            btn.closest('.contenido-item')?.remove();
            renumerar();
        });
    });
})();
