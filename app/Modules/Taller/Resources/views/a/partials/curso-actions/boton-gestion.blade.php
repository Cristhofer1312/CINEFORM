{{--
    Un botón de gestión del curso. Cada uno se vuelve a filtrar con la misma
    regla de la Policy que aplica el servidor, para que el panel nunca muestre
    una acción que después se rechace (p. ej. aprobar el curso propio).
--}}
@switch($cap)
    @case('ver_participantes')
        @can('verParticipantes', $curso)
            <a href="{{ route('taller.cursos.participantes', $curso->crypt_id) }}" class="btn btn-secondary btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                <i class="fas fa-users-cog me-1"></i> Postulados y Participantes
            </a>
        @endcan
        @break
    @case('reasignar_facilitador')
        @can('reasignarFacilitador', $curso)
            <button type="button" class="btn btn-warning btn-sm py-2 fw-bold rounded-pill shadow-xs text-dark" onclick="abrirModalReasignarFacilitador()">
                <i class="fas fa-user-edit me-1"></i> Reasignar Facilitador
            </button>
        @endcan
        @break
    @case('ajustar_certificado')
        @can('gestionar', $curso)
            <a href="{{ route('taller.cursos.certificado.edit', $curso->crypt_id) }}" class="btn btn-outline-primary btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                <i class="fas fa-certificate me-1"></i> Ajustar Certificado
            </a>
        @endcan
        @break
    @case('aceptar_asignacion')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::EDICION->value])
            <button class="btn btn-success btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="aceptarCursoFacilitador('{{ $curso->crypt_id }}', this)">
                <i class="fas fa-check-circle me-1"></i> Aceptar Asignación
            </button>
        @endcan
        @break
    @case('rechazar_asignacion')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::RECHAZADO->value])
            <button class="btn btn-outline-danger btn-sm py-2 fw-bold rounded-pill" onclick="rechazarContenido('{{ $curso->crypt_id }}', 2)">
                <i class="fas fa-times-circle me-1"></i> Rechazar Asignación
            </button>
        @endcan
        @break
    @case('editar')
        @can('editar', $curso)
            <a href="{{ route('taller.cursos.edit', $curso->crypt_id) }}" class="btn btn-white btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                <i class="fas fa-edit me-1 text-primary"></i> Editar Programa
            </a>
            <a href="{{ route('taller.cursos.requisitos.edit', $curso->id_curso) }}" class="btn btn-white btn-sm py-2 fw-bold border shadow-xs rounded-pill mt-2">
                <i class="fas fa-list-check me-1 text-info"></i> Editar Requisitos
            </a>
        @endcan
        @break
    @case('enviar_aprobacion')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::APROBACION->value])
            <button class="btn btn-primary btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarEdicion('{{ $curso->crypt_id }}')">
                <i class="fas fa-paper-plane me-1"></i> Enviar a Revisión
            </button>
        @endcan
        @break
    @case('aprobar')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::INSCRIPCION->value])
            <button class="btn btn-success btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="aprobarCurso('{{ $curso->crypt_id }}')">
                <i class="fas fa-check-double me-1"></i> Aprobar Propuesta
            </button>
        @endcan
        @break
    @case('rechazar')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::DECLINADO->value])
            <button class="btn btn-danger btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="rechazarContenido('{{ $curso->crypt_id }}', 3)">
                <i class="fas fa-ban me-1"></i> Declinar Propuesta
            </button>
        @endcan
        @break
    @case('finalizar_inscripciones')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::EN_CURSO->value])
            <button class="btn btn-warning btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarInscripciones('{{ $curso->crypt_id }}')">
                <i class="fas fa-user-lock me-1"></i> Cerrar Inscripciones
            </button>
        @endcan
        @break
    @case('finalizar_curso')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::FINALIZADO->value])
            <button class="btn btn-danger btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarCurso('{{ $curso->crypt_id }}')">
                <i class="fas fa-stop-circle me-1"></i> Finalizar Curso
            </button>
        @endcan
        @break
    @case('cerrar_curso')
        @can('cambiarEstado', [$curso, \App\Enums\EstadoCurso::CERRADO->value])
            <button class="btn btn-dark btn-sm py-2 fw-bold rounded-pill shadow-sm" onclick="cerrarCurso('{{ $curso->crypt_id }}')">
                <i class="fas fa-archive me-1"></i> Archivar Definitivamente
            </button>
        @endcan
        @break
    @case('en_revision')
        <div class="alert alert-info text-center small p-3 mb-0 rounded-4 border-0">
            <i class="fas fa-hourglass-half fa-2x mb-2 d-block text-info opacity-60"></i>
            <strong class="d-block mb-1">Propuesta en Evaluación</strong>
            <p class="mb-0 opacity-75">Tu propuesta está siendo revisada por la coordinación. Recibirás una notificación cuando sea procesada.</p>
        </div>
        @break
    @case('ver_motivo')
        <div class="d-flex align-items-center justify-content-center mb-2">
            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
            <span class="fw-bold text-danger small">
                {{ ($curso->estado_actual->id_estado ?? 0) == 2 ? 'Asignación Rechazada' : 'Propuesta Declinada' }}
            </span>
        </div>
        <button class="btn btn-info btn-sm py-2 fw-bold rounded-pill shadow-xs text-white w-100"
                onclick="verObservaciones('{{ $curso->crypt_id }}')">
            <i class="fas fa-clipboard-list me-1"></i> Ver Observaciones
        </button>
        @break
    @case('gestionar_asistencia')
    @case('consultar_asistencia')
        @can('gestionarAsistencia', $curso)
            <a href="{{ route('taller.asistencia.consolidado', $curso->crypt_id) }}" class="btn btn-outline-primary btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                <i class="fas fa-user-check me-1"></i> {{ $cap === 'gestionar_asistencia' ? 'Gestionar Asistencia' : 'Consultar Asistencia' }}
            </a>
        @endcan
        @break
    @case('gestionar_certificacion')
        <a href="{{ route('taller.certificacion.panel', $curso->crypt_id) }}" class="btn btn-outline-success btn-sm py-2 fw-bold border shadow-xs rounded-pill">
            <i class="fas fa-clipboard-check me-1"></i> Panel de Certificación
        </a>
        @break
    @case('ver_satisfaccion')
        @can('verSatisfaccion', $curso)
            <a href="{{ route('taller.cursos.satisfaccion.resultados', $curso->crypt_id) }}" class="btn btn-outline-info btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                <i class="fas fa-poll me-1"></i> Resultados de Encuesta
            </a>
        @endcan
        @break
@endswitch
