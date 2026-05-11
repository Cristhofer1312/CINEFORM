{{-- Estado 8: Finalizado - Facilitador --}}
{{-- El facilitador puede ver los contenidos del curso finalizado --}}

<a class="btn btn-info w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}">
    <i class="fas fa-eye me-2"></i> Ver contenidos
</a>

<div class="alert alert-secondary text-center small p-2 mb-0">
    <i class="fas fa-info-circle me-1"></i> El programa ha finalizado.
</div>
