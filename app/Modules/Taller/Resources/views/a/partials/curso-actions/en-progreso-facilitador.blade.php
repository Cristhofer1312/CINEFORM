{{-- Estado 7: En Progreso - Facilitador --}}
{{-- El facilitador puede ver y gestionar los contenidos del curso --}}

<a class="btn btn-success w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}">
    <i class="fas fa-user-tie me-2"></i> Ver contenidos
</a>