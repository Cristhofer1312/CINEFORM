{{-- Contenedor Maestro de Acciones (Modelo de Capacidades) --}}

@php
    // Categorización de capacidades para organizar la interfaz
    $principales = ['inscribirse', 'cancelar_inscripcion', 'acceder_contenido', 'emitir_certificado', 'ver_archivo'];
    $gestion = ['ver_participantes', 'aceptar_asignacion', 'rechazar_asignacion', 'editar', 'enviar_aprobacion', 'aprobar', 'rechazar', 'finalizar_inscripciones', 'finalizar_curso', 'cerrar_curso', 'ver_motivo', 'en_revision'];
    
    // Filtrar qué capacidades tiene el usuario en cada categoría
    $capsPrincipales = array_intersect($capacidades, $principales);
    $capsGestion = array_intersect($capacidades, $gestion);
@endphp

<div class="actions-wrapper">
    
    <!-- ZONA 0: ESTADO DE POSTULACIÓN (Para el Participante) -->
    @if(isset($inscripcion) && $inscripcion && $curso->id_estado <= 6)
        <div class="postulation-status-zone mb-4">
            <div class="card border-0 shadow-xs rounded-4 overflow-hidden border-start border-4 
                {{ $inscripcion->esPostulado() ? 'border-primary bg-primary-soft' : '' }}
                {{ $inscripcion->esAprobado() ? 'border-success bg-success-soft' : '' }}
                {{ $inscripcion->esRechazado() ? 'border-warning bg-warning-soft' : '' }}
                {{ $inscripcion->esDenegado() ? 'border-danger bg-danger-soft' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas 
                                {{ $inscripcion->esPostulado() ? 'fa-hourglass-half text-primary' : '' }}
                                {{ $inscripcion->esAprobado() ? 'fa-check-circle text-success' : '' }}
                                {{ $inscripcion->esRechazado() ? 'fa-exclamation-triangle text-warning' : '' }}
                                {{ $inscripcion->esDenegado() ? 'fa-times-circle text-danger' : '' }}
                                fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">
                                Estado: 
                                {{ $inscripcion->esPostulado() ? 'Postulación en Revisión' : '' }}
                                {{ $inscripcion->esAprobado() ? 'Inscrito Formalmente' : '' }}
                                {{ $inscripcion->esRechazado() ? 'Postulación con Observaciones' : '' }}
                                {{ $inscripcion->esDenegado() ? 'Postulación Denegada' : '' }}
                            </h6>
                            <p class="mb-0 text-muted small">
                                {{ $inscripcion->esPostulado() ? 'Tu solicitud está siendo evaluada por la coordinación.' : '' }}
                                {{ $inscripcion->esAprobado() ? '¡Felicidades! Ya puedes acceder a los contenidos.' : '' }}
                                {{ $inscripcion->esRechazado() ? 'Debes corregir los documentos o respuestas indicadas.' : '' }}
                                {{ $inscripcion->esDenegado() ? 'Lo sentimos, no has sido admitido en este programa.' : '' }}
                            </p>
                        </div>
                    </div>
                    @if($inscripcion->esRechazado())
                        <div class="mt-3 p-2 bg-white rounded border border-warning-subtle small text-dark">
                            <strong>Motivo:</strong> {{ $inscripcion->motivo_estado }}
                        </div>
                        <a href="{{ route('taller.inscripciones.create', $curso->id_curso) }}" class="btn btn-warning btn-sm w-100 fw-bold mt-2 rounded-pill shadow-sm">
                            <i class="fas fa-edit me-2"></i> Corregir Postulación
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    <!-- ZONA 1: ACCIONES PRINCIPALES (Usuario Final / Participante) -->
    @if(count($capsPrincipales) > 0)
        <div class="main-actions-zone d-grid gap-3 mb-4">
            @foreach($capsPrincipales as $cap)
                @switch($cap)
                    @case('inscribirse')
                        <a href="{{ route('taller.inscripciones.create', $curso->id_curso) }}" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push">
                            <i class="fas fa-paper-plane me-2"></i> Postularme al Programa
                        </a>
                        @break

                    @case('cancelar_inscripcion')
                        @if($inscripcion && ($inscripcion->esPostulado() || $inscripcion->esRechazado()))
                            <button class="btn btn-outline-danger w-100 fw-bold py-2 rounded-pill shadow-xs" onclick="cancelarInscripcion('{{ $inscripcion->crypt_id }}')">
                                <i class="fas fa-user-minus me-2"></i> Retirar Postulación
                            </button>
                        @endif
                        @break

                    @case('acceder_contenido')
                        @if($curso->contenidos_count > 0 || $curso->contenidos->count() > 0)
                            <a class="btn btn-success w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push" href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}">
                                <i class="fas fa-play-circle me-2"></i> Ver contenidos del programa
                            </a>
                        @else
                            <div class="alert alert-light text-center small p-3 mb-0 rounded-4 border">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                                <strong class="opacity-50">Sin contenidos disponibles</strong>
                            </div>
                        @endif
                        @break

                    @case('emitir_certificado')
                        <a class="btn btn-gold w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push border-0" style="background: linear-gradient(135deg, #d4af37 0%, #f9d71c 100%); color: #000;" href="{{ route('taller.certificados.descargar', $curso->crypt_id) }}">
                            <i class="fas fa-award me-2"></i> Obtener Certificado
                        </a>
                        @break

                    @case('ver_archivo')
                         <div class="alert alert-secondary text-center small p-3 mb-0 rounded-4">
                            <i class="fas fa-archive fa-2x mb-2 d-block opacity-50"></i>
                            <strong>Programa Archivado</strong>
                            <p class="mb-0 mt-2 opacity-75">Este curso ha concluido y se encuentra en el historial.</p>
                        </div>
                        @break
                @endswitch
            @endforeach
        </div>
    @endif

    <!-- ZONA 2: HERRAMIENTAS DE GESTIÓN (Administración / Operación) -->
    @if(count($capsGestion) > 0)
        <div class="management-zone p-4 bg-light rounded-4 border shadow-xs">
            <small class="text-muted fw-bold d-block mb-4 text-uppercase text-center" style="font-size: 0.65rem; letter-spacing: 1.5px; opacity: 0.8;">
                <i class="fas fa-shield-alt me-1"></i> Panel de Administración
            </small>
            
            <div class="d-grid gap-3">
                @foreach($capsGestion as $cap)
                    @switch($cap)   
                        @case('ver_participantes')
                            <a href="{{ route('taller.cursos.participantes', $curso->crypt_id) }}" class="btn btn-secondary btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                                <i class="fas fa-users-cog me-1"></i> Postulados y Participantes
                            </a>
                            @break
                        @case('aceptar_asignacion')
                            <button class="btn btn-success btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="aceptarCursoFacilitador('{{ $curso->crypt_id }}')">
                                <i class="fas fa-check-circle me-1"></i> Aceptar Asignación
                            </button>
                            @break
                        @case('rechazar_asignacion')
                             <button class="btn btn-outline-danger btn-sm py-2 fw-bold rounded-pill" onclick="rechazarContenido('{{ $curso->crypt_id }}')">
                                 <i class="fas fa-times-circle me-1"></i> Rechazar
                             </button>
                             @break
                        @case('editar')
                            <a href="{{ route('taller.cursos.edit', $curso->crypt_id) }}" class="btn btn-white btn-sm py-2 fw-bold border shadow-xs rounded-pill">
                                <i class="fas fa-edit me-1 text-primary"></i> Editar Programa
                            </a>
                            <a href="{{ route('taller.cursos.requisitos.edit', $curso->id_curso) }}" class="btn btn-white btn-sm py-2 fw-bold border shadow-xs rounded-pill mt-2">
                                <i class="fas fa-list-check me-1 text-info"></i> Editar Requisitos
                            </a>
                            @break
                        @case('enviar_aprobacion')
                            <button class="btn btn-primary btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarEdicion('{{ $curso->crypt_id }}')">
                                <i class="fas fa-paper-plane me-1"></i> Enviar a Revisión
                            </button>
                            @break
                        @case('aprobar')
                            <button class="btn btn-success btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="aprobarCurso('{{ $curso->crypt_id }}')">
                                <i class="fas fa-check-double me-1"></i> Aprobar Propuesta
                            </button>
                            @break
                        @case('rechazar')
                            <button class="btn btn-danger btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="rechazarContenido('{{ $curso->crypt_id }}')">
                                <i class="fas fa-ban me-1"></i> Declinar Propuesta
                            </button>
                            @break
                        @case('finalizar_inscripciones')
                            <button class="btn btn-warning btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarInscripciones('{{ $curso->crypt_id }}')">
                                <i class="fas fa-user-lock me-1"></i> Cerrar Inscripciones
                            </button>
                            @break
                        @case('finalizar_curso')
                            <button class="btn btn-danger btn-sm py-2 fw-bold rounded-pill shadow-xs" onclick="finalizarCurso('{{ $curso->crypt_id }}')">
                                <i class="fas fa-stop-circle me-1"></i> Finalizar Curso
                            </button>
                            @break
                        @case('cerrar_curso')
                            <button class="btn btn-dark btn-sm py-2 fw-bold rounded-pill shadow-sm" onclick="cerrarCurso('{{ $curso->crypt_id }}')">
                                <i class="fas fa-archive me-1"></i> Archivar Definitivamente
                            </button>
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
                                <span class="fw-bold text-danger small">Propuesta Rechazada</span>
                            </div>
                            <button class="btn btn-info btn-sm py-2 fw-bold rounded-pill shadow-xs text-white w-100"
                                    onclick="verObservaciones('{{ $curso->crypt_id }}')">
                                <i class="fas fa-clipboard-list me-1"></i> Ver Observaciones
                            </button>
                            @break
                    @endswitch
                @endforeach
            </div>
        </div>
    @endif

    <!-- AVISO SIN CAPACIDADES (GUEST / SIN CUPOS) -->
    @if(count($capacidades) == 0)
         <div class="alert alert-warning text-center small p-3 mb-0 rounded-4">
            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block opacity-50"></i>
            <strong>No hay acciones disponibles</strong>
            <p class="mb-0 mt-2 opacity-75">El curso no acepta nuevas inscripciones en este momento.</p>
        </div>
    @endif

</div>
