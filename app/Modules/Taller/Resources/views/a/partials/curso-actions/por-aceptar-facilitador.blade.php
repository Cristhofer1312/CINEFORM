{{-- Estado 1: Por Aceptar - Facilitador --}}
{{-- El facilitador debe aceptar el curso asignado --}}

<a class="btn btn-info w-100 mb-2" disabled>
    <i class="fas fa-user-tie me-2"></i> Eres el instructor de este curso
</a>
<button class="btn btn-success w-100 mb-2" onclick="aceptarCursoFacilitador('{{ $curso->crypt_id }}', this)">
    <i class="fas fa-user-tie me-2"></i> Aceptar Curso
</button>