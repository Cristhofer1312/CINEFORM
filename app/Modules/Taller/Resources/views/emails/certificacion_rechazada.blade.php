@extends('layouts.email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 80px; height: 80px;">
                            <i class="fas fa-times-circle text-danger" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Certificación con Observaciones</h2>
                    <p class="text-muted mb-4">Estimado(a) <strong>{{ $inscripcion->persona->primer_nombre }}</strong>,</p>
                    <p class="text-muted">Tu certificación en el programa <strong>{{ $inscripcion->curso->nombre }}</strong> tiene <strong class="text-danger">OBSERVACIONES</strong>.</p>
                    <div class="alert alert-warning text-start mt-3" style="border-radius: 0.75rem;">
                        <p class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Motivo:</p>
                        <p class="mb-0">{{ $inscripcion->certificado_motivo_denegacion }}</p>
                    </div>
                    <p class="text-muted mt-3">Por favor, contacta al facilitador o coordinador para más información.</p>
                    <div class="mt-4 mb-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-bold">
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
