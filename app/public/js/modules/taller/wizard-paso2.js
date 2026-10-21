/**
 * WizardPaso2: trimestre derivado de fecha_inicio + toggle de localidades.
 *
 * El trimestre NO es input: se calcula ceil(mes/3) y se muestra readonly.
 * El servidor deriva el mismo valor en StorePaso2Request::validated().
 */
const WizardPaso2 = (() => {
    const trimestreDe = (iso) => {
        if (!iso) return null;
        const mes = parseInt(String(iso).split('-')[1], 10);
        if (!mes || mes < 1 || mes > 12) return null;
        return Math.ceil(mes / 3);
    };

    const pintarTrimestre = () => {
        const inicio = document.querySelector('input[name="fecha_inicio"]')?.value;
        const destino = document.getElementById('trimestre-auto');
        if (!destino) return;
        const tri = trimestreDe(inicio);
        destino.textContent = tri ? tri + '°' : '—';
    };

    const toggleLocalidades = (el, animate = false) => {
        const input = el || document.getElementById('es_nacional');
        const cont = document.getElementById('container-localidades');
        if (!cont) return;
        const isNac = Boolean(input && input.checked);
        if (animate && window.jQuery) {
            if (isNac) {
                jQuery(cont).stop(true, true).slideUp(200);
            } else {
                jQuery(cont).stop(true, true).slideDown(200);
            }
        } else {
            cont.style.display = isNac ? 'none' : '';
        }
        if (isNac) {
            document.querySelectorAll('#container-localidades .localidad-checkbox').forEach((cb) => {
                cb.checked = false;
            });
        }
    };

    const initLogistica = () => {
        const inputFecha = document.querySelector('input[name="fecha_inicio"]');
        inputFecha?.addEventListener('change', pintarTrimestre);
        pintarTrimestre();

        const inputNacional = document.getElementById('es_nacional');
        inputNacional?.addEventListener('change', (e) => toggleLocalidades(e.target, true));
        inputNacional?.addEventListener('click', (e) => toggleLocalidades(e.target, true));
        toggleLocalidades(inputNacional, false);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLogistica);
    } else {
        initLogistica();
    }

    // ── Buscador AJAX de facilitadores (modal paso 2) ──
    // La primera página viene renderizada en servidor; el fetch solo
    // filtra (búsqueda) y pagina ("cargar más").
    let faciState = { url: null, next: null, loading: false, seleccion: null };

    const escapar = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

    const filaHtml = (f) => {
        const esp = (f.especialidades || []).slice(0, 3)
            .map((e) => `<span class="badge bg-light text-primary border me-1" style="font-size:.7rem;">${escapar(e)}</span>`).join('');
        const esSel = String(f.id_persona) === String(faciState.seleccion);
        const selClasses = esSel ? ' bg-primary-soft border-primary border-start border-4' : '';
        const checkDisplay = esSel ? '' : 'd-none';
        return `<div class="facilitator-item p-3 border-bottom d-flex align-items-center justify-content-between${selClasses}" style="cursor:pointer;"
                onclick="typeof seleccionarFacilitador === 'function' ? seleccionarFacilitador(this, '${escapar(f.id_persona)}', '${escapar(f.nombre)}', '${escapar(f.dni)}', true) : selectFacilitator('${escapar(f.id_persona)}', '${escapar(f.nombre)}', '${escapar(f.dni)}', true)"
                data-id-persona="${escapar(f.id_persona)}" data-nombre="${escapar(f.nombre)}" data-dni="${escapar(f.dni)}">
                <div class="d-flex align-items-center flex-grow-1 overflow-hidden me-2"><div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold text-dark">${escapar(f.nombre)}</h6>
                    <small class="text-muted d-block">C.I: ${escapar(f.dni)}</small>
                    <div class="mt-1">${esp}</div>
                </div></div>
                <div class="check-icon ms-2 ${checkDisplay}">
                    <i class="fas fa-check-circle text-primary fs-4"></i>
                </div></div>`;
    };

    const pintarFilas = (items, limpiar, detalleError) => {
        const cont = document.getElementById('facilitator-container');
        if (!cont) return;
        if (limpiar) cont.innerHTML = '';
        const carga = document.getElementById('facilitator-loading');
        if (carga) carga.remove();
        cont.insertAdjacentHTML('beforeend', items.map(filaHtml).join(''));
        marcarSeleccion();
        if (limpiar && items.length === 0) {
            cont.innerHTML = '<div class="p-4 text-center text-muted small">Sin resultados para esa búsqueda.</div>';
        }
        if (detalleError && !cont.querySelector('.facilitator-item')) {
            cont.innerHTML = `<div class="p-4 text-center text-danger small">No se pudo buscar (${escapar(detalleError)}). Recarga la página e inténtalo de nuevo.</div>`;
        }
    };

    const marcarSeleccion = () => {
        document.querySelectorAll('#facilitator-container .facilitator-item').forEach((row) => {
            const coincide = String(row.dataset.idPersona) === String(faciState.seleccion);
            row.classList.toggle('bg-primary-soft', coincide);
            row.classList.toggle('border-primary', coincide);
            row.classList.toggle('border-start', coincide);
            row.classList.toggle('border-4', coincide);
            const check = row.querySelector('.check-icon');
            if (check) {
                check.classList.toggle('d-none', !coincide);
            }
        });
    };

    const elegirDesdeFila = (row) => {
        if (!row) return;
        faciState.seleccion = row.dataset.idPersona;
        if (typeof seleccionarFacilitador === 'function') {
            seleccionarFacilitador(row, row.dataset.idPersona, row.dataset.nombre, row.dataset.dni, true);
        } else if (typeof selectFacilitator === 'function') {
            selectFacilitator(row.dataset.idPersona, row.dataset.nombre, row.dataset.dni, true);
        }
        marcarSeleccion();
    };

    const cargarPagina = (url) => {
        if (!url || faciState.loading) return;
        faciState.loading = true;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((r) => {
                if (r.ok) return r.json();
                return r.text().then((t) => Promise.reject(new Error('HTTP ' + r.status + ' ' + t.slice(0, 80))));
            })
            .then((json) => {
                const primera = url === faciState.url || !url.includes('page=');
                pintarFilas(json.data || [], primera);
                faciState.next = json.next_page_url || null;
                const more = document.getElementById('facilitator-more');
                if (more) more.style.display = faciState.next ? '' : 'none';
            })
            .catch((err) => {
                pintarFilas([], false, err && err.message ? err.message : 'red');
            })
            .finally(() => { faciState.loading = false; });
    };

    const initFacilitadores = (baseUrl, seleccionado) => {
        faciState = { url: baseUrl, next: null, loading: false, seleccion: seleccionado };
        marcarSeleccion();
        let temporizador = null;
        // Clic delegado: sirve para filas del servidor y del fetch.
        document.getElementById('facilitator-container')?.addEventListener('click', (e) => {
            const item = e.target.closest('.facilitator-item');
            if (item) {
                elegirDesdeFila(item);
            }
        });
        document.getElementById('filtro-facilitador')?.addEventListener('input', (e) => {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => {
                const q = encodeURIComponent(e.target.value.trim());
                cargarPagina(`${baseUrl}?q=${q}`);
            }, 300);
        });
        document.getElementById('facilitator-more')?.addEventListener('click', () => {
            cargarPagina(faciState.next || `${baseUrl}?page=2`);
        });
        // Las filas iniciales ya vienen del servidor: sin auto-fetch.
        const contIni = document.getElementById('facilitator-container');
        const more = document.getElementById('facilitator-more');
        if (more) more.style.display = contIni && contIni.dataset.hayMas ? '' : 'none';
    };

    return { 
        toggleLocalidades, 
        pintarTrimestre, 
        initFacilitadores, 
        setSeleccion: (id) => { faciState.seleccion = id; } 
    };
})();

window.TallerModule = window.TallerModule || {};
window.TallerModule.toggleLocalidades = WizardPaso2.toggleLocalidades;
