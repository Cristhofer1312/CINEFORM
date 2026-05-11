    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Alertas de Sesión -->
            @if(session('success'))
                <x-taller.alert type="success" title="¡Guardado con éxito!">
                    {{ session('success') }}
                </x-taller.alert>
            @endif

            <!-- Encabezado de la Página -->
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Configuración Maestra</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Edición de Curso </h2>
                    <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1 text-primary"></i> Gestiona la descripción y el currículo de tu programa educativo.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Volver al Panel
                    </a>
                </div>
            </div>

                {{-- ══ Historial de Revisiones (visible solo si existen observaciones) ══ --}}
                @if($curso->observaciones && $curso->observaciones->count() > 0)
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-start border-4 border-warning">
                    <x-taller.card-header 
                        icon="fas fa-clipboard-list" 
                        title="Historial de Revisiones" 
                        subtitle="Observaciones de la coordinación sobre este curso. Revísalas para mejorar tu propuesta."
                        badge="{{ $curso->observaciones->count() }}"
                        badgeColor="warning text-dark"
                    />
                    <div class="card-body p-4">
                        @foreach($curso->observaciones as $obs)
                        <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="mb-1 text-dark" style="line-height: 1.6;">{{ $obs->observacion }}</p>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>{{ $obs->created_at->format('d/m/Y H:i') }}
                                    @if($obs->autor) — <i class="far fa-user me-1"></i>{{ $obs->autor->username ?? $obs->autor->name ?? '' }} @endif
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

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

                <!-- Datos Generales Card -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                    <x-taller.card-header 
                        icon="fas fa-cog" 
                        title="Parámetros Maestros" 
                    />
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <!-- Fila 1: Identidad -->
                            <div class="col-md-7">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-heading me-2 opacity-50"></i> NOMBRE OFICIAL DEL CURSO
                                </label>
                                <div class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2" style="height: auto; min-height: 58px;">
                                    {{ $curso->nombre ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-chalkboard-teacher me-2 opacity-50"></i> MODALIDAD
                                </label>
                                <div class="form-control border-2 shadow-none rounded-3 bg-white fw-bold py-3 fs-5 border-light-2 text-center" style="height: auto; min-height: 58px;">
                                    {{ $curso->modalidad->nombre_modalidad ?? 'N/A' }}
                                </div>
                            </div>

                            @if($curso->telegram)
                            <div class="col-12">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fab fa-telegram-plane me-2 opacity-50 text-info"></i> CANAL DE TELEGRAM ASIGNADO
                                </label>
                                <div class="alert alert-info border-0 shadow-xs rounded-3 d-flex align-items-center mb-0">
                                    <a href="{{ $curso->telegram }}" target="_blank" class="fw-bold text-decoration-none">
                                        <i class="fas fa-external-link-alt me-2"></i> Unirse al Canal de Comunicación
                                    </a>
                                    <span class="ms-auto badge bg-white text-info border">Solo lectura</span>
                                </div>
                            </div>
                            @endif

                            <!-- Fila 2: Métricas Rápidas -->
                            <div class="col-md-3">  
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-calendar-week me-2 opacity-50"></i> DURACIÓN
                                </label>
                                <div class="input-group">
                                    <div class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3 text-primary fs-5">
                                        {{ $curso->duracion ?? '0' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-truncate">
                                    <i class="fas fa-clock me-2 opacity-50"></i> HORAS
                                </label>
                                <div class="input-group">
                                    <div class="form-control border-2 py-2 text-center fw-bold border-light-2 rounded-3 text-primary fs-5">
                                        {{ $curso->horas ?? '0' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-play-circle me-2 opacity-50 text-success"></i> Apertura de curso
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-check"></i></span>
                                    <div class="form-control border-2 border-start-0 py-2 border-light-2 fw-bold">
                                        {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center text-uppercase">
                                    <i class="far fa-stop-circle me-2 opacity-50 text-danger"></i> CIERRE DE CURSO
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-white border-2 border-end-0 text-muted border-light-2"><i class="far fa-calendar-minus"></i></span>
                                    <div class="form-control border-2 border-start-0 py-2 border-light-2 fw-bold">
                                        {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Área Descriptiva (Editable) -->
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

                <!-- Sección: Currículo del Curso -->
                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                    <x-taller.card-header 
                        icon="fas fa-list-ul" 
                        title="Estructura de Contenidos" 
                        subtitle="Configura los módulos y el sistema de evaluación promediado."
                    >
                        <!-- Contador de Ponderación Dinámico -->
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

                <!-- Footer de Acciones Fijo/Sustentado -->
                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-6">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between">
                                <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <div class="ponderacion-alerts-container me-3 text-end">
                                        <div id="alert-100" class="text-success small fw-bold" style="display: none;">
                                            <i class="fas fa-check-double me-1"></i> Lista para guardar
                                        </div>
                                        <div id="alert-insufficient" class="text-warning small fw-bold" style="display: none;">
                                            <i class="fas fa-clock me-1"></i> Falta carga
                                        </div>
                                        <div id="alert-over" class="text-danger small fw-bold" style="display: none;">
                                            <i class="fas fa-times-circle me-1"></i> Exceso de carga
                                        </div>
                                    </div>
                                    <button type="submit" id="btn-save-curso" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg" style="background: #1e3a8a;">
                                        <i class="fas fa-save me-2 text-white"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
