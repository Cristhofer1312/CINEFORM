<div class="overflow-auto pe-2" style="max-height: 600px;">
    @if($items->count() > 0)
        @foreach($items as $insc)
            @php $p = $insc->persona; @endphp
            @if($p)
            <div class="participante-item d-flex align-items-center p-3 mb-3 rounded-4 border bg-white transition-all shadow-xs">
                <div class="me-4 flex-shrink-0">
                    {!! renderAvatar($p, 'avatar-md') !!}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h5 class="mb-1 fw-bold text-dark text-truncate">
                        {{ $p->primer_nombre }} {{ $p->primer_apellido }}
                    </h5>
                    <div class="d-flex align-items-center flex-wrap gap-3 mt-2">
                        <span class="badge bg-light text-secondary border">
                            <i class="fas fa-id-card me-1 opacity-50"></i> {{ $p->dni }}
                        </span>
                        <span class="badge bg-light text-secondary border">
                            <i class="far fa-calendar me-1 opacity-50"></i> 
                            {{ $tipo === 'postulado' ? 'Postulado el' : 'Inscrito el' }} {{ $insc->fecha_inscripcion ? $insc->fecha_inscripcion->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    
                    @if($insc->motivo_estado)
                        <div class="mt-2 p-2 bg-light rounded-3 border-start border-3 {{ $tipo === 'rechazado' ? 'border-warning' : 'border-danger' }} small">
                            <strong>Razón:</strong> {{ $insc->motivo_estado }}
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-column align-items-end gap-2 ms-3 flex-shrink-0">
                    @if($curso->requisitos->count() > 0)
                        <a href="{{ route('taller.inscripciones.respuestas', ['curso' => $curso->id_curso, 'inscripcion' => $insc->id_inscripcion]) }}" 
                           class="btn btn-outline-info btn-sm rounded-pill px-3 py-1 fw-bold w-100">
                            <i class="fas fa-file-alt me-1"></i> Ver Requisitos
                        </a>
                    @endif

                    @if($tipo === 'postulado')
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-1 fw-bold w-100"
                                onclick="updatePostulacion('{{ $insc->id_inscripcion }}', 'aprobar', '¿Aprobar Postulación?', 'El participante será inscrito formalmente.')">
                            <i class="fas fa-check me-1"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold w-100"
                                onclick="updatePostulacion('{{ $insc->id_inscripcion }}', 'rechazar', '¿Rechazar para Corrección?', 'Indique la razón para que el participante pueda corregir.', true)">
                            <i class="fas fa-exclamation-triangle me-1"></i> Rechazar
                        </button>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1 fw-bold w-100"
                                onclick="updatePostulacion('{{ $insc->id_inscripcion }}', 'denegar', '¿Denegar Definitivamente?', 'Esta acción no permitirá que el usuario se vuelva a postular.', true)">
                            <i class="fas fa-times-circle me-1"></i> Denegar
                        </button>
                    @elseif($tipo === 'rechazado')
                         <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1 fw-bold w-100"
                                onclick="updatePostulacion('{{ $insc->id_inscripcion }}', 'denegar', '¿Denegar Definitivamente?', 'Esta acción es irreversible.', true)">
                            <i class="fas fa-times-circle me-1"></i> Denegar Final
                        </button>
                    @endif
                </div>
            </div>
            @endif
        @endforeach
    @else
        <div class="text-center py-5 opacity-50">
            <i class="fas fa-folder-open fa-3x mb-3"></i>
            <p class="fw-bold">No hay registros en esta sección.</p>
        </div>
    @endif
</div>
