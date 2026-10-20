@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Programas</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Paso 1: Identidad</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">¿De qué trata el curso?</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-tag me-1 text-primary"></i> Nombre, portada, nivel y clasificación PROCINEC.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.index') }}" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        <i class="fas fa-save me-2 text-primary"></i> Guardar borrador y salir
                    </a>
                </div>
            </div>

            <x-taller.wizard-steps :actual="$pasoActual ?? 1" :completados="$completados ?? []" />

            <form action="{{ route('taller.cursos.wizard.paso1.store') }}" method="POST" enctype="multipart/form-data">
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
                    <x-taller.card-header icon="fas fa-tag" title="Identidad y clasificación" />
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <x-taller.input label="NOMBRE DEL CURSO" icon="fas fa-heading" name="nombre"
                                    :value="old('nombre', $paso1['nombre'] ?? '')"
                                    placeholder="Ej: Especialización en Guion Cinematográfico" required />
                            </div>
                            <div class="col-md-4">
                                <x-taller.select label="NIVEL" icon="fas fa-layer-group" name="nivel">
                                    @foreach(['Básico', 'Medio', 'Avanzado'] as $nivel)
                                        <option value="{{ $nivel }}" {{ old('nivel', $paso1['nivel'] ?? '') == $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                    @endforeach
                                </x-taller.select>
                            </div>

                            <div class="col-md-12">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-image me-2 opacity-50 text-primary"></i> FOTO DE PRESENTACIÓN (OPCIONAL)
                                </label>
                                <div class="bg-white border-2 rounded-3 p-3 border-light-2 shadow-xs">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="position-relative flex-shrink-0">
                                            <img id="foto-preview" src="" alt="Vista previa"
                                                 class="rounded-3 border" style="width: 180px; height: 110px; object-fit: cover; display: none;">
                                            <div id="foto-placeholder" class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 180px; height: 110px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" class="form-control form-control-sm" name="foto" id="foto-input"
                                                   accept="image/png,image/jpeg" onchange="previewFoto(this)">
                                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i> JPG o PNG, máx. 2 MB. Se muestra en el catálogo.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <x-taller.select label="ACTIVIDAD FORMATIVA" icon="fas fa-graduation-cap" name="id_actividad_formativa" required>
                                    @foreach($actividades as $act)
                                        <option value="{{ $act->id_actividad_formativa }}" data-abreviatura="{{ $act->abreviatura }}" {{ (string) old('id_actividad_formativa', $paso1['id_actividad_formativa'] ?? '') === (string) $act->id_actividad_formativa ? 'selected' : '' }}>
                                            {{ $act->nombre }} ({{ $act->abreviatura }})
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-6">
                                <x-taller.select label="ASPECTO CINEMATOGRÁFICO" icon="fas fa-film" name="id_aspecto">
                                    <option value="">— Sin aspecto —</option>
                                    @foreach($aspectos as $asp)
                                        <option value="{{ $asp->id_aspecto }}" data-abreviatura="{{ $asp->abreviatura }}" {{ (string) old('id_aspecto', $paso1['id_aspecto'] ?? '') === (string) $asp->id_aspecto ? 'selected' : '' }}>
                                            {{ $asp->nombre }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>
                            <div class="col-md-6">
                                <x-taller.select label="PÚBLICO OBJETIVO" icon="fas fa-users" name="id_modalidad_especial">
                                    <option value="">— General —</option>
                                    @foreach($modalidadesEspeciales as $me)
                                        <option value="{{ $me->id_modalidad_especial }}" data-abreviatura="{{ $me->abreviatura }}" data-es-menor="{{ $me->es_menor ? '1' : '0' }}" {{ (string) old('id_modalidad_especial', $paso1['id_modalidad_especial'] ?? '') === (string) $me->id_modalidad_especial ? 'selected' : '' }}>
                                            {{ $me->nombre }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1 text-primary"></i>
                                    Si seleccionas <strong>Niño</strong> o <strong>Adolescente</strong>, la inscripción pedirá la
                                    <strong>Carta de permiso (Autorización de menores)</strong>.
                                </small>
                            </div>
                            <div class="col-md-6">
                                <x-taller.select label="MODALIDAD" icon="fas fa-laptop-house" name="id_modalidad" required>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id_modalidad }}" data-abreviatura="{{ $mod->abreviatura }}" {{ (string) old('id_modalidad', $paso1['id_modalidad'] ?? '') === (string) $mod->id_modalidad ? 'selected' : '' }}>
                                            {{ $mod->nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </x-taller.select>
                            </div>

                            <div class="col-12 mt-2">
                                <label for="descripcion" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-align-left me-2 opacity-50"></i> RESUMEN O SÍNTESIS DEL PROGRAMA
                                </label>
                                <textarea class="form-control border-2 shadow-none rounded-4 bg-white p-3 border-light-2" id="descripcion" name="descripcion"
                                    rows="4" placeholder="Breve descripción del programa académico...">{{ old('descripcion', $paso1['descripcion'] ?? '') }}</textarea>
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

    @push('scripts')
    <script>
        function previewFoto(input) {
            const file = input.files && input.files[0];
            const preview = document.getElementById('foto-preview');
            const placeholder = document.getElementById('foto-placeholder');
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
            }
        }
    </script>
    @endpush
@endsection
