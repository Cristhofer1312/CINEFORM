@extends('layouts.kaiadmin-menu')

@section('title', 'Asistencia - ' . $curso->nombre)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-card overflow-hidden" style="border-radius: 1rem;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-primary-soft text-primary me-2 px-3 py-1 rounded-pill" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-user-check me-1"></i> Asistencia
                            </span>
                            <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">{{ $curso->nombre }}</small>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">Lista Consolidada de Asistencia</h3>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarToken">
                            <i class="fas fa-qrcode me-2"></i> Generar Enlace / QR
                        </button>
                        <a href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}" class="btn btn-outline-light text-dark border-0 bg-gray-100 hover-lift">
                            <i class="fas fa-arrow-left me-2"></i> Volver al contenido
                        </a>
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

    @if(session('token_url'))
    <div class="card border-success mb-4 shadow-sm" style="border-radius: 1rem;">
        <div class="card-body p-4">
            <h5 class="fw-bold text-success mb-3"><i class="fas fa-link me-2"></i> Enlace de Asistencia Generado</h5>
            <div class="row g-4 align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Enlace para compartir:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="tokenUrl" value="{{ session('token_url') }}" readonly>
                        <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById('tokenUrl').value); this.innerHTML='<i class=\'fas fa-check\'></i> Copiado'; setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i> Copiar',1500);">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                    <small class="text-muted">Expira: {{ session('token_expira') }}</small>
                </div>
                <div class="col-md-3 text-center">
                    @if(session('token_qr'))
                        <img src="{{ session('token_qr') }}" alt="QR Asistencia" class="img-fluid rounded shadow-sm" style="max-width: 180px;">
                    @endif
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ session('token_url') }}" class="btn btn-success rounded-pill px-4 fw-bold" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i> Probar enlace
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabla consolidada -->
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list-alt me-2 text-primary"></i> Participantes ({{ $curso->inscripciones->count() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Participante</th>
                            @foreach($actividades as $actividad)
                            <th class="text-center py-3 fw-bold text-muted small" style="font-size:0.75rem; min-width: 70px;">
                                {{ $actividad->fecha_contenido ? \Carbon\Carbon::parse($actividad->fecha_contenido)->format('d/m') : 'S/F' }}<br>
                                <span class="text-primary">{{ substr($actividad->titulo, 0, 10) }}{{ strlen($actividad->titulo) > 10 ? '...' : '' }}</span>
                            </th>
                            @endforeach
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Asistencia</th>
                            <th class="text-center py-3 fw-bold text-muted small text-uppercase" style="letter-spacing: 1px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($curso->inscripciones as $inscripcion)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-box bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:36px;height:36px;font-weight:700;font-size:0.85rem;">
                                        {{ substr($inscripcion->persona->primer_nombre ?? 'N', 0, 1) }}{{ substr($inscripcion->persona->primer_apellido ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;">{{ $inscripcion->persona->primer_nombre ?? '' }} {{ $inscripcion->persona->primer_apellido ?? '' }}</h6>
                                        <small class="text-muted">C.I. {{ $inscripcion->persona->dni ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            @foreach($actividades as $actividad)
                            <td class="text-center py-3">
                                @php
                                    $key = $actividad->id_contenido_curso . '_' . $inscripcion->id_persona;
                                    $asistio = isset($asistenciasMap[$key]);
                                @endphp
                                @if($asistio)
                                    <span class="badge bg-success rounded-pill px-2 py-1" title="Asistió el {{ $asistio['fecha_hora_marcado'] ?? '' }}">
                                        <i class="fas fa-check"></i>
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2 py-1">
                                        <i class="fas fa-times"></i>
                                    </span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center py-3">
                                @php
                                    $totalAct = $actividades->count();
                                    $totalAsist = 0;
                                    foreach($actividades as $act) {
                                        $k = $act->id_contenido_curso . '_' . $inscripcion->id_persona;
                                        if(isset($asistenciasMap[$k])) $totalAsist++;
                                    }
                                    $pct = $totalAct > 0 ? round(($totalAsist / $totalAct) * 100) : 0;
                                @endphp
                                <span class="fw-bold {{ $pct >= 80 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ $pct }}%
                                </span>
                                <small class="text-muted d-block">({{ $totalAsist }}/{{ $totalAct }})</small>
                            </td>
                            <td class="text-center py-3">
                                <a href="{{ route('taller.asistencia.individual', ['curso' => $curso->crypt_id, 'inscripcion' => $inscripcion->crypt_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Ver detalle">
                                    <i class="fas fa-eye me-1"></i> Detalle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $actividades->count() + 3 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash mb-3 opacity-25" style="font-size: 3rem; display: block;"></i>
                                No hay participantes inscritos en este curso.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generar Token -->
<div class="modal fade" id="modalGenerarToken" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 1rem;">
            <form action="#" method="POST" id="formGenerarToken">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-qrcode me-2"></i> Generar Enlace de Asistencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actividad:</label>
                        <select name="contenido_id" id="selectActividad" class="form-select rounded-pill" required>
                            <option value="">Seleccionar actividad...</option>
                            @foreach($actividades as $act)
                            <option value="{{ $act->id_contenido_curso }}">
                                {{ $act->titulo }} ({{ $act->fecha_contenido ? $act->fecha_contenido->format('d/m/Y') : 'Sin fecha' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duración del enlace (minutos):</label>
                        <input type="number" name="duracion_minutos" class="form-control rounded-pill" value="30" min="5" max="480">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-bolt me-2"></i> Generar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('formGenerarToken').addEventListener('submit', function(e) {
        e.preventDefault();
        const select = document.getElementById('selectActividad');
        const contenidoId = select.value;
        if (!contenidoId) { alert('Selecciona una actividad'); return; }

        const duracion = this.querySelector('[name=duracion_minutos]').value;
        const form = this;

        fetch(`{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}`.replace('/contenido/', '/contenido/' + contenidoId + '/generar-token'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ duracion_minutos: duracion }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar el token generado
                location.reload();
            } else {
                alert(data.message || 'Error al generar token');
            }
        })
        .catch(err => alert('Error de red'));
    });
</script>
@endpush
@endsection
