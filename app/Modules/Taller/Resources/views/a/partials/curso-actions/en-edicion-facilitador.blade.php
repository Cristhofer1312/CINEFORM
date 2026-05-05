{{-- Estado 4: En Edición - Facilitador --}}
{{-- El facilitador puede finalizar la edición o continuar editando --}}

<button class="btn btn-success w-100 mb-2" onclick="finalizarEdicion({{ $curso->id_curso }})">
    <i class="fas fa-user-tie me-2"></i> Finalizar edicion
</button>
<a class="btn btn-primary w-100 mb-2" href="{{ route('taller.cursos.edit', $curso->id_curso) }}">
    <i class="fas fa-user-tie me-2"></i> Editar
</a>