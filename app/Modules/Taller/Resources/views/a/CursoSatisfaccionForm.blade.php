@extends('layouts.kaiadmin-menu')

@section('title', 'Encuesta de Satisfacción - ' . $curso->nombre)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-clipboard-check fa-2x"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                <h2 class="fw-bold text-dark mb-0">Encuesta de Satisfacción</h2>
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">4 preguntas</span>
                            </div>
                            <h5 class="text-primary fw-bold mb-2">{{ $curso->nombre }}</h5>
                            <p class="text-muted mb-0">Responde las 4 preguntas de esta encuesta. Es obligatoria para descargar tu certificado.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-xs rounded-4">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-xs rounded-4">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-xs rounded-4">
                    <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Revisa los siguientes errores:</h6>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('taller.cursos.satisfaccion.store', $curso->crypt_id) }}" method="POST">
                @csrf

                <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-list-check me-2 text-primary opacity-50"></i> Tu valoración
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @foreach($preguntas as $campo => $titulo)
                            <div class="{{ $loop->last ? 'mb-0' : 'mb-4' }}">
                                <label for="{{ $campo }}" class="form-label fw-bold text-dark">
                                    <span class="badge bg-primary text-white rounded-circle me-2">{{ $loop->iteration }}</span>{{ $titulo }}
                                </label>
                                <select name="{{ $campo }}" id="{{ $campo }}" class="form-select form-select-lg shadow-xs rounded-3 @error($campo) is-invalid @enderror" required>
                                    <option value="" {{ old($campo, '') === '' ? 'selected' : '' }} disabled> Aún sin responder</option>
                                    @foreach($escala as $valor => $etiqueta)
                                        <option value="{{ $valor }}" {{ (string) old($campo, '') === (string) $valor ? 'selected' : '' }}>{{ $valor }} — {{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error($campo)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-light bg-opacity-50 border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-bottom-4">
                        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-outline-secondary rounded-pill fw-bold px-4">
                            <i class="fas fa-arrow-left me-2"></i> Volver al curso
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm hvr-push">
                            <i class="fas fa-paper-plane me-2"></i> Enviar encuesta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
