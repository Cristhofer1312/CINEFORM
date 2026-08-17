@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Alertas --}}
            @if(session('success'))
                <x-taller.alert type="success" title="Éxito">
                    {{ session('success') }}
                </x-taller.alert>
            @endif
            @if(session('info'))
                <x-taller.alert type="info" title="Información">
                    {{ session('info') }}
                </x-taller.alert>
            @endif
            @if(session('error'))
                <x-taller.alert type="danger" title="Atención">
                    {{ session('error') }}
                </x-taller.alert>
            @endif

            {{-- Header Hero --}}
            <div class="text-center mb-5">
                <h1 class="fw-bold text-dark mb-3">
                    <i class="fas fa-chalkboard-teacher me-2 text-primary"></i> Sé Facilitador en CINEFORM
                </h1>
                <p class="lead text-muted">Únete a nuestro equipo de facilitadores y forma parte de la formación
                    cinematográfica en Venezuela.</p>
            </div>

            {{-- ¿Qué hace un facilitador? --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                <div class="card-header bg-primary-soft border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i> Deberes y
                        Responsabilidades del Facilitador</h5>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-briefcase me-2 text-primary"></i> 1. Deberes y
                        Responsabilidades Administrativas u Operativas</h6>
                    <ul class="text-muted mb-4" style="text-align: justify;">
                        <li class="mb-2"><strong>Cumplimiento de acuerdos:</strong> Cumplir estrictamente con los acuerdos
                            concertados para la actividad formativa con la Gerencia del Laboratorio del Cine y el
                            Audiovisual de Venezuela a través de la Coordinación de Formación.</li>
                        <li class="mb-2"><strong>Definición previa de parámetros:</strong> Determinar en mutuo acuerdo con
                            la Gerencia el nombre de la actividad, contenido, horas académicas, cantidad de cupos, material
                            de apoyo y plantillas.</li>
                        <li class="mb-2"><strong>Respeto al tope de cupos:</strong> Respetar el límite de participantes
                            fijado y abstenerse de incorporar alumnos adicionales una vez finalizado el proceso de
                            inscripción.</li>
                        <li class="mb-2"><strong>Documentación al día:</strong> Mantener actualizados sus documentos
                            personales y legales (RIF, Cédula de Identidad, Certificación de cuenta bancaria, Registro CNAC,
                            entre otros) para permitir el procesamiento oportuno de sus pagos.</li>
                        <li class="mb-2"><strong>Respeto a los canales de comunicación:</strong> Respetar la administración
                            y derechos sobre los grupos de WhatsApp o Telegram, los cuales pertenecen al ente organizador.
                        </li>
                        <li class="mb-2"><strong>Canalización interna de eventualidades:</strong> Manifestar cualquier
                            necesidad, problema o imposibilidad de dictar el curso (por causas de fuerza mayor) de forma
                            directa a la Coordinación de Formación, evitando publicar estas situaciones en los grupos o
                            redes de los estudiantes.</li>
                    </ul>

                    <h6 class="fw-bold mb-3"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i> 2. Deberes y
                        Responsabilidades Pedagógicas</h6>
                    <ul class="text-muted mb-4" style="text-align: justify;">
                        <li class="mb-2"><strong>Diseño y reporte del plan de estudios:</strong> Diseñar y estructurar el
                            plan de contenido conforme a los días y horas acordados, debiendo entregarlo a la Coordinación
                            de Formación antes de iniciar las clases.</li>
                        <li class="mb-2"><strong>Uso de la imagen institucional:</strong> Utilizar obligatoriamente la
                            imagen gráfica y los logos oficiales del CNAC en todas las presentaciones e insumos didácticos
                            que emplee.</li>
                        <li class="mb-2"><strong>Evaluación y seguimiento:</strong> Establecer las prácticas y evaluaciones
                            de la actividad, llevar un registro estricto de la asistencia física e incentivar a los alumnos
                            a cumplir con el 100 % de asistencia para finalizar el curso con éxito.</li>
                        <li class="mb-2"><strong>Atención a público especial (niños y adolescentes):</strong> En caso de
                            impartir actividades de Modalidad Especial, debe adaptar los contenidos a dicha audiencia y
                            orientar sobre la consignación del requisito de la Carta de Autorización del representante.</li>
                        <li class="mb-2"><strong>Sometimiento a evaluación docente:</strong> Aceptar la evaluación final que
                            realizarán los estudiantes sobre su desempeño metodológico, organización, dominio de la materia
                            y calidad del material de apoyo, con fines de mejora continua.</li>
                    </ul>

                    <h6 class="fw-bold mb-3"><i class="fas fa-balance-scale me-2 text-primary"></i> 3. Deberes y
                        Responsabilidades Éticas</h6>
                    <ul class="text-muted mb-0" style="text-align: justify;">
                        <li class="mb-2"><strong>Respeto a los derechos de autor:</strong> Queda expresamente comprometido a
                            no utilizar ni apropiarse de las ideas u historias presentadas por los participantes en sus
                            guiones, garantizando la protección de la autoría intelectual de los estudiantes (Cláusula
                            Décima).</li>
                    </ul>
                </div>
            </div>

            {{-- Requisitos para postularse --}}
            @if($requisitos->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden border-top border-4 border-success">
                    <div class="card-header bg-success-soft border-0 py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="fas fa-clipboard-list me-2"></i> Requisitos para
                            Postularte</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($requisitos as $req)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @if($req->tipo === 'documento')
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            @elseif($req->tipo === 'pregunta')
                                                <i class="fas fa-question-circle fa-2x text-info"></i>
                                            @else
                                                <i class="fas fa-info-circle fa-2x text-success"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $req->titulo }}
                                                @if($req->obligatorio) <span class="text-danger">*</span> @endif
                                            </h6>
                                            @if($req->descripcion)
                                                <p class="text-muted small mb-0">{{ $req->descripcion }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Estados de postulación --}}
            @if($postulacionPendiente)
                <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center mb-4">
                    <i class="fas fa-clock fa-2x mb-3 text-warning"></i>
                    <h5 class="fw-bold">Postulación en Revisión</h5>
                    <p class="mb-0">Tu postulación se encuentra actualmente en fase de revisión. Te notificaremos por correo
                        electrónico cuando tengamos una respuesta.</p>
                    <small class="text-muted">Enviada el {{ $postulacionPendiente->creado_en->format('d/m/Y H:i') }}</small>
                </div>
            @elseif($ultimaPostulacionRechazada)
                <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Observaciones en tu Postulación
                        Anterior</h5>
                    <p>{{ $ultimaPostulacionRechazada->motivo_rechazo }}</p>
                    <hr>
                    <p class="mb-0">Puedes corregir tus respuestas y <strong>volver a postularte</strong>.</p>
                </div>
            @endif

            {{-- Botón CTA --}}
            @if(!$postulacionPendiente)
                <div class="text-center mb-5">
                    <div class="form-check d-inline-block text-start mb-4 bg-light p-3 rounded border border-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="checkAceptoCondiciones"
                            style="cursor: pointer; width: 1.25em; height: 1.25em; margin-top: 0.15em;">
                        <label class="form-check-label fw-bold text-dark" for="checkAceptoCondiciones" style="cursor: pointer;">
                            He leído y acepto los deberes y responsabilidades correspondientes al papel de facilitador.
                        </label>
                    </div>
                    <br>
                    <a href="{{ route('taller.postulacion-facilitador.formulario') }}" id="btnPostularme"
                        class="btn btn-primary btn-lg fw-bold px-5 py-3 rounded-pill shadow-lg disabled">
                        <i class="fas fa-paper-plane me-2"></i> Postularme
                    </a>
                </div>
            @endif

            {{-- Preview mode link --}}
            @if(!empty($isPreview) && $isPreview)
                <div class="text-center mb-4">
                    <a href="{{ route('taller.postulacion-facilitador.admin') }}"
                        class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Panel Admin
                    </a>
                </div>
            @endif

        </div>
    </div>

    @push('styles')
        <style>
            .bg-primary-soft {
                background-color: rgba(13, 110, 253, 0.1);
            }

            .bg-success-soft {
                background-color: rgba(25, 135, 84, 0.1);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkbox = document.getElementById('checkAceptoCondiciones');
                const btnPostularme = document.getElementById('btnPostularme');

                if (checkbox && btnPostularme) {
                    checkbox.addEventListener('change', function () {
                        if (this.checked) {
                            btnPostularme.classList.remove('disabled');
                        } else {
                            btnPostularme.classList.add('disabled');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection