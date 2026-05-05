{{-- Estado 8: Finalizado - Coordinador --}}
{{-- El coordinador puede ver contenidos o archivar el curso definitivamente --}}

<a class="btn btn-info w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->id_curso]) }}">
    <i class="fas fa-eye me-2"></i> Ver contenidos
</a>

<button class="btn btn-dark w-100 mb-2" onclick="cerrarCurso({{ $curso->id_curso }})">
    <i class="fas fa-archive me-2"></i> Archivar Curso
</button>
