@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-white border-0 shadow-card overflow-hidden" style="border-radius: 1rem;">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-primary-soft text-primary me-2 px-3 py-1 rounded-pill"
                                    style="font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-microscope me-1"></i> Evaluación
                                </span>
                                <small class="text-muted text-uppercase fw-bold"
                                    style="letter-spacing: 1px; font-size: 0.75rem;">{{ $curso->nombre }}</small>
                            </div>
                            <h3 class="fw-bold text-dark mb-0">{{ $contenido->titulo }}</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('taller.cursos.contenido', ['curso' => $curso->id_curso, 'contenido_id' => $contenido->id_contenido_curso]) }}"
                                class="btn btn-outline-light text-dark border-0 bg-gray-100 hover-lift">
                                <i class="fas fa-arrow-left me-2"></i> Volver al contenido
                            </a>
                            <a href="{{ route('taller.cursos.show', $curso->id_curso) }}"
                                class="btn btn-outline-light text-dark border-0 bg-gray-100 hover-lift" style="background-color: #f8fafc;">
                                <i class="fas fa-arrow-left me-2"></i> Volver al curso
                            </a>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

                        <!-- Search Bar Centered -->
                        <div class="row justify-content-center mt-4 mb-4">
                            <div class="col-12">
                                <form action="{{ route('taller.calificaciones.index', ['curso' => $curso->id_curso, 'contenido' => $contenido->id_contenido_curso]) }}" method="GET">
                                    <div class="input-group input-group-lg shadow-sm border bg-white rounded-pill overflow-hidden search-box-focus">
                                        <span class="input-group-text border-0 bg-transparent ps-4 text-muted"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search"
                                            class="form-control border-0 bg-transparent shadow-none ps-2 fs-6"
                                            placeholder="Buscar participante por nombre o cédula..."
                                            value="{{ request('search') }}">
                                        @if(request('search'))
                                            <a href="{{ route('taller.calificaciones.index', ['curso' => $curso->id_curso, 'contenido' => $contenido->id_contenido_curso]) }}" 
                                               class="btn btn-link text-muted px-3 border-0 shadow-none"><i class="fas fa-times-circle"></i></a>
                                        @endif
                                        <button type="submit" class="btn btn-primary px-4 rounded-pill m-1 fw-bold shadow-sm" style="background-color: #1e3a8a;">Buscar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                <form action="{{ route('taller.calificaciones.store', ['curso' => $curso->id_curso, 'contenido' => $contenido->id_contenido_curso]) }}" method="POST">
                    @csrf
                    <!-- Lista Zen-Minimalista -->
                    <div class="mt-4 bg-white rounded-3 border-top border-bottom">
                        <div class="d-none d-lg-flex bg-light py-2 px-4 border-bottom text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">
                            <div style="width: 30%;">Estudiante</div>
                            <div style="width: 15%;" class="text-center">Calificación (0-100)</div>
                            <div style="width: 55%;" class="ps-4">Observaciones del Facilitador</div>
                        </div>

                    @forelse($estudiantes as $estudiante)
                        <div class="zen-row py-4 px-4 border-bottom transition-all">
                            <div class="row align-items-center g-0">
                                <!-- Nombre -->
                                <div class="col-lg-4 col-md-5 mb-3 mb-lg-0">
                                    <h6 class="fw-bold text-dark mb-1">{{ $estudiante->primer_nombre ?? '' }} {{ $estudiante->primer_apellido ?? '' }}</h6>
                                    <span class="text-muted small">C.I. {{ $estudiante->dni ?? 'N/A' }}</span>
                                </div>

                                <!-- Calificación -->
                                <div class="col-lg-2 col-md-3 mb-3 mb-lg-0 text-center px-lg-4">
                                    <input type="number" 
                                        name="calificaciones[{{ $estudiante->id_persona }}][nota]" 
                                        class="form-control text-center py-2 fw-bold border bg-light shadow-none zen-input-grade"
                                        value="{{ $estudiante->calificacion }}" min="0" max="100" step="0.01" placeholder="--"
                                        style="font-size: 1.1rem; border-radius: 8px;">
                                </div>

                                <!-- Feedback -->
                                <div class="col-lg-6 col-md-4">
                                    <input type="text" 
                                        name="calificaciones[{{ $estudiante->id_persona }}][observacion]" 
                                        class="form-control border-0 border-bottom bg-transparent rounded-0 shadow-none ps-lg-4 py-2 zen-input-feedback"
                                        value="{{ $estudiante->observacion }}"
                                        placeholder="Escribir feedback opcional..."
                                        style="font-size: 0.95rem;">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-5 text-center text-muted">
                            <i class="fas fa-user-slash mb-3 opacity-25" style="font-size: 3rem;"></i>
                            <p>No hay alumnos registrados para calificar.</p>
                        </div>
                    @endforelse
                </div>

                @if(count($estudiantes) > 0)
                    <div class="py-5 text-end">
                        <button type="submit" class="btn btn-dark btn-lg rounded-3 px-5 py-3 fw-bold border-0 shadow-sm" style="background-color: #1e293b;">
                            Guardar Cambios y Finalizar
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
</div>

@push('styles')
        <style>
            body { background-color: #f8fafc; }
            .card { border-radius: 16px; border: 1px solid #eef2f6; }
            
            .search-box-focus:focus-within {
                border-color: #1e3a8a !important;
                background-color: #fff !important;
                box-shadow: 0 4px 12px rgba(30, 58, 138, 0.08);
            }

            .student-row-refined:hover {
                background-color: #fbfdff !important;
            }

            .avatar-box {
                transition: transform 0.2s ease;
            }

            .grade-input-container {
                border: 2px solid #f1f4f8;
                transition: all 0.2s ease;
            }

            .grade-input-container:focus-within {
                border-color: #1e3a8a;
                background-color: #fff !important;
                box-shadow: 0 4px 12px rgba(30, 58, 138, 0.08);
            }

            .feedback-box-minimal {
                border: 1px solid #f1f4f8;
                transition: all 0.2s ease;
            }

            .feedback-box-minimal:focus-within {
                border-color: #1e3a8a;
                background-color: #fff !important;
                box-shadow: 0 4px 12px rgba(30, 58, 138, 0.05);
            }

            /* Quitar flechas de input number */
            .no-spinners::-webkit-inner-spin-button, 
            .no-spinners::-webkit-outer-spin-button { 
                -webkit-appearance: none; 
                margin: 0; 
            }
            .no-spinners {
                -moz-appearance: textfield;
            }

            .breadcrumb-item + .breadcrumb-item::before {
                content: "›";
                color: #cbd5e0;
                font-weight: bold;
            }

            .zen-row:hover { background-color: #f8fafc; }
            .zen-row { border-bottom: 1px solid #f1f5f9; }

            .zen-input-grade:focus {
                background-color: #fff !important;
                border-color: #1e293b !important;
                color: #0f172a !important;
            }

            .zen-input-feedback:focus {
                border-bottom-color: #1e293b !important;
                color: #0f172a !important;
            }

            .badge-minimal {
                background-color: #f1f5f9;
                color: #475569;
                font-weight: 600;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('input', function (e) {
                if (e.target.matches('.zen-input-grade')) {
                    const input = e.target;
                    let val = parseFloat(input.value);
                    
                    if (val > 100) { input.value = 100; val = 100; }
                    if (val < 0) { input.value = 0; val = 0; }
                }
            });
        </script>
    @endpush
@endsection