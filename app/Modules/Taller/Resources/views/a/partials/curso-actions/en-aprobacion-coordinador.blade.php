{{-- Estado 5: En Aprobación - Coordinador --}}
{{-- Solo quien tiene permiso de APROBAR_CURSO puede abrir inscripciones --}}

@php
    $puedeAprobar = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::APROBAR_CURSO);
@endphp

@if($puedeAprobar)
    <button class="btn btn-success w-100 mb-2 fw-bold" onclick="aprobarCurso('{{ $curso->crypt_id }}')">
        <i class="fas fa-check-circle me-2"></i> Aprobar y Abrir Inscripciones
    </button>
@else
    <div class="alert alert-info small py-2 text-center mb-2">
        <i class="fas fa-lock me-1"></i> Este curso está pendiente de aprobación por un revisor autorizado.
    </div>
@endif

<button class="btn btn-danger w-100 mb-2" onclick="rechazarContenido('{{ $curso->crypt_id }}')">
    <i class="fas fa-times-circle me-2"></i> Declinar Propuesta
</button>