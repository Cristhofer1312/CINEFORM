{{-- Estado 6: Inscripciones - Cupos disponibles --}}
{{-- El usuario puede inscribirse al curso --}}

<a href="{{ route('taller.inscripciones.create', $curso->id_curso) }}" class="btn btn-primary w-100 mb-2">
    <i class="fas fa-check-circle me-2"></i> Inscribirse
</a>