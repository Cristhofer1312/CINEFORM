{{-- Estado 6: Inscripciones - Cupos disponibles --}}
{{-- El usuario puede inscribirse al curso --}}

<button class="btn btn-primary w-100 mb-2" onclick="inscribirAlCurso('{{ $curso->crypt_id }}')">
    <i class="fas fa-check-circle me-2"></i> Inscribirse
</button>