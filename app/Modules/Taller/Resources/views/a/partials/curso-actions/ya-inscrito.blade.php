{{-- Estado 6: Inscripciones - Usuario ya inscrito --}}
{{-- El usuario ya está inscrito y puede cancelar su inscripción --}}

<a class="btn btn-success w-100 mb-2" disabled>
    <i class="fas fa-check-circle me-2"></i> Ya estás inscrito
</a>
<button class="btn btn-outline-danger w-100 mb-2 cancelar-inscripcion-btn"
    data-inscripcion-id="{{ $inscripcion->crypt_id }}">
    <i class="fas fa-times-circle me-2"></i> Cancelar inscripción
</button>