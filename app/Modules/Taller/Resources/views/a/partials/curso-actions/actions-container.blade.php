{{-- Contenedor Maestro de Acciones (Modelo de Capacidades) --}}

@php
    // Categorización de capacidades para organizar la interfaz
    $principales = ['inscribirse', 'cancelar_inscripcion', 'acceder_contenido', 'emitir_certificado', 'ver_archivo'];
    $gestion = ['ver_participantes', 'reasignar_facilitador', 'ajustar_certificado', 'aceptar_asignacion', 'rechazar_asignacion', 'editar', 'enviar_aprobacion', 'aprobar', 'rechazar', 'finalizar_inscripciones', 'finalizar_curso', 'cerrar_curso', 'ver_motivo', 'en_revision', 'gestionar_certificacion', 'ver_satisfaccion', 'gestionar_asistencia', 'consultar_asistencia'];
    
    // Filtrar qué capacidades tiene el usuario en cada categoría
    $capsPrincipales = array_intersect($capacidades, $principales);
    $capsGestion = array_intersect($capacidades, $gestion);
@endphp

<div class="actions-wrapper">
    
    <!-- ZONA 0: ESTADO DE POSTULACIÓN (Para el Participante) -->
    @if(isset($inscripcion) && $inscripcion && ($curso->estado_actual->id_estado ?? 0) <= 6)
        <div class="postulation-status-zone mb-4">
            <div class="card border-0 shadow-xs rounded-4 overflow-hidden border-start border-4 
                {{ $inscripcion->esPostulado() ? 'border-primary bg-primary-soft' : '' }}
                {{ $inscripcion->esAprobado() ? 'border-success bg-success-soft' : '' }}
                {{ $inscripcion->esRechazado() ? 'border-warning bg-warning-soft' : '' }}
                {{ $inscripcion->esDenegado() ? 'border-danger bg-danger-soft' : '' }}
                {{ $inscripcion->esRetirado() ? 'border-secondary bg-light' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas
                                {{ $inscripcion->esPostulado() ? 'fa-hourglass-half text-primary' : '' }}
                                {{ $inscripcion->esAprobado() ? 'fa-check-circle text-success' : '' }}
                                {{ $inscripcion->esRechazado() ? 'fa-exclamation-triangle text-warning' : '' }}
                                {{ $inscripcion->esDenegado() ? 'fa-times-circle text-danger' : '' }}
                                {{ $inscripcion->esRetirado() ? 'fa-user-minus text-secondary' : '' }}
                                fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">
                                Estado: 
                                {{ $inscripcion->esPostulado() ? 'Postulación en Revisión' : '' }}
                                {{ $inscripcion->esAprobado() ? 'Inscrito Formalmente' : '' }}
                                {{ $inscripcion->esRechazado() ? 'Postulación con Observaciones' : '' }}
                                {{ $inscripcion->esDenegado() ? 'Postulación Denegada' : '' }}
                                {{ $inscripcion->esRetirado() ? 'Retirado del Curso' : '' }}
                            </h6>
                            <p class="mb-0 text-muted small">
                                {{ $inscripcion->esPostulado() ? 'Tu solicitud está siendo evaluada por la coordinación.' : '' }}
                                {{ $inscripcion->esAprobado() ? '¡Felicidades! Ya puedes acceder a los contenidos.' : '' }}
                                {{ $inscripcion->esRechazado() ? 'Debes corregir los documentos o respuestas indicadas.' : '' }}
                                {{ $inscripcion->esDenegado() ? 'Lo sentimos, no has sido admitido en este programa.' : '' }}
                                {{ $inscripcion->esRetirado() ? 'La coordinación te retiró de este curso.' : '' }}
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
                        @if(!isset($inscripcion) || ($inscripcion && $inscripcion->esAprobado()))
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
                        @endif
                        @break

                    @case('emitir_certificado')
                        @if(isset($inscripcion) && $inscripcion->certificadoAprobado())
                            @if(($contextoSatisfaccion['disponible'] ?? false) && !($contextoSatisfaccion['respondida'] ?? false))
                                <a class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push" href="{{ route('taller.cursos.satisfaccion.create', $curso->crypt_id) }}">
                                    <i class="fas fa-clipboard-check me-2"></i> Responder encuesta
                                </a>
                                <div class="alert alert-info text-center small p-3 mb-0 rounded-4 border-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Completa la encuesta para habilitar la descarga de tu certificado.
                                </div>
                            @else
                                <a class="btn btn-gold w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push border-0" style="background: linear-gradient(135deg, #d4af37 0%, #f9d71c 100%); color: #000;" href="{{ route('taller.certificados.descargar', $curso->crypt_id) }}">
                                    <i class="fas fa-award me-2"></i> Obtener Certificado
                                </a>
                            @endif
                        @elseif(isset($inscripcion) && $inscripcion->certificadoDenegado())
                            <div class="alert alert-danger text-center small p-3 mb-0 rounded-4 border-0">
                                <i class="fas fa-times-circle fa-2x mb-2 d-block text-danger opacity-60"></i>
                                <strong class="d-block mb-1">Certificado No Aprobado</strong>
                                <p class="mb-0 opacity-75">{{ $inscripcion->certificado_motivo_denegacion }}</p>
                            </div>
                        @else
                            <div class="alert alert-secondary text-center small p-3 mb-0 rounded-4 border-0">
                                <i class="fas fa-hourglass-half fa-2x mb-2 d-block opacity-40"></i>
                                <strong class="d-block mb-1">Certificado en Revisión</strong>
                                <p class="mb-0 opacity-75">Tu certificado está siendo evaluado por el facilitador.</p>
                            </div>
                        @endif
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

    <!-- ZONA 2: PANEL POR PAPEL. Con perfiles sumados una persona puede ser
         coordinación y facilitador del mismo curso: cada bloque solo muestra
         las acciones de ese papel. -->
    @php
        $bloques = [
            ['titulo' => 'Coordinación', 'icono' => 'fa-shield-alt', 'caps' => $capsCoordinacion ?? null],
            ['titulo' => 'Como facilitador', 'icono' => 'fa-chalkboard-teacher', 'caps' => $capsFacilitador ?? null],
        ];
        // Sin desglose (otras vistas que incluyen este parcial): un solo bloque.
        if ($bloques[0]['caps'] === null && $bloques[1]['caps'] === null) {
            $bloques = [['titulo' => 'Panel de Administración', 'icono' => 'fa-shield-alt', 'caps' => $capsGestion]];
        }
        $esPropioYCoordina = ! empty($capsCoordinacion) && ! empty($capsFacilitador);
    @endphp

    @foreach($bloques as $bloque)
        @php($capsBloque = array_values(array_intersect($bloque['caps'] ?? [], $gestion)))
        @continue(count($capsBloque) === 0)
        @php($botones = trim(view('taller::a.partials.curso-actions.boton-gestion-lista', ['caps' => $capsBloque, 'curso' => $curso])->render()))
        @continue($botones === '' && ! ($esPropioYCoordina && $loop->first))
        <div class="management-zone p-4 bg-light rounded-4 border shadow-xs {{ $loop->last ? '' : 'mb-3' }}">
            <small class="text-muted fw-bold d-block mb-4 text-uppercase text-center" style="font-size: 0.75rem; letter-spacing: 1.5px; opacity: 0.8;">
                <i class="fas {{ $bloque['icono'] }} me-1"></i> {{ $bloque['titulo'] }}
            </small>
            <div class="d-grid gap-3">
                {!! $botones !!}
                @if($esPropioYCoordina && $loop->first)
                    <p class="small text-muted text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Eres el facilitador de este curso: revisarlo, aprobarlo o reasignarlo corresponde a otra persona de coordinación.
                    </p>
                @endif
            </div>
        </div>
    @endforeach

    <!-- AVISO SIN CAPACIDADES (GUEST / SIN CUPOS) -->
    @if(count($capacidades) == 0)
         <div class="alert alert-warning text-center small p-3 mb-0 rounded-4">
            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block opacity-50"></i>
            <strong>No hay acciones disponibles</strong>
            <p class="mb-0 mt-2 opacity-75">El curso no acepta nuevas inscripciones en este momento.</p>
        </div>
    @endif

</div>
