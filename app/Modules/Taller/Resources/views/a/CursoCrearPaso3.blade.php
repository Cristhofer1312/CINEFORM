@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Programas</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Paso 3: Temario</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">¿Cómo se estructura el contenido?</h2>
                    <p class="text-muted small mb-0 d-flex align-items-center flex-wrap gap-2">
                        <span><i class="fas fa-list-ul me-1 text-primary"></i> Bloques de clase.
                        @if(! empty($rango['inicio']))
                            Del {{ \Carbon\Carbon::parse($rango['inicio'])->format('d/m/Y') }} al {{ ! empty($rango['fin']) ? \Carbon\Carbon::parse($rango['fin'])->format('d/m/Y') : '—' }}.
                        @endif
                        Las evaluaciones las define el facilitador después.</span>
                        @if(!empty($borrador->updated_at))
                            <span class="badge bg-light text-muted border py-1 px-2 rounded-pill small">
                                <i class="fas fa-check-circle text-success me-1"></i> Borrador guardado {{ $borrador->updated_at->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </p>
                </div>
                <div class="text-end d-flex gap-2">
                    <a href="{{ route('taller.cursos.wizard.paso2.create') }}" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Atrás
                    </a>
                    <button type="submit" form="form-paso3" name="accion" value="salir" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        <i class="fas fa-save me-2 text-primary"></i> Guardar borrador y salir
                    </button>
                </div>
            </div>

            <x-taller.wizard-steps :actual="$pasoActual ?? 3" :completados="$completados ?? []" />

            <form id="form-paso3" action="{{ route('taller.cursos.wizard.paso3.store') }}" method="POST">
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

                <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden">
                    <x-taller.card-header icon="fas fa-list-ul" title="Estructura de contenidos"
                        subtitle="Bloques de clase con fecha y horario. Sin evaluaciones en este paso." />
                    <div class="card-body p-4 bg-light bg-opacity-25">
                        <div id="contenidos-container" class="row row-cols-1 g-4"
                            data-rango-inicio="{{ $rango['inicio'] ?? '' }}" data-rango-fin="{{ $rango['fin'] ?? '' }}">
                            @foreach(old('contenidos', $contenidos ?? []) as $index => $contenido)
                                <x-taller.contenido-simple :index="$index" :contenido="$contenido" :rango-inicio="$rango['inicio'] ?? null" :rango-fin="$rango['fin'] ?? null" />
                            @endforeach
                        </div>

                        <div class="text-center mt-5">
                            <button type="button" id="agregar-contenido"
                                class="btn btn-outline-dark border-2 px-5 py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-plus-circle me-2"></i> Añadir Bloque
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-5 pb-5">
                    <div class="col-lg-7">
                        <div class="card border shadow rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <button type="submit" name="accion" value="salir" class="btn btn-outline-secondary px-4 py-3 rounded-pill fw-bold">
                                    <i class="fas fa-save me-2"></i> Guardar borrador y salir
                                </button>
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
    <script src="{{ asset('js/modules/taller/wizard-paso3.js') }}"></script>
    @endpush
@endsection
