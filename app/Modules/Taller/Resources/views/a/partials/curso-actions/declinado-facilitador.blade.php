{{-- Estado 3: Declinado - Facilitador --}}
{{-- El facilitador puede ver el motivo de rechazo y realizar ediciones --}}

<i class="fas fa-user-tie me-2"></i> Contenido sugerido Declinado

<button class="btn btn-danger w-100 mb-2" data-motivo="{{ $curso->estado_actual->pivot->motivo ?? '' }}"
    data-nombre="{{ $curso->nombre }}"
    onclick="verMotivoRechazo({{ $curso->id_curso }}, this.dataset.motivo, this.dataset.nombre)">
    Motivo de rechazo
</button>

<a class="btn btn-primary w-100 mb-2" href="{{ route('taller.cursos.edit', $curso->id_curso) }}">
    <i class="fas fa-user-tie me-2"></i> Editar
</a>

<button class="btn btn-success w-100 mb-2" onclick="finalizarEdicion({{ $curso->id_curso }})">
    <i class="fas fa-user-tie me-2"></i> Finalizar edicion
</button>