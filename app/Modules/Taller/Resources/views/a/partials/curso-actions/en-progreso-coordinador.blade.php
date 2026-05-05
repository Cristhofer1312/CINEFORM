{{-- Estado 7: En Progreso - Coordinador --}}
{{-- El coordinador puede ver contenidos, finalizar el curso o hacer edición de contingencia --}}

<a class="btn btn-info w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->id_curso]) }}">
    <i class="fas fa-user-tie me-2"></i> Ver contenidos
</a>

<button class="btn btn-danger w-100 mb-2" onclick="finalizarCurso({{ $curso->id_curso }})">
    Finalizar Curso
</button>

<a href="{{ route('taller.cursos.edit', $curso->id_curso) }}" class="btn btn-success w-100 mb-2">
    Edicion
</a>