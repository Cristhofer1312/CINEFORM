@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show">
                    <div class="icon-shape icon-sm bg-success-light text-success rounded-circle me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div><h6 class="mb-0 fw-bold">¡Creado!</h6><small>{{ session('success') }}</small></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
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
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Panel de Control Principal -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                            <i class="fas fa-plus fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Información de Cabecera</h5>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <!-- Nombre y Código -->
                            <div class="col-md-8">
                                <label for="nombre" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-heading me-2 opacity-50"></i> NOMBRE DEL CURSO *
                                </label>
                                <input type="text" class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2" id="nombre" name="nombre"
                                    value="{{ old('nombre') }}" placeholder="Ej: Especialización en Guion Cinematográfico" required>
                            </div>
                            <div class="col-md-4">
                                <label for="codigo" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-barcode me-2 opacity-50"></i> CÓDIGO PROCINEC
                                </label>
                                <input type="text" class="form-control border-2 shadow-none rounded-3 bg-white py-3 fs-5 border-light-2 text-primary fw-bold" id="codigo" name="codigo"
                                    value="{{ old('codigo') }}" placeholder="LAB-TA...">
                            </div>

                            <!-- Facilitador y Región -->
                            <div class="col-md-7">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-tie me-2 opacity-50 text-primary"></i> FACILITADOR RESPONSABLE *
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

                            <div class="col-md-5">
                                <label for="id_estado" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-2 opacity-50 text-danger"></i> ESTADO / REGIÓN
                                </label>
                                <select name="id_estado" id="id_estado" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" style="height: 58px;">
                                    <option value="">Seleccione región...</option>
                                    @foreach($regiones as $reg)
                                        <option value="{{ $reg->id_estado }}" {{ old('id_estado') == $reg->id_estado ? 'selected' : '' }}>
                                            {{ $reg->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Clasificación Académica -->
                            <div class="col-md-4">
                                <label for="id_actividad_formativa" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-graduation-cap me-2 opacity-50"></i> ACTIVIDAD FORMATIVA
                                </label>
                                <select name="id_actividad_formativa" id="id_actividad_formativa" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" onchange="generateProcinecCode()">
                                    <option value="" data-abreviatura="">Seleccione tipo...</option>
                                    @foreach($actividades as $act)
                                        <option value="{{ $act->id_actividad_formativa }}" data-abreviatura="{{ $act->abreviatura }}" {{ old('id_actividad_formativa') == $act->id_actividad_formativa ? 'selected' : '' }}>
                                            {{ $act->nombre }} ({{ $act->abreviatura }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="id_aspecto" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-film me-2 opacity-50"></i> ASPECTO CINEMATOGRÁFICO
                                </label>
                                <select name="id_aspecto" id="id_aspecto" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" onchange="generateProcinecCode()">
                                    <option value="" data-abreviatura="">Seleccione aspecto...</option>
                                    @foreach($aspectos as $asp)
                                        <option value="{{ $asp->id_aspecto }}" data-abreviatura="{{ $asp->abreviatura }}" {{ old('id_aspecto') == $asp->id_aspecto ? 'selected' : '' }}>
                                            {{ $asp->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="id_modalidad_especial" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-users me-2 opacity-50"></i> PÚBLICO OBJETIVO
                                </label>
                                <select name="id_modalidad_especial" id="id_modalidad_especial" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" onchange="generateProcinecCode()">
                                    <option value="" data-abreviatura="">Seleccione público...</option>
                                    @foreach($modalidadesEspeciales as $me)
                                        <option value="{{ $me->id_modalidad_especial }}" data-abreviatura="{{ $me->abreviatura }}" {{ old('id_modalidad_especial') == $me->id_modalidad_especial ? 'selected' : '' }}>
                                            {{ $me->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Parámetros y Nivel -->
                            <div class="col-md-3">
                                <label for="id_modalidad" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-laptop-house me-2 opacity-50"></i> MODALIDAD *
                                </label>
                                <select name="id_modalidad" id="id_modalidad" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" required onchange="generateProcinecCode()">
                                    <option value="" data-abreviatura="">Seleccione modalidad...</option>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id_modalidad }}" data-abreviatura="{{ $mod->abreviatura }}" {{ old('id_modalidad') == $mod->id_modalidad ? 'selected' : '' }}>
                                            {{ $mod->nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="nivel" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-layer-group me-2 opacity-50"></i> NIVEL
                                </label>
                                <select name="nivel" id="nivel" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2">
                                    <option value="">Seleccione nivel...</option>
                                    <option value="Básico" {{ old('nivel') == 'Básico' ? 'selected' : '' }}>Básico</option>
                                    <option value="Medio" {{ old('nivel') == 'Medio' ? 'selected' : '' }}>Medio</option>
                                    <option value="Avanzado" {{ old('nivel') == 'Avanzado' ? 'selected' : '' }}>Avanzado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="trimestre" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-calendar-minus me-2 opacity-50"></i> TRIMESTRE
                                </label>
                                <select name="trimestre" id="trimestre" class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2" onchange="generateProcinecCode()">
                                    <option value="">...</option>
                                    <option value="1" {{ old('trimestre') == 1 ? 'selected' : '' }}>1° </option>
                                    <option value="2" {{ old('trimestre') == 2 ? 'selected' : '' }}>2° </option>
                                    <option value="3" {{ old('trimestre') == 3 ? 'selected' : '' }}>3° </option>
                                    <option value="4" {{ old('trimestre') == 4 ? 'selected' : '' }}>4° </option>
                                </select>
                                <div id="trimestre-error" class="text-danger small fw-bold mt-1" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> No se adapta a la función trimestral
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="anio" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-calendar-check me-2 opacity-50"></i> AÑO
                                </label>
                                <input type="number" class="form-control border-2 shadow-none rounded-3 bg-white py-2 border-light-2 text-center" id="anio" name="anio"
                                    value="{{ old('anio', date('Y')) }}" placeholder="{{ date('Y') }}" oninput="generateProcinecCode()">
                            </div>
                            <div class="col-md-2">
                                <label for="correlativo" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-hashtag me-2 opacity-50"></i> CORREL.
                                </label>
                                <input type="number" class="form-control border-2 shadow-none rounded-3 bg-white py-2 border-light-2 text-center" id="correlativo" name="correlativo"
                                    value="{{ old('correlativo', $proximoCorrelativo) }}" placeholder="000" oninput="generateProcinecCode()">
                            </div>

                            <!-- Fechas, Cupos y Tiempos -->
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-calendar-alt me-2 opacity-50 text-success"></i> INICIO
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-check"></i></span>
                                    <input type="date" class="form-control border-2 border-start-0 py-2 border-light-2" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" onchange="validateTrimestre()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-calendar-times me-2 opacity-50 text-danger"></i> FINALIZACIÓN
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-minus"></i></span>
                                    <input type="date" class="form-control border-2 border-start-0 py-2 border-light-2" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" onchange="validateTrimestre()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-users-cog me-2 opacity-50"></i> CUPO MÁXIMO
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="fas fa-user-plus small"></i></span>
                                    <input type="number" class="form-control border-2 border-start-0 py-2 fw-bold border-light-2" name="cantidad_cupos" value="{{ old('cantidad_cupos') }}" placeholder="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-calendar-week me-2 opacity-50"></i> DURACIÓN EN DÍAS
                                </label>
                                <input type="number" class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3" name="duracion" value="{{ old('duracion') }}" placeholder="0">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-clock me-2 opacity-50"></i> TOTAL HORAS ACADÉMICAS
                                </label>
                                <input type="number" class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3" name="horas" value="{{ old('horas') }}" placeholder="0">
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
                    <div class="card-header bg-white py-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                                    <i class="fas fa-layer-group fs-5"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark">Estructura de Contenidos</h4>
                                    <p class="text-muted small mb-0">Define los módulos evaluables y recursos digitales.</p>
                                </div>
                            </div>
                            
                            <!-- Barra de Ponderación -->
                            <div id="total-ponderacion-container" class="card border-2 border-dashed shadow-xs p-2 px-3 rounded-4 bg-light" style="min-width: 250px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-muted uppercase">Balance Académico</span>
                                    <span class="badge bg-white text-dark fw-bold border"><span id="total-ponderacion-valor">0</span>%</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div id="ponderacion-progress" class="progress-bar rounded-pill" role="progressbar" style="width: 0%; transition: width 0.5s ease;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-25">
                        <div id="contenidos-container" class="row row-cols-1 g-4">
                            @foreach(old('contenidos', []) as $index => $contenido)
                                <div class="col contenido-item animate__animated animate__fadeIn" data-index="{{ $index }}">
                                    <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                                                <div class="col-md-auto">
                                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="contenidos[{{ $index }}][titulo]" 
                                                        class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" 
                                                        required value="{{ $contenido['titulo'] ?? '' }}" placeholder="Título del bloque...">
                                                </div>
                                                <div class="col-md-auto">
                                                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                                                        <input type="hidden" name="contenidos[{{ $index }}][es_evaluacion]" value="0">
                                                        <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" 
                                                            id="evalSwitch_{{ $index }}" name="contenidos[{{ $index }}][es_evaluacion]" 
                                                            value="1" onchange="toggleEvaluacion(this)"
                                                            {{ (isset($contenido['es_evaluacion']) && $contenido['es_evaluacion'] == '1') ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_{{ $index }}">Evaluable</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-auto">
                                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                                                </div>
                                            </div>

                                            <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3" 
                                                style="{{ (isset($contenido['es_evaluacion']) && $contenido['es_evaluacion'] == '1') ? 'display:flex' : 'display:none' }}; background-color: #0d6efd !important;">
                                                <div class="col-md-7">
                                                    <label class="small text-white fw-bold mb-1">METODOLOGÍA</label>
                                                    <select name="contenidos[{{ $index }}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                                        <option value="">Seleccione...</option>
                                                        @foreach($tiposEvaluacion as $tipo)
                                                            <option value="{{ $tipo->id_tipo_evaluacion }}" {{ (isset($contenido['id_tipo_evaluacion']) && $contenido['id_tipo_evaluacion'] == $tipo->id_tipo_evaluacion) ? 'selected' : '' }}>
                                                                {{ $tipo->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="small text-white fw-bold mb-1">PESO (%)</label>
                                                    <input type="number" name="contenidos[{{ $index }}][ponderacion]" 
                                                        class="form-control border-0 text-center fw-bold text-primary" 
                                                        oninput="actualizarPonderacionTotal()" value="{{ $contenido['ponderacion'] ?? '' }}" placeholder="0">
                                                </div>
                                            </div>

                                            {{-- Panel de Material de Apoyo (Toggle) --}}
                                            <div class="material-fields row g-3 rounded-3 p-3 mb-3"
                                                style="{{ !empty($contenido['url_contenido']) ? 'display:flex' : 'display:none' }}; background-color: #4f46e5 !important;">
                                                <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                                                    <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                                                </div>
                                                <div class="col-12">
                                                    <label class="small text-white fw-bold mb-1 opacity-75">ENLACE DEL RECURSO (URL)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-0 opacity-50"><i class="fas fa-link small"></i></span>
                                                        <input type="url" name="contenidos[{{ $index }}][url_contenido]"
                                                            class="form-control border-0 py-2"
                                                            value="{{ $contenido['url_contenido'] ?? '' }}" placeholder="https://...">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-7">
                                                    <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                                    <input type="date" name="contenidos[{{ $index }}][fecha_contenido]"
                                                        class="form-control border-light-2 py-2"
                                                        value="{{ $contenido['fecha_contenido'] ?? '' }}">
                                                </div>
                                                <div class="col-md-5 d-flex align-items-end">
                                                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                                                        <input type="hidden" name="contenidos[{{ $index }}][_has_material]" value="0">
                                                        <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle"
                                                            id="matSwitch_{{ $index }}"
                                                            onchange="toggleMaterial(this)"
                                                            {{ !empty($contenido['url_contenido']) ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_{{ $index }}">
                                                            <i class="fas fa-paperclip me-1 text-indigo"></i> ¿Tiene material?
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <textarea name="contenidos[{{ $index }}][descripcion_breve]" class="form-control border-light-2" 
                                                        rows="2" placeholder="Breve explicación...">{{ $contenido['descripcion_breve'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                onclick="selectFacilitator(this, '{{ $p->id_persona }}', '{{ $p->primer_nombre }} {{ $p->primer_apellido }}', '{{ $p->dni }}')"
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
    </style>
    @endpush

    @push('scripts')
    <script>
        const tiposEvaluacion = {!! json_encode($tiposEvaluacion) !!};
        let contadorContenidos = {{ count(old('contenidos', [])) }};

        document.addEventListener('DOMContentLoaded', function() {
            actualizarPonderacionTotal();
        });

        function getTipoOptions() {
            return tiposEvaluacion.map(t => `<option value="${t.id_tipo_evaluacion}">${t.nombre}</option>`).join('');
        }

        function filterFacilitators() {
            const searchText = document.getElementById('filtro_nombre_cedula_modal').value.toLowerCase();
            const specId = document.getElementById('filtro_especializacion_modal').value;
            const items = document.querySelectorAll('.facilitator-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const doc = item.getAttribute('data-doc');
                const specs = item.getAttribute('data-specializations').split(',');

                const matchesSearch = name.includes(searchText) || doc.includes(searchText);
                const matchesSpec = specId === "" || specs.includes(specId);

                if (matchesSearch && matchesSpec) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        }

        function selectFacilitator(element, id, name, doc) {
            document.querySelectorAll('.facilitator-item').forEach(item => {
                item.classList.remove('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
                item.querySelector('.check-icon').classList.add('d-none');
            });

            element.classList.add('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
            element.querySelector('.check-icon').classList.remove('d-none');

            document.getElementById('id_persona').value = id;
            document.getElementById('selected-facilitator-name').innerText = name;
            document.getElementById('selected-facilitator-doc').innerText = 'C.I: ' + doc;
            
            // Cerrar modal tras selección
            const modalEl = document.getElementById('modalFacilitadores');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if(modal) setTimeout(() => modal.hide(), 250);
        }

        function toggleEvaluacion(inputElement) {
            const container = inputElement.closest('.contenido-item');
            const evalFields = container.querySelector('.evaluacion-fields');
            const isActive = inputElement.checked;

            if(isActive) {
                evalFields.style.display = 'flex';
                evalFields.style.opacity = 0;
                setTimeout(() => { evalFields.style.opacity = 1; }, 50);
            } else {
                evalFields.style.display = 'none';
                evalFields.querySelectorAll('input, select').forEach(el => el.value = '');
            }
            actualizarPonderacionTotal();
        }

        function toggleMaterial(inputElement) {
            const container = inputElement.closest('.contenido-item');
            const materialFields = container.querySelector('.material-fields');
            const urlInput = materialFields ? materialFields.querySelector('input[type="url"]') : null;
            const isActive = inputElement.checked;

            if (isActive) {
                materialFields.style.display = 'flex';
                materialFields.style.opacity = 0;
                setTimeout(() => { materialFields.style.opacity = 1; }, 50);
            } else {
                materialFields.style.display = 'none';
                if (urlInput) urlInput.value = '';
            }
        }

        function actualizarPonderacionTotal() {
            let total = 0;
            let hasEvaluacion = false;
            const contenidoItems = document.querySelectorAll('.contenido-item');
            const hasContenidos = contenidoItems.length > 0;
            const evalInputs = document.querySelectorAll('input[name*="[ponderacion]"]');
            
            evalInputs.forEach(input => {
                const container = input.closest('.contenido-item');
                const evalCheckbox = container.querySelector('.custom-control-input');
                const isEval = evalCheckbox && evalCheckbox.checked;
                if (isEval) {
                    hasEvaluacion = true;
                    total += parseFloat(input.value) || 0;
                }
            });

            document.getElementById('total-ponderacion-valor').innerText = total;
            const progressBar = document.getElementById('ponderacion-progress');
            progressBar.style.width = Math.min(total, 100) + '%';
            
            const alert100 = document.getElementById('alert-100');
            const alertInsuff = document.getElementById('alert-insufficient');
            const alertOver = document.getElementById('alert-over');
            const btnSave = document.getElementById('btn-save-curso');
            
            alert100.style.display = 'none';
            alertInsuff.style.display = 'none';
            alertOver.style.display = 'none';

            if (hasContenidos) {
                progressBar.className = 'progress-bar bg-success';
                alert100.style.display = 'block';
                if (btnSave) btnSave.disabled = false;
            } else {
                progressBar.className = 'progress-bar bg-secondary';
                alertInsuff.style.display = 'block';
                alertInsuff.innerHTML = '<i class="fas fa-info-circle me-1"></i> Sin contenidos';
                if (btnSave) btnSave.disabled = false;
            }
        }

        function generateProcinecCode() {
            const getAbbr = (id) => {
                const el = document.getElementById(id);
                if(!el) return '';
                return el.options[el.selectedIndex]?.getAttribute('data-abreviatura') || '';
            };

            const actividad = getAbbr('id_actividad_formativa');
            const trimestre = document.getElementById('trimestre').value || '';
            const correlativo = document.getElementById('correlativo').value || '';
            const aspecto = getAbbr('id_aspecto');
            const modo = getAbbr('id_modalidad');
            const publico = getAbbr('id_modalidad_especial');
            const anio = document.getElementById('anio').value || '';

            if (actividad || trimestre || correlativo || aspecto || modo || publico || anio) {
                const code = `LAB-${actividad}${trimestre}${correlativo}${aspecto}${modo}${publico}-${anio}`;
                document.getElementById('codigo').value = code.replace(/-+$/, '');
            }
        }

        function validateTrimestre() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const trimestreSelect = document.getElementById('trimestre');
            const errorDiv = document.getElementById('trimestre-error');
            const btnSave = document.getElementById('btn-save-curso');

            if (!fechaInicio || !fechaFin) return;

            const getTrimestre = (dateStr) => {
                const month = new Date(dateStr).getUTCMonth() + 1; // 1-12
                return Math.ceil(month / 3);
            };

            const tInicio = getTrimestre(fechaInicio);
            const tFin = getTrimestre(fechaFin);

            if (tInicio === tFin) {
                // Mismo trimestre: Auto-seleccionar y limpiar error
                trimestreSelect.value = tInicio;
                trimestreSelect.classList.remove('border-danger');
                errorDiv.style.display = 'none';
                btnSave.disabled = false;
                
                // Actualizar año automáticamente basado en fecha de inicio
                document.getElementById('anio').value = new Date(fechaInicio).getUTCFullYear();
                
                generateProcinecCode();
            } else {
                // Diferentes trimestres: Bloquear y mostrar error
                trimestreSelect.value = "";
                trimestreSelect.classList.add('border-danger');
                errorDiv.style.display = 'block';
                btnSave.disabled = true;
                
                Swal.fire({
                    icon: 'error',
                    title: 'Rango de fechas inválido',
                    text: 'El curso inicia en un trimestre y termina en otro. Esto no se adapta a la función trimestral de PROCINEC.',
                    confirmButtonColor: '#1e3a8a'
                });
            }
        }

        document.getElementById('agregar-contenido').addEventListener('click', function () {
            const contenedor = document.getElementById('contenidos-container');
            const index = contadorContenidos++;
            const template = `
                <div class="col contenido-item animate__animated animate__zoomIn" data-index="${index}">
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden content-card-edit mb-2">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3 border-bottom pb-3 mb-3">
                                <div class="col-md-auto">
                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        ${index + 1}
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" name="contenidos[${index}][titulo]" class="form-control border-0 bg-transparent fs-5 fw-bold p-0 text-dark focus-none" required placeholder="Título del bloque...">
                                </div>
                                <div class="col-md-auto">
                                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-1 border shadow-xs">
                                        <input type="hidden" name="contenidos[${index}][es_evaluacion]" value="0">
                                        <input type="checkbox" class="form-check-input ms-0 me-2 custom-control-input" id="evalSwitch_${index}" name="contenidos[${index}][es_evaluacion]" value="1" onchange="toggleEvaluacion(this)">
                                        <label class="form-check-label small fw-bold text-muted mt-1" for="evalSwitch_${index}">Evaluable</label>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-contenido"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>

                            <div class="row g-3 evaluacion-fields bg-primary rounded-3 p-3 mb-3" style="display:none; background-color: #0d6efd !important;">
                                <div class="col-md-7">
                                    <label class="small text-white fw-bold mb-1">METODOLOGÍA</label>
                                    <select name="contenidos[${index}][id_tipo_evaluacion]" class="form-select border-0 shadow-sm">
                                        <option value="">Seleccione...</option>
                                        ${getTipoOptions()}
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="small text-white fw-bold mb-1">PESO (%)</label>
                                    <input type="number" name="contenidos[${index}][ponderacion]" class="form-control border-0 text-center fw-bold text-primary" oninput="actualizarPonderacionTotal()" placeholder="0">
                                </div>
                            </div>

                            <div class="material-fields row g-3 rounded-3 p-3 mb-3" style="display:none; background-color: #4f46e5 !important;">
                                <div class="col-12 text-white fw-bold small uppercase d-flex align-items-center mb-1">
                                    <i class="fas fa-paperclip me-2"></i> Material de Apoyo
                                </div>
                                <div class="col-12">
                                    <label class="small text-white fw-bold mb-1 opacity-75">ENLACE DEL RECURSO (URL)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 opacity-50"><i class="fas fa-link small"></i></span>
                                        <input type="url" name="contenidos[${index}][url_contenido]" class="form-control border-0 py-2" placeholder="https://...">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="small text-muted fw-bold mb-1">FECHA DE CLASE</label>
                                    <input type="date" name="contenidos[${index}][fecha_contenido]" class="form-control border-light-2 py-2">
                                </div>
                                <div class="col-md-5 d-flex align-items-end">
                                    <div class="form-check form-switch p-0 d-flex align-items-center bg-light rounded-pill px-3 py-2 border shadow-xs w-100">
                                        <input type="hidden" name="contenidos[${index}][_has_material]" value="0">
                                        <input type="checkbox" class="form-check-input ms-0 me-2 material-toggle"
                                            id="matSwitch_${index}"
                                            onchange="toggleMaterial(this)">
                                        <label class="form-check-label small fw-bold text-muted mt-1" for="matSwitch_${index}">
                                            <i class="fas fa-paperclip me-1"></i> ¿Tiene material?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <textarea name="contenidos[${index}][descripcion_breve]" class="form-control border-light-2" rows="2" placeholder="Breve explicación..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', template);
            actualizarPonderacionTotal();
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-contenido')) {
                const item = e.target.closest('.contenido-item');
                item.classList.add('animate__zoomOut');
                setTimeout(() => {
                    item.remove();
                    actualizarPonderacionTotal();
                }, 300);
            }
        });
    </script>
    @endpush
@endsection