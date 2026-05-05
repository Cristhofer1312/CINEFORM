{{-- Estado 8: Finalizado - Estudiante --}}
{{-- El estudiante puede ver contenidos y emitir su certificado --}}

<a class="btn btn-info w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->id_curso]) }}">
    <i class="fas fa-eye me-2"></i> Ver contenidos
</a>

<a class="btn btn-success w-100 mb-2" href="#">
    <i class="fas fa-certificate me-2"></i> Emitir Certificado
</a>
