@extends('layouts.email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width: 80px; height: 80px;">
                            <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Certificación Aprobada</h2>
                    <p class="text-muted mb-4">¡Felicidades, <strong>{{ $inscripcion->persona->primer_nombre }}</strong>!</p>
                    <p class="text-muted">Tu certificación en el programa <strong>{{ $inscripcion->curso->nombre }}</strong> ha sido <strong class="text-success">APROBADA</strong>.</p>
                    <p class="text-muted">Puedes descargar tu certificado desde tu panel de "Mis Cursos".</p>
                    <div class="mt-4 mb-3">
                        <a href="{{ route('login') }}" class="btn btn-success btn-lg rounded-pill px-4 fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </a>
                    </div>
                    <hr class="my-4">
                    <p class="text-muted small mb-0">Atentamente,</p>
                    <p class="fw-bold text-dark mb-0">El equipo de CINEFORM</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
