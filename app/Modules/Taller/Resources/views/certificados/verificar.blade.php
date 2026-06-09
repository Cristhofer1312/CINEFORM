@extends('taller::layouts.master')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Verificación de Certificado</h4>
                </div>
                <div class="card-body text-center">
                    @if(isset($error))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> {{ $error }}
                        </div>
                    @elseif($valido)
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h2 class="text-success mt-2">Certificado Auténtico</h2>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="bg-light w-30">Participante</th>
                                        <td>{{ $persona->nombre_completo }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Curso</th>
                                        <td>{{ $curso->nombre }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Código de Validación</th>
                                        <td><code>{{ $curso->codigo }}-{{ $persona->dni }}</code></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Fecha de Emisión</th>
                                        <td>{{ $fecha }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            Este documento ha sido emitido y validado por el Sistema CINEFORM.
                        </div>
                    @else
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            <h2 class="text-danger mt-2">Certificado No Válido</h2>
                        </div>
                        <p>Los datos proporcionados no coinciden con nuestros registros o el curso aún no ha finalizado.</p>
                        <a href="/" class="btn btn-primary">Volver al Inicio</a>
                    @endif
                </div>
                <div class="card-footer text-muted text-center">
                    &copy; {{ date('Y') }} CINEFORM - Sistema de Gestión de Formación
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Incluir Bootstrap CSS si el layout master no lo tiene --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection
