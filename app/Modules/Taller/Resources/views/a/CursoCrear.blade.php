@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas -->
            @if(session('success'))
                <x-taller.alert type="success" title="¡Creado!">
                    {{ session('success') }}
                </x-taller.alert>
            @endif

            <!-- Encabezado de Creación -->
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Nuevo Programa</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Crear Nuevo Curso</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-magic me-1 text-primary"></i> Estás configurando una nueva oferta académica para el sistema.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.index') }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Cancelar
                    </a>
                </div>
            </div>

            <form action="{{ route('taller.cursos.store_new') }}" method="POST">
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

                <!-- Panel de Control Principal -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <x-taller.card-header 
                        icon="fas fa-plus" 
                        title="Información de Cabecera" 
                    />
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <!-- Nombre y Código -->
                            <div class="col-md-8">
                                <x-taller.input 
                                    label="NOMBRE DEL CURSO" 
                                    icon="fas fa-heading" 
                                    name="nombre" 
                                    :value="old('nombre')" 
                                    placeholder="Ej: Especialización en Guion Cinematográfico" 
                                    required 
                                />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input 
                                    label="CÓDIGO PROCINEC" 
                                    icon="fas fa-barcode" 
                                    name="codigo" 
                                    :value="old('codigo')" 
                                    placeholder="LAB-TA..." 
                                />
                            </div>

                            <div class="col-md-12">
                                <x-taller.input 
                                    label="CANAL DE TELEGRAM (OPCIONAL)" 
                                    icon="fab fa-telegram-plane" 
                                    name="telegram" 
                                    :value="old('telegram')" 
                                    placeholder="https://t.me/nombre_del_canal" 
                                />
                            </div>

                            <!-- Facilitador Responsable -->
                            <div class="col-md-7">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-tie me-2 opacity-50 text-primary"></i> FACILITADOR RESPONSABLE <span class="text-danger ms-1">*</span>
                                </label>
                                <div class="bg-white border-2 rounded-3 p-2 px-3 shadow-xs border-light-2 d-flex align-items-center" style="height: 58px;">
                                    <div class="avatar-sm me-3">
                                        <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-chalkboard-teacher small"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" id="selected-facilitator-name">
                                            @php
                                                $facilitadorOld = old('id_persona') ? $facilitadores->first(fn($f) => $f->personalData->id_persona == old('id_persona')) : null;
                                            @endphp
                                            {{ $facilitadorOld ? ($facilitadorOld->personalData->primer_nombre . ' ' . $facilitadorOld->personalData->primer_apellido) : 'No asignado' }}
                                        </h6>
                                        <small class="text-muted d-block" id="selected-facilitator-doc">
                                            {{ $facilitadorOld ? 'C.I: ' . $facilitadorOld->personalData->dni : 'Haga clic para seleccionar' }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0 ms-2" data-bs-toggle="modal" data-bs-target="#modalFacilitadores">
                                        <i class="fas fa-search small"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="id_persona" id="id_persona" value="{{ old('id_persona') }}" required>
                            </div>

                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check form-switch p-3 bg-light rounded-4 border shadow-xs d-inline-flex align-items-center w-100" style="height: 58px;">
                                    <input type="hidden" name="es_nacional_present" value="1">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="es_nacional" name="es_nacional" value="1" {{ old('es_nacional') ? 'checked' : '' }} onchange="TallerModule.toggleLocalidades(this)">
                                    <label class="form-check-label fw-bold text-dark mb-0 text-truncate" for="es_nacional">
                                        <i class="fas fa-globe-americas me-1 text-primary"></i> ALCANCE NACIONAL
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12" id="container-localidades" style="{{ old('es_nacional') ? 'display:none;' : '' }}">
                                <div class="card border-light-2 shadow-xs rounded-4 overflow-hidden mb-0">
                                    <div class="card-header bg-light border-0 py-3 d-flex align-items-center justify-content-between">
                                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i> ESTADOS / LOCALIDADES PERMITIDAS
                                        </h6>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-xs btn-white border shadow-xs rounded-pill px-3 py-1" onclick="TallerModule.selectAllLocalidades(false)" style="font-size: 0.7rem;">
                                                <i class="fas fa-times me-1 text-danger"></i> NINGUNO
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="input-group mb-3 shadow-sm border rounded-pill overflow-hidden bg-white">
                                            <span class="input-group-text bg-white border-0 ps-3">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-0 py-2 shadow-none" id="search-localidad" placeholder="Filtrar por nombre de estado..." onkeyup="TallerModule.filterLocalidades()">
                                        </div>
                                        
                                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 overflow-auto custom-scrollbar" id="localidades-grid" style="max-height: 220px;">
                                            @foreach($regiones as $reg)
                                                <div class="col localidad-item" data-nombre="{{ strtolower($reg->description) }}">
                                                    <div class="form-check p-0 m-0">
                                                        <input type="checkbox" 
                                                               class="btn-check localidad-checkbox" 
                                                               name="localidades[]" 
                                                               id="loc_{{ $reg->id }}" 
                                                               value="{{ $reg->id }}"
                                                               {{ is_array(old('localidades')) && in_array($reg->id, old('localidades')) ? 'checked' : '' }}
                                                               autocomplete="off">
                                                        <label class="btn btn-outline-primary btn-sm w-100 rounded-3 py-2 text-truncate shadow-xs fw-bold" for="loc_{{ $reg->id }}">
                                                            {{ $reg->description }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> El curso solo será visible para los estados seleccionados.</small>
                            </div>

                            <!-- Clasificación Académica -->
                            <div class="col-md-4">
                                <x-taller.select 
                                    label="ACTIVIDAD FORMATIVA" 
                                    icon="fas fa-graduation-cap" 
                                    name="id_actividad_formativa"
                                    onchange="generateProcinecCode()"
                                >
                                    @foreach($actividades as $act)
                                        <option value="{{ $act->id_actividad_formativa }}" data-abreviatura="{{ $act->abreviatura }}" {{ old('id_actividad_formativa') == $act->id_actividad_formativa ? 'selected' : '' }}>
                                            {{ $act->nombre }} ({{ $act->abreviatura }})
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-4">
                                <x-taller.select 
                                    label="ASPECTO CINEMATOGRÁFICO" 
                                    icon="fas fa-film" 
                                    name="id_aspecto"
                                    onchange="generateProcinecCode()"
                                >
                                    @foreach($aspectos as $asp)
                                        <option value="{{ $asp->id_aspecto }}" data-abreviatura="{{ $asp->abreviatura }}" {{ old('id_aspecto') == $asp->id_aspecto ? 'selected' : '' }}>
                                            {{ $asp->nombre }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-4">
                                <x-taller.select 
                                    label="PÚBLICO OBJETIVO" 
                                    icon="fas fa-users" 
                                    name="id_modalidad_especial"
                                    onchange="generateProcinecCode()"
                                >
                                    @foreach($modalidadesEspeciales as $me)
                                        <option value="{{ $me->id_modalidad_especial }}" data-abreviatura="{{ $me->abreviatura }}" {{ old('id_modalidad_especial') == $me->id_modalidad_especial ? 'selected' : '' }}>
                                            {{ $me->nombre }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>

                            <!-- Parámetros y Nivel -->
                            <div class="col-md-3">
                                <x-taller.select 
                                    label="MODALIDAD" 
                                    icon="fas fa-laptop-house" 
                                    name="id_modalidad"
                                    required
                                    onchange="generateProcinecCode()"
                                >
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id_modalidad }}" data-abreviatura="{{ $mod->abreviatura }}" {{ old('id_modalidad') == $mod->id_modalidad ? 'selected' : '' }}>
                                            {{ $mod->nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-3">
                                <x-taller.select 
                                    label="NIVEL" 
                                    icon="fas fa-layer-group" 
                                    name="nivel"
                                >
                                    <option value="Básico" {{ old('nivel') == 'Básico' ? 'selected' : '' }}>Básico</option>
                                    <option value="Medio" {{ old('nivel') == 'Medio' ? 'selected' : '' }}>Medio</option>
                                    <option value="Avanzado" {{ old('nivel') == 'Avanzado' ? 'selected' : '' }}>Avanzado</option>
                                </x-taller.select>
                            </div>
                            <div class="col-md-2">
                                <x-taller.select 
                                    label="TRIMESTRE" 
                                    icon="fas fa-calendar-minus" 
                                    name="trimestre"
                                    onchange="generateProcinecCode()"
                                >
                                    <option value="1" {{ old('trimestre') == 1 ? 'selected' : '' }}>1° </option>
                                    <option value="2" {{ old('trimestre') == 2 ? 'selected' : '' }}>2° </option>
                                    <option value="3" {{ old('trimestre') == 3 ? 'selected' : '' }}>3° </option>
                                    <option value="4" {{ old('trimestre') == 4 ? 'selected' : '' }}>4° </option>
                                </x-taller.select>
                                <div id="trimestre-error" class="text-danger small fw-bold mt-1" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> No se adapta a la función trimestral
                                </div>
                            </div>
                            <div class="col-md-2">
                                <x-taller.input 
                                    label="AÑO" 
                                    icon="fas fa-calendar-check" 
                                    name="anio" 
                                    :value="old('anio', date('Y'))" 
                                    type="number" 
                                    oninput="generateProcinecCode()"
                                />
                            </div>
                            <div class="col-md-2">
                                <x-taller.input 
                                    label="CORREL." 
                                    icon="fas fa-hashtag" 
                                    name="correlativo" 
                                    :value="old('correlativo', $proximoCorrelativo)" 
                                    type="number" 
                                    oninput="generateProcinecCode()"
                                />
                            </div>

                            <!-- Fechas, Cupos y Tiempos -->
                            <div class="col-md-4">
                                <x-taller.input 
                                    label="INICIO" 
                                    icon="far fa-calendar-check" 
                                    name="fecha_inicio" 
                                    :value="old('fecha_inicio')" 
                                    type="date" 
                                    onchange="validateTrimestre()"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input 
                                    label="FINALIZACIÓN" 
                                    icon="far fa-calendar-minus" 
                                    name="fecha_fin" 
                                    :value="old('fecha_fin')" 
                                    type="date" 
                                    onchange="validateTrimestre()"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-taller.input 
                                    label="CUPO MÁXIMO" 
                                    icon="fas fa-user-plus" 
                                    name="cantidad_cupos" 
                                    :value="old('cantidad_cupos')" 
                                    type="number" 
                                    placeholder="0"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-taller.input 
                                    label="DURACIÓN EN DÍAS" 
                                    icon="fas fa-calendar-week" 
                                    name="duracion" 
                                    :value="old('duracion')" 
                                    type="number" 
                                    placeholder="0"
                                    class="text-center"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-taller.input 
                                    label="TOTAL HORAS ACADÉMICAS" 
                                    icon="fas fa-clock" 
                                    name="horas" 
                                    :value="old('horas')" 
                                    type="number" 
                                    placeholder="0"
                                    class="text-center"
                                />
                            </div>

                            <!-- Descripción -->
                            <div class="col-12 mt-2">
                                <label for="descripcion" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 opacity-50"></i> RESUMEN O SÍNTESIS DEL PROGRAMA
                                </label>
                                <textarea class="form-control border-2 shadow-none rounded-4 bg-white p-3 border-light-2" id="descripcion" name="descripcion"
                                    rows="4" placeholder="Breve descripción del programa académico...">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Currículo Académico -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                    <x-taller.card-header 
                        icon="fas fa-layer-group" 
                        title="Estructura de Contenidos" 
                        subtitle="Define los módulos evaluables y recursos digitales."
                    >
                        <!-- Barra de Ponderación -->
                        <div id="total-ponderacion-container" class="card border-2 border-dashed shadow-xs p-2 px-3 rounded-4 bg-light ms-auto" style="min-width: 250px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-bold text-muted uppercase">Balance Académico</span>
                                <span class="badge bg-white text-dark fw-bold border"><span id="total-ponderacion-valor">0</span>%</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="ponderacion-progress" class="progress-bar rounded-pill" role="progressbar" style="width: 0%; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    </x-taller.card-header>
                    <div class="card-body p-4 bg-light bg-opacity-25">
                        <div id="contenidos-container" class="row row-cols-1 g-4">
                            @foreach(old('contenidos', []) as $index => $contenido)
                                <x-taller.contenido-item 
                                    :index="$index" 
                                    :contenido="$contenido" 
                                    :tiposEvaluacion="$tiposEvaluacion" 
                                />
                            @endforeach
                        </div>

                        <div class="text-center mt-5">
                            <button type="button" id="agregar-contenido"
                                class="btn btn-outline-dark border-2 px-5 py-3 rounded-pill fw-bold hvr-push shadow-sm">
                                <i class="fas fa-plus-circle me-2"></i> Añadir Bloque Académico
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer de Acciones -->
                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                <a href="{{ route('taller.cursos.index') }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    Descartar
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <div class="me-3 text-end">
                                        <div id="alert-100" class="text-success small fw-bold" style="display: none;">
                                            <i class="fas fa-check-circle me-1"></i> Listo para crear
                                        </div>
                                        <div id="alert-insufficient" class="text-warning small fw-bold">
                                            <i class="fas fa-info-circle me-1"></i> Sin contenidos
                                        </div>
                                        <div id="alert-over" class="text-danger small fw-bold" style="display: none;">
                                            <i class="fas fa-times-circle me-1"></i> Exceso de (%)
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-save-curso" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg">
                                        <i class="fas fa-rocket me-2"></i> Crear Nuevo Curso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Facilitadores (Igual a Editar) -->
    <div class="modal fade" id="modalFacilitadores" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-search me-2"></i> Asignar Facilitador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted border-light-2"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-start-0 py-2 border-light-2" id="filtro_nombre_cedula_modal" onkeyup="filterFacilitators()" placeholder="Nombre o cédula...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <select id="filtro_especializacion_modal" class="form-select shadow-sm py-2 border-light-2" onchange="filterFacilitators()">
                                <option value="">Todas las especialidades</option>
                                @foreach($especializaciones as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="facilitator-container" class="bg-white rounded-4 border border-light-2 overflow-auto shadow-sm" style="max-height: 400px;">
                        @foreach($facilitadores as $facilitador)
                            @php $p = $facilitador->personalData @endphp
                            <div class="facilitator-item p-3 border-bottom cursor-pointer transition-all hover-bg-light {{ old('id_persona') == $p->id_persona ? 'bg-primary-soft border-primary border-start border-4' : '' }}"
                                onclick="selectFacilitator(this, '{{ $p->crypt_id }}', '{{ $p->primer_nombre }} {{ $p->primer_apellido }}', '{{ $p->dni }}')"
                                data-name="{{ strtolower($p->primer_nombre . ' ' . $p->primer_apellido) }}"
                                data-doc="{{ $p->dni }}"
                                data-specializations="{{ $p->especializaciones->pluck('id')->join(',') }}">
                                
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $p->primer_nombre }} {{ $p->primer_apellido }}</h6>
                                        <small class="text-muted d-block">C.I: {{ $p->dni }}</small>
                                        <div class="mt-1">
                                            @foreach($p->especializaciones as $esp)
                                                <span class="badge bg-light text-primary border border-primary-soft small fw-normal rounded-pill me-1 px-2" style="font-size: 0.75rem;">
                                                    {{ $esp->nombre }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="ms-3 check-icon {{ old('id_persona') == $p->id_persona ? '' : 'd-none' }}">
                                        <i class="fas fa-check-circle text-primary fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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

    @push('styles')
    <style>
        .bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
        .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
        .bg-gray-100 { background-color: #f1f5f9; }
        .border-light-2 { border: 1.5px solid #d1d5db !important; }
        .shadow-xs { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .shadow-card { box-shadow: 0 12px 35px rgba(0,0,0,0.1); }
        
        .content-card-edit {
            border: 1px solid #e2e8f0;
            border-left: 5px solid #1e3a8a;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        .content-card-edit:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(30, 58, 138, 0.12) !important;
            border-color: rgba(30, 58, 138, 0.2);
        }
        
        .focus-none:focus { outline: none; border: none; box-shadow: none; }
        .hvr-push { transition: transform 0.2s; }
        .hvr-push:active { transform: scale(0.98); }
        .transition-hover { transition: all 0.2s ease; }
        .transition-hover:hover { background-color: #f1f5f9; transform: translateY(-1px); }
        .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
        .cursor-pointer { cursor: pointer; }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/modules/taller/facilitadores.js') }}"></script>
    <script src="{{ asset('js/modules/taller/cursos.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            TallerModule.init({
                tiposEvaluacion: {!! json_encode($tiposEvaluacion) !!}
            });
        });

        /**
         * Proxies de compatibilidad
         * Estas funciones permiten que los atributos onchange/onclick del HTML 
         * sigan funcionando llamando al nuevo módulo modular.
         */
        function generateProcinecCode() { TallerModule.generateProcinecCode(); }
        function validateTrimestre() { TallerModule.validateTrimestre(); }
        function toggleEvaluacion(el) { TallerModule.toggleEvaluacion(el); }
        function toggleMaterial(el) { TallerModule.toggleMaterial(el); }
        function toggleLocalidades(el) { TallerModule.toggleLocalidades(el); }
        function filterFacilitators() { FacilitadorModule.filterFacilitators(); }
        
        function selectFacilitator(element, id, name, doc) { 
            // Manejo de UI específica del modal antes de delegar al módulo
            document.querySelectorAll('.facilitator-item').forEach(item => {
                item.classList.remove('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
                item.querySelector('.check-icon').classList.add('d-none');
            });
            element.classList.add('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
            element.querySelector('.check-icon').classList.remove('d-none');
            
            FacilitadorModule.selectFacilitador(id, name, doc); 
        }
    </script>
    @endpush
@endsection