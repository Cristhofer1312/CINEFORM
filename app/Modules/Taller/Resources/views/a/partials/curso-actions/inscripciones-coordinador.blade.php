{{-- Estado 6: Inscripciones - Coordinador --}}
{{-- El coordinador puede finalizar el período de inscripciones --}}

<a class="btn btn-info w-100 mb-2" disabled>
    <i class="fas fa-user-tie me-2"></i> Gestión de Inscripciones
</a>

<button class="btn btn-success w-100 mb-2" onclick="finalizarInscripciones('{{ $curso->crypt_id }}')">
    <i class="fas fa-lock me-2"></i> Finalizar Inscripciones
</button>