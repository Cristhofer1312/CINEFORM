@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Programas</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Paso 2: Logística</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">¿Cuándo, dónde y con quién?</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-calendar-alt me-1 text-primary"></i> Facilitador, calendario, cupos y sede.</p>
                </div>
                <div class="text-end d-flex gap-2">
                    <a href="{{ route('taller.cursos.wizard.paso1.create') }}" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Atrás
                    </a>
                    <a href="{{ route('taller.cursos.index') }}" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        Guardar borrador y salir
                    </a>
                </div>
            </div>

            <x-taller.wizard-steps :actual="$pasoActual ?? 2" :completados="$completados ?? []" />

            <form action="{{ route('taller.cursos.wizard.paso2.store') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <x-taller.alert type="danger" title="Errores de validación">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-taller.alert>
                @endif

                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <x-taller.card-header icon="fas fa-calendar-alt" title="Logística, fechas y sede" />
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-tie me-2 opacity-50 text-primary"></i> FACILITADOR RESPONSABLE <span class="text-danger ms-1">*</span>
                                </label>
                                <div class="bg-white border-2 rounded-3 p-2 px-3 shadow-xs border-light-2 d-flex align-items-center" style="height: 58px;">
                                    @php
                                        $facilitadorSel = $facilitadorSel ?? null;
                                        $facNombre = $facilitadorSel ? trim(($facilitadorSel->primer_nombre ?? '').' '.($facilitadorSel->primer_apellido ?? '')) : null;
                                    @endphp
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" id="selected-facilitator-name">
                                            {{ $facNombre ?: 'No asignado' }}
                                        </h6>
                                        <small class="text-muted d-block" id="selected-facilitator-doc">
                                            {{ $facilitadorSel ? 'C.I: '.$facilitadorSel->dni : 'Haga clic para seleccionar' }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0 ms-2" data-bs-toggle="modal" data-bs-target="#modalFacilitadores">
                                        <i class="fas fa-search small"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="id_persona" id="id_persona" value="{{ old('id_persona', $paso2['id_persona'] ?? '') }}" required>
                            </div>

                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check form-switch p-3 bg-light rounded-4 border shadow-xs d-inline-flex align-items-center w-100" style="height: 58px;">
                                    <input type="hidden" name="es_nacional_present" value="1">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="es_nacional" name="es_nacional" value="1" {{ old('es_nacional', $paso2['es_nacional'] ?? false) ? 'checked' : '' }} onchange="toggleLocalidades(this)">
                                    <label class="form-check-label fw-bold text-dark mb-0 text-truncate" for="es_nacional">
                                        <i class="fas fa-globe-americas me-1 text-primary"></i> ALCANCE NACIONAL
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <x-taller.input label="INICIO" icon="far fa-calendar-check" name="fecha_inicio"
                                    :value="old('fecha_inicio', $paso2['fecha_inicio'] ?? '')" type="date" required />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input label="FINALIZACIÓN" icon="far fa-calendar-minus" name="fecha_fin"
                                    :value="old('fecha_fin', $paso2['fecha_fin'] ?? '')" type="date" required />
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-calendar-minus me-2 opacity-50 text-primary"></i> TRIMESTRE (AUTO)
                                </label>
                                <div class="form-control border-2 text-center fw-bold border-light-2 rounded-3 bg-light" style="height: 58px; line-height: 42px;">
                                    <span id="trimestre-auto">{{ $trimestreSugerido ? $trimestreSugerido . '°' : '—' }}</span>
                                </div>
                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i> Se calcula desde la fecha de inicio.</small>
                            </div>

                            <div class="col-md-4">
                                <x-taller.input label="AÑO" icon="fas fa-calendar-check" name="anio"
                                    :value="old('anio', $paso2['anio'] ?? date('Y'))" type="number" />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input label="CORRELATIVO" icon="fas fa-hashtag" name="correlativo"
                                    :value="old('correlativo', $paso2['correlativo'] ?? $proximoCorrelativo)" type="number" required />
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-barcode me-2 opacity-50 text-primary"></i> CÓDIGO (VISTA PREVIA)
                                </label>
                                <div class="form-control border-2 text-center fw-bold border-light-2 rounded-3 bg-light font-monospace" style="height: 58px; line-height: 42px;" id="codigo-preview">{{ $codigoPreview }}</div>
                            </div>

                            <div class="col-md-4">
                                <x-taller.input label="CUPO MÁXIMO" icon="fas fa-user-plus" name="cantidad_cupos"
                                    :value="old('cantidad_cupos', $paso2['cantidad_cupos'] ?? '')" type="number" min="1" placeholder="Ej: 25" required />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input label="DURACIÓN EN DÍAS" icon="fas fa-calendar-week" name="duracion"
                                    :value="old('duracion', $paso2['duracion'] ?? '')" type="number" placeholder="0" class="text-center" />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input label="TOTAL HORAS ACADÉMICAS" icon="fas fa-clock" name="horas"
                                    :value="old('horas', $paso2['horas'] ?? '')" type="number" placeholder="0" class="text-center" />
                            </div>

                            <div class="col-md-12">
                                <x-taller.input label="CANAL DE TELEGRAM (OPCIONAL)" icon="fab fa-telegram-plane" name="telegram"
                                    :value="old('telegram', $paso2['telegram'] ?? '')" placeholder="https://t.me/nombre_del_canal" />
                            </div>
                            <div class="col-md-12">
                                <x-taller.input label="DIRECCIÓN CORTA" icon="fas fa-map-pin" name="direccion_corta"
                                    :value="old('direccion_corta', $paso2['direccion_corta'] ?? '')" placeholder="Ej: Calle 15, Edificio Colgate piso 3" required />
                            </div>

                            <div class="col-md-12" id="container-localidades" style="{{ old('es_nacional', $paso2['es_nacional'] ?? false) ? 'display:none;' : '' }}">
                                <div class="card border-light-2 shadow-xs rounded-4 overflow-hidden mb-0">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i> ESTADOS / LOCALIDADES PERMITIDAS
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 overflow-auto custom-scrollbar" id="localidades-grid" style="max-height: min(220px, 40vh);">
                                            @php $locSel = old('localidades', $paso2['localidades'] ?? []); @endphp
                                            @foreach($regiones as $reg)
                                                <div class="col localidad-item" data-nombre="{{ strtolower($reg->description) }}">
                                                    <div class="form-check p-0 m-0">
                                                        <input type="checkbox" class="btn-check localidad-checkbox" name="localidades[]"
                                                            id="loc_{{ $reg->id }}" value="{{ $reg->id }}"
                                                            {{ is_array($locSel) && in_array($reg->id, $locSel) ? 'checked' : '' }} autocomplete="off">
                                                        <label class="btn btn-outline-primary btn-sm w-100 rounded-3 py-2 text-truncate shadow-xs fw-bold" for="loc_{{ $reg->id }}">
                                                            {{ $reg->description }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-end flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg">
                                    Guardar y continuar <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalFacilitadores" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-search me-2"></i> Asignar Facilitador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    <div class="input-group shadow-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0 py-2" id="filtro-facilitador"
                            placeholder="Nombre o cédula..." autocomplete="off">
                    </div>
                    <div id="facilitator-container" class="bg-white rounded-4 border overflow-auto shadow-sm" style="max-height: min(400px, 50vh);"
                        data-hay-mas="{{ $facilitadoresIni->hasMorePages() ? '1' : '' }}">
                        @forelse($facilitadoresIni as $facilitador)
                            @php $p = $facilitador->personalData; @endphp
                            @if($p)
                            <div class="facilitator-item p-3 border-bottom" style="cursor:pointer;"
                                data-id-persona="{{ $p->id_persona }}"
                                data-nombre="{{ trim(($p->primer_nombre ?? '').' '.($p->primer_apellido ?? '')) }}"
                                data-dni="{{ $p->dni }}">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $p->primer_nombre }} {{ $p->primer_apellido }}</h6>
                                        <small class="text-muted d-block">C.I: {{ $p->dni }}</small>
                                        @if($p->especializaciones->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach($p->especializaciones->take(3) as $esp)
                                                <span class="badge bg-light text-primary border me-1" style="font-size:.7rem;">{{ $esp->nombre }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="p-4 text-center text-muted small">Sin facilitadores disponibles.</div>
                        @endforelse
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" id="facilitator-more" style="display:none;">
                            Cargar más
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold w-100 py-3" data-bs-dismiss="modal">
                        Confirmar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/modules/taller/wizard-paso2.js') }}"></script>
    <script>
        const FACILITADORES_URL = "{{ route('taller.cursos.wizard.facilitadores.buscar') }}";
        function toggleLocalidades(el) { WizardPaso2.toggleLocalidades(el); }
        function selectFacilitator(id, name, doc) {
            document.getElementById('id_persona').value = id;
            document.getElementById('selected-facilitator-name').textContent = name;
            document.getElementById('selected-facilitator-doc').textContent = 'C.I: ' + doc;
            document.querySelectorAll('#facilitator-container .facilitator-item').forEach(function (row) {
                row.classList.toggle('bg-light', row.dataset.idPersona === String(id));
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            WizardPaso2.initFacilitadores(FACILITADORES_URL, document.getElementById('id_persona').value || null);
        });
    </script>
    @endpush
@endsection
