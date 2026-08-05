@extends('layouts.kaiadmin-login')

@section('title', 'Asistencia No Disponible')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient-secondary py-5" style="background: linear-gradient(135deg, #475569 0%, #94a3b8 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0" style="border-radius: 1.5rem;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Asistencia No Disponible</h3>
                        <p class="text-muted mb-4">{{ $mensaje }}</p>

                        @if(isset($curso))
                        <a href="{{ route('taller.cursos.show', $curso->crypt_id) }}" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i> Volver al curso
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
