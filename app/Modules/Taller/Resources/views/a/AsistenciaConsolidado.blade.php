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
<div class="modal fade" id="modalGenerarToken" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header bg-primary text-white" style="border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-qrcode me-2"></i> Generar Enlace de Asistencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Paso 1: Seleccionar actividad -->
                <div id="pasoSeleccion">
                    <form id="formGenerarToken">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Actividad:</label>
                            <select name="contenido_id" id="selectActividad" class="form-select" required>
                                <option value="">Seleccionar actividad...</option>
                                @foreach($actividades as $act)
                                <option value="{{ $act->id_contenido_curso }}">
                                    {{ $act->titulo }} ({{ $act->fecha_contenido ? $act->fecha_contenido->format('d/m/Y') : 'Sin fecha' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Duración del enlace (minutos):</label>
                            <input type="number" name="duracion_minutos" class="form-control" value="30" min="5" max="480">
                            <small class="text-muted">El enlace expirará después de este tiempo.</small>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGenerar">
                                <i class="fas fa-bolt me-2"></i> Generar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Paso 2: Mostrar QR + Link -->
                <div id="pasoResultado" class="d-none">
                    <div class="text-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Enlace Generado</h5>
                        <p class="text-muted small mb-0">Actividad: <strong id="resultadoActividad"></strong></p>
                        <p class="text-muted small">Expira: <strong id="resultadoExpira"></strong></p>
                    </div>

                    <!-- QR Code -->
                    <div class="text-center mb-4">
                        <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm border">
                            <img id="resultadoQR" src="" alt="QR de asistencia" class="img-fluid" style="max-width: 220px;">
                        </div>
                    </div>

                    <!-- Link para copiar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Enlace para compartir:</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="resultadoUrl" readonly>
                            <button class="btn btn-outline-primary" type="button" id="btnCopiarEnlace" onclick="copiarEnlace()">
                                <i class="fas fa-copy me-1"></i> Copiar
                            </button>
                        </div>
                    </div>

                    <!-- Botones de compartir -->
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <button class="btn btn-success rounded-pill flex-grow-1 fw-bold" onclick="compartirConQR()">
                            <i class="fas fa-share-alt me-2"></i> Compartir enlace + QR
                        </button>
                        <button class="btn btn-outline-primary rounded-pill flex-grow-1 fw-bold" onclick="descargarQR()">
                            <i class="fas fa-download me-2"></i> Descargar QR
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill flex-grow-1 fw-bold" onclick="abrirEnlace()">
                            <i class="fas fa-external-link-alt me-2"></i> Probar enlace
                        </button>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold ms-2" onclick="volverAPaso1()">
                            <i class="fas fa-plus me-2"></i> Generar otro
                        </button>
                    </div>
                </div>

                <!-- Loading -->
                <div id="pasoCargando" class="d-none text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted fw-bold">Generando enlace...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const rutaGenerarToken = '{{ route('taller.asistencia.generar-token', $curso->crypt_id) }}';
    const csrfToken = '{{ csrf_token() }}';

    function volverAPaso1() {
        document.getElementById('pasoResultado').classList.add('d-none');
        document.getElementById('pasoSeleccion').classList.remove('d-none');
        document.getElementById('selectActividad').value = '';
    }

    function copiarEnlace() {
        const url = document.getElementById('resultadoUrl').value;
        navigator.clipboard.writeText(url).then(() => {
            const btn = document.getElementById('btnCopiarEnlace');
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copiado';
            setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copiar'; }, 2000);
        });
    }

    function compartirWhatsApp() {
        const url = document.getElementById('resultadoUrl').value;
        const texto = 'Marca tu asistencia aquí: ' + url;
        window.open('https://wa.me/?text=' + encodeURIComponent(texto), '_blank');
    }

    async function compartirConQR() {
        const url = document.getElementById('resultadoUrl').value;
        const img = document.getElementById('resultadoQR');
        const texto = 'Marca tu asistencia aquí: ' + url;

        // Intentar Web Share API (funciona en móviles con WhatsApp instalado)
        if (navigator.share && navigator.canShare) {
            try {
                const response = await fetch(img.src);
                const blob = await response.blob();
                const file = new File([blob], 'qr-asistencia.png', { type: 'image/png' });

                const shareData = {
                    title: 'Asistencia - QR',
                    text: texto,
                    files: [file]
                };

                if (navigator.canShare(shareData)) {
                    await navigator.share(shareData);
                    return;
                }
            } catch (err) {
                // Si falla, continuar con fallback
            }
        }

        // Fallback: copiar enlace + descargar QR para que el usuario lo envíe manualmente
        await navigator.clipboard.writeText(texto);
        descargarQR();
        alert('Enlace copiado y QR descargado. Pega el enlace en WhatsApp y adjunta la imagen del QR.');
    }

    function descargarQR() {
        const img = document.getElementById('resultadoQR');
        const a = document.createElement('a');
        a.href = img.src;
        a.download = 'qr-asistencia.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function abrirEnlace() {
        const url = document.getElementById('resultadoUrl').value;
        window.open(url, '_blank');
    }

    document.getElementById('formGenerarToken').addEventListener('submit', function(e) {
        e.preventDefault();
        const select = document.getElementById('selectActividad');
        const contenidoId = select.value;
        if (!contenidoId) { alert('Selecciona una actividad'); return; }

        const duracion = this.querySelector('[name=duracion_minutos]').value;
        const optionText = select.options[select.selectedIndex].text;

        // Mostrar loading
        document.getElementById('pasoSeleccion').classList.add('d-none');
        document.getElementById('pasoCargando').classList.remove('d-none');

        fetch(rutaGenerarToken, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                contenido_id: parseInt(contenidoId),
                duracion_minutos: parseInt(duracion)
            }),
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('pasoCargando').classList.add('d-none');

            if (data.success) {
                // Llenar resultado
                document.getElementById('resultadoQR').src = data.qr;
                document.getElementById('resultadoUrl').value = data.url;
                document.getElementById('resultadoActividad').textContent = optionText;
                document.getElementById('resultadoExpira').textContent = data.expira;

                // Mostrar paso 2
                document.getElementById('pasoResultado').classList.remove('d-none');
            } else {
                document.getElementById('pasoSeleccion').classList.remove('d-none');
                alert(data.message || 'Error al generar token');
            }
        })
        .catch(err => {
            document.getElementById('pasoCargando').classList.add('d-none');
            document.getElementById('pasoSeleccion').classList.remove('d-none');
            alert('Error de red al procesar la solicitud');
        });
    });
</script>
@endpush
@endsection
