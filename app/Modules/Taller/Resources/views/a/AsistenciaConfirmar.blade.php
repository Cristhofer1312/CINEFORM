@extends('layouts.kaiadmin-login')

@section('title', 'Confirmar Asistencia')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient-primary py-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0" style="border-radius: 1.5rem;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-check fa-2x text-primary"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Confirmar Asistencia</h3>
                        <p class="text-muted mb-4">Curso: <strong>{{ $curso->nombre }}</strong></p>

                        <div class="card bg-light border-0 rounded-3 mb-4 text-start p-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day text-primary me-2"></i>
                                <span class="fw-bold">{{ $contenido->fecha_contenido->format('d/m/Y') }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-book-open text-primary me-2"></i>
                                <span>{{ $contenido->titulo }}</span>
                            </div>
                        </div>

                        @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                        @endif

                        @if(session('info'))
                        <div class="alert alert-info" role="alert">
                            {{ session('info') }}
                        </div>
                        @endif

                        <form action="{{ route('taller.asistencia.confirmar-marcar', ['curso' => $curso->crypt_id, 'token' => $tokenRecord->token]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold py-3 shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Sí, registrar mi asistencia
                            </button>
                        </form>

                        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-link text-muted mt-3">
                            <i class="fas fa-arrow-left me-1"></i> Volver al curso
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
