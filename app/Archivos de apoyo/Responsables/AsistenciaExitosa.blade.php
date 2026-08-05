@extends('layouts.kaiadmin-login')

@section('title', 'Asistencia Registrada')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient-success py-5" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0" style="border-radius: 1.5rem;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-success mb-2">¡Asistencia Registrada!</h3>
                        <p class="text-muted mb-2">Tu asistencia ha sido registrada exitosamente.</p>

                        <div class="card bg-light border-0 rounded-3 my-4 text-start p-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-graduation-cap text-success me-2"></i>
                                <span class="fw-bold">{{ $curso->nombre }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day text-success me-2"></i>
                                <span>{{ $contenido->titulo }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-success me-2"></i>
                                <small class="text-muted">Registrado el {{ now()->format('d/m/Y a las H:i') }}</small>
                            </div>
                        </div>

                        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-success btn-lg rounded-pill fw-bold px-5 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i> Volver al curso
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
