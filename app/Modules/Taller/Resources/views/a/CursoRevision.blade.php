@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="{{ route('taller.cursos.index') }}" class="text-decoration-none text-muted">Programas</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Paso 5: Revisión</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Confirma antes de crear</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-check-circle me-1 text-primary"></i> Resumen de lo ingresado en los pasos 1 al 4.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('taller.cursos.wizard.requisitos.create') }}" class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> Atrás
                    </a>
                </div>
            </div>

            <x-taller.wizard-steps :actual="$pasoActual ?? 5" :completados="$completados ?? []" />

            @if ($errors->any())
                <x-taller.alert type="danger" title="No se pudo crear">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-taller.alert>
            @endif

            @if(($estadoCertificado ?? '') !== 'CONFIGURADO')
                <x-taller.alert type="warning" title="Certificado incompleto">
                    @if(($estadoCertificado ?? '') === 'SIN_PLANTILLA')
                        El curso nacerá <strong>SIN PLANTILLA</strong>: se usará el diseño por defecto del sistema. Puedes completarlo desde la ficha con "Ajustar Certificado".
                    @else
                        El curso nacerá con estado <strong>FALTA FIRMA</strong>: el certificado saldrá sin firma. Puedes completarlo desde la ficha con "Ajustar Certificado".
                    @endif
                </x-taller.alert>
            @endif

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                        <x-taller.card-header icon="fas fa-tag" title="1 · Identidad" />
                        <div class="card-body p-4">
                            <dl class="mb-0">
                                <dt class="small text-muted">NOMBRE</dt>
                                <dd class="fw-bold">{{ $paso1['nombre'] ?? '—' }}</dd>
                                <dt class="small text-muted">NIVEL</dt>
                                <dd>{{ $paso1['nivel'] ?? '—' }}</dd>
                                <dt class="small text-muted">PÚBLICO OBJETIVO</dt>
                                <dd>
                                    {{ $publicoObjetivo ?? 'General' }}
                                    @if(!empty($esParaMenores))
                                        <span class="badge bg-warning text-dark ms-2">Carta de permiso requerida</span>
                                    @endif
                                </dd>
                                <dt class="small text-muted">CÓDIGO PREVISTO</dt>
                                <dd class="font-monospace fw-bold">{{ $codigoPreview ?? '—' }}</dd>
                                <dt class="small text-muted">FOTO</dt>
                                <dd>{{ ! empty($hayFoto) ? 'Cargada' : 'Sin foto' }}</dd>
                            </dl>
                            <a href="{{ route('taller.cursos.wizard.paso1.create') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Editar paso 1</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                        <x-taller.card-header icon="fas fa-calendar-alt" title="2 · Logística" />
                        <div class="card-body p-4">
                            <dl class="mb-0">
                                <dt class="small text-muted">FACILITADOR</dt>
                                <dd class="fw-bold">
                                    @if($facilitador)
                                        {{ $facilitador->primer_nombre }} {{ $facilitador->primer_apellido }} ({{ $facilitador->dni }})
                                    @else
                                        —
                                    @endif
                                </dd>
                                <dt class="small text-muted">FECHAS</dt>
                                <dd>{{ $paso2['fecha_inicio'] ?? '—' }} → {{ $paso2['fecha_fin'] ?? '—' }} (Trim. {{ $paso2['trimestre'] ?? '—' }} / {{ $paso2['anio'] ?? '—' }})</dd>
                                <dt class="small text-muted">CUPOS · SEDE</dt>
                                <dd>{{ $paso2['cantidad_cupos'] ?? '—' }} cupos · {{ ! empty($paso2['es_nacional']) ? 'Nacional' : (count($paso2['localidades'] ?? []) . ' estados') }} · {{ $paso2['direccion_corta'] ?? '—' }}</dd>
                            </dl>
                            <a href="{{ route('taller.cursos.wizard.paso2.create') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Editar paso 2</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                        <x-taller.card-header icon="fas fa-list-ul" title="3 · Temario" badge="{{ count($contenidos ?? []) }}" />
                        <div class="card-body p-4">
                            @if(empty($contenidos))
                                <p class="text-muted mb-2">Sin bloques. El facilitador los definirá después.</p>
                            @else
                                <ol class="mb-2">
                                    @foreach($contenidos as $bloque)
                                        <li><strong>{{ $bloque['titulo'] ?? 'Bloque' }}</strong>
                                            <span class="text-muted small">{{ $bloque['fecha_contenido'] ?? '' }} {{ $bloque['hora_inicio'] ?? '' }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                            <a href="{{ route('taller.cursos.wizard.paso3.create') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Editar paso 3</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                        <x-taller.card-header icon="fas fa-award" title="4 · Certificado" badge="{{ $estadoCertificado ?? '' }}" />
                        <div class="card-body p-4">
                            <p class="mb-2">Estado: <strong>{{ $estadoCertificado ?? '—' }}</strong></p>
                            <a href="{{ route('taller.cursos.wizard.plantilla.create') }}" class="btn btn-sm btn-outline-primary rounded-pill">Editar paso 4</a>
                        </div>
                    </div>
                    <div class="card border-0 shadow-card rounded-4 overflow-hidden mt-4">
                        <x-taller.card-header icon="fas fa-list-check" title="5 · Requisitos" badge="{{ count($requisitos ?? []) }}" />
                        <div class="card-body p-4">
                            @if(empty($requisitos))
                                <p class="text-muted mb-2">Sin requisitos.</p>
                            @else
                                <ul class="mb-2">
                                    @foreach($requisitos as $req)
                                        <li><strong>[{{ $req['tipo'] ?? '' }}]</strong> {{ $req['titulo'] ?? '' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <a href="{{ route('taller.cursos.wizard.requisitos.create') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Editar requisitos</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-5 pb-5">
                <div class="col-lg-8">
                    <div class="card border shadow rounded-pill overflow-hidden bg-white">
                        <div class="card-body p-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <a href="{{ route('taller.cursos.index') }}" class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                <i class="fas fa-save me-2"></i> Guardar borrador
                            </a>
                            <form action="{{ route('taller.cursos.wizard.finalizar') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg">
                                    <i class="fas fa-rocket me-2"></i> Finalizar y crear curso
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
