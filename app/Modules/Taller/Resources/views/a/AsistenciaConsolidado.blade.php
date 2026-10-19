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

    <!-- Enlace Activo -->
    <div id="seccionTokenActivo" class="card border-0 shadow-sm mb-4 d-none" style="border-radius: 1rem; border-left: 4px solid #10b981;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-start">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:48px;height:48px;">
                        <i class="fas fa-link fa-lg text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Enlace de asistencia activo</h6>
                        <p class="text-muted small mb-1">
                            Actividad: <strong id="tokenActivoActividad"></strong>
                        </p>
                        <p class="text-muted small mb-1">
                            Expira: <strong id="tokenActivoExpira"></strong>
                            <span class="ms-2" id="tokenActivoCountdown"></span>
                        </p>
                        <div class="d-flex align-items-center mt-2">
                            <div class="input-group input-group-sm" style="max-width: 400px;">
                                <input type="text" class="form-control form-control-sm" id="tokenActivoUrl" readonly>
                                <button class="btn btn-outline-success" type="button" onclick="copiarEnlaceActivo()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold" onclick="compartirTelegramActivo()">
                        <i class="fab fa-telegram-plane me-1"></i> Telegram
                    </button>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="compartirWhatsAppActivo()">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="descargarQRActivo()">
                        <i class="fas fa-download me-1"></i> Descargar QR
                    </button>
                    <button class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" onclick="desactivarTokenActivo()">
                        <i class="fas fa-ban me-1"></i> Desactivar
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                        <p class="text-muted small">Expira: <strong id="resultadoExpira"></strong>
                            <span class="ms-2 badge bg-warning text-dark" id="resultadoCountdown"></span>
                        </p>
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
                        <button class="btn btn-info rounded-pill flex-grow-1 fw-bold text-white" onclick="compartirTelegramModal()">
                            <i class="fab fa-telegram-plane me-2"></i> Telegram
                        </button>
                        <button class="btn btn-outline-success rounded-pill flex-grow-1 fw-bold" onclick="compartirWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp
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
                            <i class="fas fa-plus me-2"></i> Generar nuevo enlace
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
    const rutaDesactivarToken = '{{ route('taller.asistencia.desactivar-token', $curso->crypt_id) }}';
    const rutaTokensActivos = '{{ route('taller.asistencia.tokens-activos', $curso->crypt_id) }}';
    const csrfToken = '{{ csrf_token() }}';

    // Múltiples countdowns independientes por elementoId
    let countdownIntervals = {};
    let tokensActivosData = [];

    // ─── Navegación del modal ───────────────────────────────────────────────
    function mostrarPaso(paso) {
        ['pasoSeleccion', 'pasoCargando', 'pasoResultado'].forEach(id => {
            document.getElementById(id).classList.add('d-none');
        });
        document.getElementById(paso).classList.remove('d-none');
    }

    function volverAPaso1() {
        mostrarPaso('pasoSeleccion');
        document.getElementById('selectActividad').value = '';
        detenerCountdown('resultadoCountdown');
    }

    // ─── Countdown ──────────────────────────────────────────────────────────
    function iniciarCountdown(timestampExpiracion, elementoId) {
        detenerCountdown(elementoId);
        const el = document.getElementById(elementoId);
        if (!el || !timestampExpiracion) return;

        function actualizar() {
            const ahora = Math.floor(Date.now() / 1000);
            const restante = timestampExpiracion - ahora;

            if (restante <= 0) {
                el.textContent = 'Expirado';
                el.className = el.className.replace(/bg-warning|bg-success/g, 'bg-danger');
                detenerCountdown(elementoId);
                return;
            }

            const min = Math.floor(restante / 60);
            const seg = restante % 60;
            el.textContent = min + ':' + String(seg).padStart(2, '0');
            el.className = el.className.replace(/bg-success/g, '');
            if (!el.classList.contains('bg-warning') && !el.classList.contains('bg-danger')) {
                el.classList.add('bg-warning', 'text-dark');
            }
        }

        actualizar();
        countdownIntervals[elementoId] = setInterval(actualizar, 1000);
    }

    function detenerCountdown(elementoId) {
        if (elementoId) {
            if (countdownIntervals[elementoId]) {
                clearInterval(countdownIntervals[elementoId]);
                delete countdownIntervals[elementoId];
            }
        } else {
            Object.keys(countdownIntervals).forEach(id => {
                clearInterval(countdownIntervals[id]);
            });
            countdownIntervals = {};
        }
    }

    // ─── Token Activo (sección superior) ────────────────────────────────────

    async function cargarTokenActivo() {
        try {
            const resp = await fetch(rutaTokensActivos, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();

            if (data.success) {
                tokensActivosData = data.tokens;
                tokensActivosData.sort((a, b) => b.expira_timestamp - a.expira_timestamp);

                if (tokensActivosData.length > 0) {
                    mostrarTokenActivo(tokensActivosData[0]);
                } else {
                    ocultarTokenActivo();
                }
            }
        } catch (e) { console.error('Error cargando tokens', e); }
    }

    function mostrarTokenActivo(data) {
        document.getElementById('tokenActivoActividad').textContent = data.actividad;
        document.getElementById('tokenActivoExpira').textContent = data.expira;
        document.getElementById('tokenActivoUrl').value = data.url;
        document.getElementById('seccionTokenActivo').classList.remove('d-none');
        document.getElementById('seccionTokenActivo').dataset.tokenId = data.token_id;
        document.getElementById('seccionTokenActivo').dataset.tokenQr = data.qr;
        iniciarCountdown(data.expira_timestamp, 'tokenActivoCountdown');
    }

    function ocultarTokenActivo() {
        document.getElementById('seccionTokenActivo').classList.add('d-none');
        detenerCountdown('tokenActivoCountdown');
    }

    // ─── Copiar texto (con fallback para contextos no-HTTPs) ────────────────

    async function copiarTexto(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(texto);
        } else {
            const ta = document.createElement('textarea');
            ta.value = texto;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        }
    }

    // ─── Copiar enlace (sección activa) ────────────────────────────────────

    async function copiarEnlaceActivo() {
        const url = document.getElementById('tokenActivoUrl').value;
        await copiarTexto(url);
        showToast('Enlace copiado al portapapeles');
    }

    async function compartirWhatsAppActivo() {
        const url = document.getElementById('tokenActivoUrl').value;
        const imgSrc = document.getElementById('seccionTokenActivo').dataset.tokenQr;
        await compartirWhatsAppQR(url, imgSrc);
    }

    function compartirTelegramActivo() {
        const url = document.getElementById('tokenActivoUrl').value;
        const imgSrc = document.getElementById('seccionTokenActivo').dataset.tokenQr;
        compartirTelegram(url, imgSrc);
    }

    async function descargarQRActivo() {
        const imgSrc = document.getElementById('seccionTokenActivo').dataset.tokenQr;
        if (!imgSrc) return;
        await descargarDesdeUrl(imgSrc, 'qr-asistencia.png');
    }

    // ─── Desactivar token ───────────────────────────────────────────────────
    async function desactivarTokenActivo() {
        const tokenId = document.getElementById('seccionTokenActivo').dataset.tokenId;
        if (!tokenId) return;

        if (!confirm('¿Desactivar este enlace? Los participantes ya no podrán marcar asistencia con él.')) return;

        try {
            const resp = await fetch(rutaDesactivarToken, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ token_id: parseInt(tokenId) })
            });
            const data = await resp.json();
            if (data.success) {
                showToast('Enlace desactivado correctamente');

                // Eliminar el token desactivado del array local
                tokensActivosData = tokensActivosData.filter(t => t.token_id != tokenId);

                if (tokensActivosData.length > 0) {
                    // Quedan más tokens activos: ordenar y mostrar el siguiente
                    tokensActivosData.sort((a, b) => b.expira_timestamp - a.expira_timestamp);
                    mostrarTokenActivo(tokensActivosData[0]);
                } else {
                    // No quedan tokens activos: ocultar la sección
                    ocultarTokenActivo();
                }
            }
        } catch (e) {
            alert('Error al desactivar el enlace');
        }
    }

    // ─── Toast notification ─────────────────────────────────────────────────

    function showToast(mensaje) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body fw-bold">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
            </div>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // ─── Copiar enlace (modal resultado) ────────────────────────────────────

    async function copiarEnlace() {
        const url = document.getElementById('resultadoUrl').value;
        await copiarTexto(url);
        const btn = document.getElementById('btnCopiarEnlace');
        btn.innerHTML = '<i class="fas fa-check me-1"></i> Copiado';
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copiar'; }, 2000);
    }

    // ─── WhatsApp / Telegram (modal resultado) ─────────────────────────────

    function compartirWhatsApp() {
        const url = document.getElementById('resultadoUrl').value;
        const imgSrc = document.getElementById('resultadoQR').src;
        compartirWhatsAppQR(url, imgSrc);
    }

    function compartirTelegramModal() {
        const url = document.getElementById('resultadoUrl').value;
        const imgSrc = document.getElementById('resultadoQR').src;
        compartirTelegram(url, imgSrc);
    }

    // ─── Compartir con archivo QR vía Web Share API ────────────────────────
    // Móvil: descarga el PNG, lo convierte en File y lo adjunta al menú nativo
    // de compartir (requiere HTTPS y llamarse por gesto del usuario).
    // Escritorio (sin navigator.share): cae al scheme de texto (wa.me / t.me).

    async function compartirConArchivo(url, imgSrc, texto, schemeFallback) {
        if (navigator.share && navigator.canShare) {
            try {
                const response = await fetch(imgSrc);
                const blob = await response.blob();
                const file = new File([blob], 'qr-asistencia.png', { type: 'image/png' });

                const shareData = { title: 'Asistencia - QR', text: texto, files: [file] };
                if (navigator.canShare(shareData)) {
                    await navigator.share(shareData);
                    return;
                }
            } catch (err) { /* continúa al fallback */ }
        }

        window.open(schemeFallback, '_blank');
    }

    // ─── WhatsApp ──────────────────────────────────────────────────────────
    // Móvil: menú nativo de compartir con el QR adjuntado.
    // Escritorio: solo el enlace vía wa.me.

    function compartirWhatsAppQR(url, imgSrc) {
        const texto = 'Marca tu asistencia aquí: ' + url;
        compartirConArchivo(url, imgSrc, texto, 'https://wa.me/?text=' + encodeURIComponent(texto));
    }

    // ─── Telegram ──────────────────────────────────────────────────────────
    // Móvil: menú nativo de compartir con el QR adjuntado.
    // Escritorio: t.me/share/url con el enlace.

    function compartirTelegram(url, imgSrc) {
        const texto = 'Marca tu asistencia aquí';
        compartirConArchivo(url, imgSrc, texto, 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(texto));
    }

    // ─── Descarga QR (corregido: fetch a blob) ─────────────────────────────

    async function descargarQR() {
        const imgSrc = document.getElementById('resultadoQR').src;
        await descargarDesdeUrl(imgSrc, 'qr-asistencia.png');
    }

    async function descargarDesdeUrl(url, filename) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(blobUrl);
        } catch (e) {
            // Fallback: abrir en nueva pestaña
            showToast('No se pudo descargar; se abrirá el QR en una pestaña');
            window.open(url, '_blank');
        }
    }

    // ─── Abrir enlace ───────────────────────────────────────────────────────

    function abrirEnlace() {
        const url = document.getElementById('resultadoUrl').value;
        window.open(url, '_blank');
    }

    // ─── Modal: abrir → verificar token activo ──────────────────────────────
    document.getElementById('modalGenerarToken').addEventListener('shown.bs.modal', function () {
        const select = document.getElementById('selectActividad');
        if (select.value) verificarTokenExistente(select.value);
    });

    document.getElementById('selectActividad').addEventListener('change', function () {
        if (this.value) verificarTokenExistente(this.value);
    });

    function verificarTokenExistente(contenidoId) {
        const tokenExistente = tokensActivosData.find(t => t.contenido_id == contenidoId);

        if (tokenExistente) {
            mostrarPaso('pasoResultado');
            document.getElementById('resultadoQR').src = tokenExistente.qr;
            document.getElementById('resultadoUrl').value = tokenExistente.url;
            document.getElementById('resultadoActividad').textContent = tokenExistente.actividad;
            document.getElementById('resultadoExpira').textContent = tokenExistente.expira;

            // Resetear y mostrar countdown del modal
            document.getElementById('resultadoCountdown').textContent = '';
            document.getElementById('resultadoCountdown').className = 'ms-2 badge bg-warning text-dark';
            document.getElementById('resultadoCountdown').classList.remove('d-none');
            document.getElementById('resultadoCountdown').dataset.timestamp = tokenExistente.expira_timestamp;
            iniciarCountdown(tokenExistente.expira_timestamp, 'resultadoCountdown');

            // NO tocar el banner — este siempre muestra el token más reciente del curso
        }
    }

    // ─── Modal: submit → generar token ──────────────────────────────────────

    document.getElementById('formGenerarToken').addEventListener('submit', function(e) {
        e.preventDefault();
        const select = document.getElementById('selectActividad');
        const contenidoId = select.value;
        if (!contenidoId) { alert('Selecciona una actividad'); return; }

        const duracion = this.querySelector('[name=duracion_minutos]').value;
        const optionText = select.options[select.selectedIndex].text;

        mostrarPaso('pasoCargando');

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
            if (data.success) {
                mostrarPaso('pasoResultado');
                document.getElementById('resultadoQR').src = data.qr;
                document.getElementById('resultadoUrl').value = data.url;
                document.getElementById('resultadoActividad').textContent = optionText;
                document.getElementById('resultadoExpira').textContent = data.expira;
                document.getElementById('resultadoCountdown').className = 'ms-2 badge bg-warning text-dark';
                document.getElementById('resultadoCountdown').classList.remove('d-none');
                document.getElementById('resultadoCountdown').dataset.timestamp = data.expira_timestamp || '';
                iniciarCountdown(data.expira_timestamp, 'resultadoCountdown');

                if (data.existente) {
                    showToast('Se reutilizó el enlace activo existente');
                }
                cargarTokenActivo();
            } else {
                mostrarPaso('pasoSeleccion');
                alert(data.message || 'Error al generar token');
            }
        })
        .catch(err => {
            mostrarPaso('pasoSeleccion');
            alert('Error de red al procesar la solicitud');
        });
    });

    // ─── Cargar token activo al iniciar ─────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        cargarTokenActivo();
    });
</script>
@endpush
@endsection
