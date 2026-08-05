@extends('layouts.kaiadmin-menu')

@section('title', 'Panel de Certificación - ' . $curso->nombre)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-card overflow-hidden" style="border-radius: 1rem;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-success-soft text-success me-2 px-3 py-1 rounded-pill" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-clipboard-check me-1"></i> Certificación
                            </span>
                            <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">{{ $curso->nombre }}</small>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">Panel de Certificación</h3>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @php
                            $col = collect($resumenParticipantes);
                            $totalP = $col->count();
                            $aprobados = $col->filter(fn($item) => $item['inscripcion']->certificado_aprobado === true)->count();
                            $pendientes = $col->filter(fn($item) => $item['inscripcion']->certificado_aprobado === null)->count();
                            $denegados = $col->filter(fn($item) => $item['inscripcion']->certificado_aprobado === false)->count();
                        @endphp
                        <span class="badge bg-dark rounded-pill px-3 py-2"><i class="fas fa-users me-1"></i> {{ $totalP }} Participantes</span>
                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check me-1"></i> {{ $aprobados }} Aprobados</span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="fas fa-hourglass-half me-1"></i> {{ $pendientes }} Pendientes</span>
                        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="fas fa-times me-1"></i> {{ $denegados }} Denegados</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-outline-light text-dark border-0 bg-gray-100 hover-lift">
            <i class="fas fa-arrow-left me-2"></i> Volver al Curso
        </a>
    </div>

    <!-- Tabla de Participantes -->
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list-alt me-2 text-success"></i> Participantes ({{ $totalP }})</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Participante</th>
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Asistencia</th>
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Promedio</th>
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Estado</th>
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenParticipantes as $item)
                        @php
                            $insc = $item['inscripcion'];
                            $pct = $item['porcentajeAsistencia'];
                            $promedio = $item['promedio'];
                            $clasesAsistidas = $item['clasesAsistidas'];
                            $estadoCert = $insc->certificado_aprobado;
                        @endphp
                        <!-- Fila principal -->
                        <tr class="border-bottom cert-row" data-toggle="detail-{{ $insc->id_inscripcion }}" style="cursor:pointer;">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-box bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:36px;height:36px;font-weight:700;font-size:0.85rem;">
                                        {{ substr($insc->persona->primer_nombre ?? 'N', 0, 1) }}{{ substr($insc->persona->primer_apellido ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;">{{ $insc->persona->nombre_completo }}</h6>
                                        <small class="text-muted">C.I. {{ $insc->persona->dni ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fw-bold {{ $pct >= 75 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ $pct }}%
                                    </span>
                                    <div class="progress mt-1" style="width: 80px; height: 6px; border-radius: 3px;">
                                        <div class="progress-bar {{ $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                             style="width: {{ $pct }}%; border-radius: 3px;"></div>
                                    </div>
                                    <small class="text-muted mt-1" style="font-size:0.75rem;">{{ $item['asistencias'] }}/{{ $item['totalActividades'] }}</small>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                @if($promedio !== null)
                                    <span class="fw-bold {{ $promedio >= 75 ? 'text-success' : ($promedio >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ number_format($promedio, 2) }}/100
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                @if($estadoCert === true)
                                    <span class="badge bg-success rounded-pill px-3 py-1"><i class="fas fa-check me-1"></i> Aprobado</span>
                                @elseif($estadoCert === false)
                                    <span class="badge bg-danger rounded-pill px-3 py-1"><i class="fas fa-times me-1"></i> Denegado</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fas fa-hourglass-half me-1"></i> Pendiente</span>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    {{-- Aprobar: disponible cuando NO está aprobado (pendiente o denegado) --}}
                                    @if($estadoCert !== true)
                                        <form action="{{ route('taller.certificacion.aprobar', ['curso' => $curso->crypt_id, 'inscripcion' => $insc->crypt_id]) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirmarAprobar('{{ addslashes($insc->persona->nombre_completo) }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Denegar: disponible cuando NO está denegado (pendiente o aprobado) --}}
                                    @if($estadoCert !== false)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Denegar" 
                                                onclick="abrirModalDenegar('{{ $curso->crypt_id }}', '{{ $insc->crypt_id }}', '{{ addslashes($insc->persona->nombre_completo) }}')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info rounded-pill px-2" title="Ver detalle" onclick="toggleDetail({{ $insc->id_inscripcion }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Fila expandible: Detalle -->
                        <tr class="detail-row" id="detail-{{ $insc->id_inscripcion }}" style="display:none;">
                            <td colspan="5" class="p-0">
                                <div class="p-4 bg-light border-top">
                                    <div class="row">
                                        <!-- Clases -->
                                        <div class="col-md-7">
                                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-check me-2 text-primary"></i> Clases ({{ $actividades->count() }})</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tbody>
                                                        @foreach($actividades as $actividad)
                                                        <tr>
                                                            <td class="py-1 px-2" style="font-size:0.85rem;">
                                                                {{ $actividad->fecha_contenido ? \Carbon\Carbon::parse($actividad->fecha_contenido)->format('d/m/Y') : 'S/F' }}
                                                                — {{ $actividad->titulo }}
                                                            </td>
                                                            <td class="py-1 px-2 text-center" style="width:50px;">
                                                                @if($clasesAsistidas->contains($actividad->id_contenido_curso))
                                                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                                                @else
                                                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-times"></i></span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <p class="mt-2 mb-0 fw-bold small">
                                                Asistencia: {{ $item['asistencias'] }}/{{ $item['totalActividades'] }} = 
                                                <span class="{{ $pct >= 75 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</span>
                                            </p>
                                        </div>
                                        <!-- Evaluaciones -->
                                        <div class="col-md-5">
                                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-star me-2 text-warning"></i> Evaluaciones</h6>
                                            @if($evaluaciones->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tbody>
                                                            @php
                                                                $califsRaw = DB::table('taller.calificaciones')
                                                                    ->where('id_curso', $curso->id_curso)
                                                                    ->where('id_persona', $insc->id_persona)
                                                                    ->get();
                                                            @endphp
                                                            @foreach($evaluaciones as $eval)
                                                            @php
                                                                $calif = $califsRaw->firstWhere('id_contenido_curso', $eval->id_contenido_curso);
                                                            @endphp
                                                            <tr>
                                                                <td class="py-1 px-2" style="font-size:0.85rem;">{{ $eval->titulo }}</td>
                                                                <td class="py-1 px-2 text-end fw-bold" style="width:80px;">
                                                                    {{ $calif ? $calif->calificacion : 'N/A' }}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class="mt-2 mb-0 fw-bold small">
                                                    Promedio: 
                                                    @if($promedio !== null)
                                                        <span class="{{ $promedio >= 75 ? 'text-success' : ($promedio >= 50 ? 'text-warning' : 'text-danger') }}">
                                                            {{ number_format($promedio, 2) }}/100
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-muted small mb-0">No hay evaluaciones registradas.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-2x mb-3 d-block opacity-25"></i>
                                No hay participantes aprobados en este curso.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Denegación -->
<div class="modal fade" id="modalDenegar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-times-circle me-2"></i> Denegar Certificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDenegar" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Indica el motivo por el cual se deniega la certificación de <strong id="denegarNombre"></strong>:</p>
                    <textarea name="motivo" class="form-control" rows="3" required maxlength="500" placeholder="Motivo de denegación..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-bold px-4"><i class="fas fa-times me-1"></i> Denegar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleDetail(id) {
        const row = document.getElementById('detail-' + id);
        row.style.display = row.style.display === 'none' ? '' : 'none';
    }

    document.querySelectorAll('.cert-row').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('.btn') || e.target.closest('form')) return;
            const id = this.getAttribute('data-toggle');
            toggleDetail(id.replace('detail-', ''));
        });
    });

    function confirmarAprobar(nombre) {
        return confirm('¿Aprobar la certificación de ' + nombre + '?');
    }

    function abrirModalDenegar(cursoCrypt, inscripcionCrypt, nombre) {
        document.getElementById('denegarNombre').textContent = nombre;
        document.getElementById('formDenegar').action = '/taller/cursos/' + cursoCrypt + '/certificacion/' + inscripcionCrypt + '/denegar';
        new bootstrap.Modal(document.getElementById('modalDenegar')).show();
    }
</script>
@endpush
