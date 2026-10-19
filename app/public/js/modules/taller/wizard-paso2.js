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

    const toggleLocalidades = (el) => {
        const cont = document.getElementById('container-localidades');
        if (!cont) return;
        cont.style.display = el && el.checked ? 'none' : '';
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('input[name="fecha_inicio"]')
            ?.addEventListener('change', pintarTrimestre);
        pintarTrimestre();
    });

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
        const sel = String(f.id_persona) === String(faciState.seleccion) ? ' bg-light' : '';
        return `<div class="facilitator-item p-3 border-bottom${sel}" style="cursor:pointer;"
                data-id-persona="${escapar(f.id_persona)}" data-nombre="${escapar(f.nombre)}" data-dni="${escapar(f.dni)}">
                <div class="d-flex align-items-center"><div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold text-dark">${escapar(f.nombre)}</h6>
                    <small class="text-muted d-block">C.I: ${escapar(f.dni)}</small>
                    <div class="mt-1">${esp}</div>
                </div></div></div>`;
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
            row.classList.toggle('bg-light', row.dataset.idPersona === String(faciState.seleccion));
        });
    };

    const elegirDesdeFila = (row) => {
        if (!row) return;
        selectFacilitator(row.dataset.idPersona, row.dataset.nombre, row.dataset.dni);
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
            elegirDesdeFila(e.target.closest('.facilitator-item'));
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

    return { toggleLocalidades, pintarTrimestre, initFacilitadores };
})();

window.TallerModule = window.TallerModule || {};
window.TallerModule.toggleLocalidades = WizardPaso2.toggleLocalidades;
