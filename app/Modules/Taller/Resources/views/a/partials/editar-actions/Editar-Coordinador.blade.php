    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas -->
            @if(session('success'))
                <x-taller.alert type="success" title="¡Actualizado!">
                    {{ session('success') }}
                </x-taller.alert>
            @endif

            <!-- Encabezado Administrativo -->
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Configuración Maestra</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Edición de Curso (Coordinador)</h2>
                    <p class="text-muted small mb-0"><i class="fas fa shield-alt me-1 text-primary"></i> Posees permisos totales para modificar los parámetros base de este programa.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.certificado.edit', $curso->crypt_id) }}" 
                       class="btn btn-outline-primary shadow-sm rounded-pill px-4 btn-sm fw-bold transition-hover me-2">
                        <i class="fas fa-certificate me-2"></i> Ajustar Certificado
                    </a>
                    <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Volver
                    </a>
                </div>
            </div>

            <form action="{{ route('taller.cursos.update', $curso->crypt_id) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <x-taller.alert type="danger" title="Errores detectados">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-taller.alert>
                @endif

                <!-- Panel de Control del Curso -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <x-taller.card-header 
                        icon="fas fa-cog" 
                        title="Parámetros Maestros" 
                    />
                    <div class="card-body p-4 bg-light bg-opacity-10">
                        <div class="row g-4">
                            <!-- Sección: Identidad del Programa -->
                            <div class="col-md-7">
                                <x-taller.input 
                                    label="NOMBRE OFICIAL DEL CURSO" 
                                    icon="fas fa-heading" 
                                    name="nombre" 
                                    :value="old('nombre', $curso->nombre)" 
                                    placeholder="Ej: Especialización en Cine Digital" 
                                    required 
                                />
                            </div>

                            <div class="col-md-12">
                                <x-taller.input 
                                    label="CANAL DE TELEGRAM (CANAL DE COMUNICACIÓN)" 
                                    icon="fab fa-telegram-plane" 
                                    name="telegram" 
                                    :value="old('telegram', $curso->telegram)" 
                                    placeholder="https://t.me/..." 
                                />
                            </div>

                            <!-- Facilitador Asignado -->
                            <div class="col-md-5">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-tie me-2 opacity-50 text-primary"></i> FACILITADOR ASIGNADO <span class="text-danger ms-1">*</span>
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
                                                $facilitadorActual = $facilitadores->first(fn($f) => $f->personalData->id_persona == $curso->id_persona);
                                            @endphp
                                            {{ $facilitadorActual ? ($facilitadorActual->personalData->primer_nombre . ' ' . $facilitadorActual->personalData->primer_apellido) : 'No asignado' }}
                                        </h6>
                                        <small class="text-muted d-block" id="selected-facilitator-doc">
                                            {{ $facilitadorActual ? 'C.I: ' . $facilitadorActual->personalData->dni : 'Seleccione un docente' }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0 ms-2" data-bs-toggle="modal" data-bs-target="#modalFacilitadores">
                                        <i class="fas fa-exchange-alt small"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="id_persona" id="id_persona" value="{{ old('id_persona', $curso->persona->crypt_id) }}" required>
                            </div>

                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check form-switch p-3 bg-white rounded-4 border shadow-xs d-inline-flex align-items-center w-100" style="height: 58px;">
                                    <input type="hidden" name="es_nacional_present" value="1">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="es_nacional" name="es_nacional" value="1" {{ old('es_nacional', $curso->es_nacional) ? 'checked' : '' }} onchange="TallerModule.toggleLocalidades(this)">
                                    <label class="form-check-label fw-bold text-dark mb-0 text-truncate" for="es_nacional">
                                        <i class="fas fa-globe-americas me-1 text-primary"></i> ALCANCE NACIONAL
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12" id="container-localidades" style="{{ old('es_nacional', $curso->es_nacional) ? 'display:none;' : '' }}">
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
                                            @php 
                                                $localidadesActuales = old('localidades', $curso->localidades->pluck('id')->toArray());
                                            @endphp
                                            @foreach($regiones as $reg)
                                                <div class="col localidad-item" data-nombre="{{ strtolower($reg->description) }}">
                                                    <div class="form-check p-0 m-0">
                                                        <input type="checkbox" 
                                                               class="btn-check localidad-checkbox" 
                                                               name="localidades[]" 
                                                               id="loc_{{ $reg->id }}" 
                                                               value="{{ $reg->id }}"
                                                               {{ in_array($reg->id, $localidadesActuales) ? 'checked' : '' }}
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
                                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> Puedes modificar las localidades asignadas.</small>
                            </div>

                            <!-- Sección: Configuración Académica -->
                            <div class="col-md-4">
                                <x-taller.select 
                                    label="MODALIDAD" 
                                    icon="fas fa-laptop-house" 
                                    name="id_modalidad"
                                    required
                                >
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id_modalidad }}" {{ old('id_modalidad', $curso->id_modalidad) == $mod->id_modalidad ? 'selected' : '' }}>
                                            {{ $mod->nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-4">
                                <x-taller.input 
                                    label="CUPO MÁXIMO" 
                                    icon="fas fa-user-plus" 
                                    name="cantidad_cupos" 
                                    :value="old('cantidad_cupos', $curso->cantidad_cupos)" 
                                    type="number" 
                                />
                            </div>
                            <div class="col-md-2">
                                <x-taller.input 
                                    label="DURACIÓN" 
                                    icon="fas fa-calendar-week" 
                                    name="duracion" 
                                    :value="old('duracion', $curso->duracion)" 
                                    type="number" 
                                    class="text-center"
                                />
                            </div>
                            <div class="col-md-2">
                                <x-taller.input 
                                    label="HORAS" 
                                    icon="fas fa-clock" 
                                    name="horas" 
                                    :value="old('horas', $curso->horas)" 
                                    type="number" 
                                    class="text-center"
                                />
                            </div>

                            <!-- Sección: Temporalidad -->
                            <div class="col-md-6">
                                <x-taller.input 
                                    label="Apertura de curso" 
                                    icon="far fa-play-circle" 
                                    name="fecha_inicio" 
                                    :value="old('fecha_inicio', $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : '')" 
                                    type="date" 
                                />
                            </div>
                            <div class="col-md-6">
                                <x-taller.input 
                                    label="CIERRE DE CURSO" 
                                    icon="far fa-stop-circle" 
                                    name="fecha_fin" 
                                    :value="old('fecha_fin', $curso->fecha_fin ? $curso->fecha_fin->format('Y-m-d') : '')" 
                                    type="date" 
                                />
                            </div>

                            <!-- Área Descriptiva -->
                            <div class="col-12 mt-2">
                                <label for="descripcion" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 opacity-50"></i> SÍNTESIS CURRICULAR DEL PROGRAMA
                                </label>
                                <textarea class="form-control border-2 shadow-none rounded-4 bg-white p-3 border-light-2" id="descripcion" name="descripcion"
                                    rows="4" placeholder="Breve descripción del programa académico para los estudiantes...">{{ old('descripcion', $curso->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sección: Currículo Académico -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                    <x-taller.card-header 
                        icon="fas fa-list-ul" 
                        title="Estructura de Contenidos" 
                        subtitle="Configura los módulos y el sistema de evaluación promediado."
                    >
                        <!-- Barra de Ponderación Dinámica -->
                        <div id="total-ponderacion-container" class="card border-2 border-dashed shadow-xs p-2 px-3 rounded-4 bg-light ms-auto" style="min-width: 250px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-bold text-muted uppercase">Balance de Notas</span>
                                <span class="badge bg-white text-dark fw-bold border"><span id="total-ponderacion-valor">0</span>%</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="ponderacion-progress" class="progress-bar rounded-pill" role="progressbar" style="width: 0%; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    </x-taller.card-header>
                    <div class="card-body p-4 bg-light bg-opacity-25">

                    <div id="contenidos-container" class="row row-cols-1 g-5">
                        @foreach($contenidos as $index => $contenido)
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
                            <i class="fas fa-plus-circle me-2"></i> Añadir Bloque de Contenido
                        </button>
                    </div>
                    </div>
                </div>

                <!-- Footer de Acciones -->
                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    Cancelar
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <div class="me-3 text-end">
                                        <div id="alert-100" class="text-success small fw-bold" style="display: none;">
                                            <i class="fas fa-check-circle me-1"></i> Balance correcto
                                        </div>
                                        <div id="alert-insufficient" class="text-warning small fw-bold" style="display: none;">
                                            <i class="fas fa-exclamation-circle me-1"></i> Incompleto
                                        </div>
                                        <div id="alert-over" class="text-danger small fw-bold" style="display: none;">
                                            <i class="fas fa-times-circle me-1"></i> Sobrecarga
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-save-curso" class="btn btn-dark px-5 py-3 rounded-pill fw-bold shadow-lg">
                                        <i class="fas fa-save me-2"></i> Guardar Cambios Maestros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
