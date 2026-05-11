/**
 * FacilitadorModule: Gestión de selección de facilitadores y modales
 */

const FacilitadorModule = (() => {
    
    /**
     * Filtra los facilitadores en el modal
     */
    const filterFacilitators = () => {
        const searchText = document.getElementById('filtro_nombre_cedula_modal').value.toLowerCase();
        const specId = document.getElementById('filtro_especializacion_modal').value;
        const items = document.querySelectorAll('.facilitator-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            const doc = item.getAttribute('data-doc').toLowerCase();
            const specs = item.getAttribute('data-specializations').split(',');

            const matchesSearch = name.includes(searchText) || doc.includes(searchText);
            const matchesSpec = specId === "" || specs.includes(specId);

            item.style.display = (matchesSearch && matchesSpec) ? 'flex' : 'none';
        });
    };

    /**
     * Selecciona un facilitador del modal
     */
    const selectFacilitador = (id, name, doc) => {
        const inputId = document.getElementById('id_persona');
        const displayName = document.getElementById('selected-facilitator-name');
        const displayDoc = document.getElementById('selected-facilitator-doc');

        if (inputId) inputId.value = id;
        if (displayName) displayName.innerText = name;
        if (displayDoc) displayDoc.innerText = `C.I: ${doc}`;

        // Cerrar modal
        const modalEl = document.getElementById('modalFacilitadores');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    };

    return {
        filterFacilitators,
        selectFacilitador
    };
})();
