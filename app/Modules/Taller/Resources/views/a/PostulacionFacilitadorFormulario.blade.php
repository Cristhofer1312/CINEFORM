@extends('layouts.kaiadmin-menu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Alertas --}}
        @if(session('error'))
            <x-taller.alert type="danger" title="Atención">
                {{ session('error') }}
            </x-taller.alert>
        @endif
        @if ($errors->any())
            <x-taller.alert type="danger" title="Errores de validación">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-taller.alert>
        @endif

        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-2">Postularme como Facilitador</h2>
            <p class="text-muted"><i class="fas fa-chalkboard-teacher me-1 text-primary"></i> Completa los requisitos solicitados para tu postulación</p>
        </div>

        <form action="{{ route('taller.postulacion-facilitador.postular') }}" method="POST" enctype="multipart/form-data" id="form-postulacion">
            @csrf

            {{-- Recursos Informativos --}}
            @php
                $recursos = $requisitos->where('tipo', 'recurso');
            @endphp
            @if($recursos->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden border-top border-4 border-success">
                <div class="card-header bg-success-soft border-0 py-3">
                    <h6 class="mb-0 fw-bold text-success text-uppercase"><i class="fas fa-info-circle me-2"></i> Información Importante</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    <ul class="list-unstyled mb-0">
                        @foreach($recursos as $req)
                            <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <h6 class="fw-bold text-dark mb-1">{{ $req->titulo }}</h6>
                                @if($req->descripcion)
                                    <p class="text-muted small mb-0">{{ $req->descripcion }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Preguntas y Documentos --}}
            @php
                $preguntasDocs = $requisitos->whereIn('tipo', ['pregunta', 'documento']);
            @endphp

            @if($preguntasDocs->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-primary text-uppercase"><i class="fas fa-clipboard-list me-2"></i> Requisitos Solicitados</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    @foreach($preguntasDocs as $req)
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">
                                {{ $req->titulo }}
                                @if($req->obligatorio) <span class="text-danger">*</span> @endif
                            </label>

                            @if($req->descripcion)
                                <small class="d-block text-muted mb-2">{{ $req->descripcion }}</small>
                            @endif

                            @if($req->tipo === 'pregunta')
                                <textarea name="req_{{ $req->id_requisito_facilitador }}" class="form-control req-input" rows="3" placeholder="Escribe tu respuesta aquí..." {{ $req->obligatorio ? 'required' : '' }}></textarea>
                            @elseif($req->tipo === 'documento')
                                <input type="file" name="req_{{ $req->id_requisito_facilitador }}" class="form-control req-input" accept=".pdf,.png,.jpg,.jpeg" {{ $req->obligatorio ? 'required' : '' }}>
                                <small class="text-muted mt-1"><i class="fas fa-file-pdf me-1"></i> Formatos aceptados: PDF, JPG, PNG. Tamaño máximo: 5MB.</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="alert alert-info border-0 rounded-4 shadow-sm p-4 text-center mb-5">
                <i class="fas fa-check-circle fa-2x mb-3 text-info"></i>
                <h5 class="fw-bold">No hay requisitos adicionales</h5>
                <p class="mb-0">Solo necesitas confirmar tu postulación haciendo clic en el botón de abajo.</p>
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('taller.postulacion-facilitador.landing') }}" class="btn btn-light fw-bold px-4 rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i> Volver
                </a>
                <button type="submit" class="btn btn-primary fw-bold px-5 py-3 rounded-pill shadow-lg" id="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i> Enviar Postulación
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('form-postulacion').addEventListener('submit', function(e) {
        let valid = true;
        const requiredInputs = this.querySelectorAll('[required]');

        requiredInputs.forEach(input => {
            if (input.type === 'file') {
                if (!input.files.length) {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            } else {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });

        if (!valid) {
            e.preventDefault();
            Swal.fire('Atención', 'Por favor, completa todos los campos obligatorios antes de continuar.', 'warning');
        } else {
            const btn = document.getElementById('btn-submit');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';
            btn.disabled = true;

            Swal.fire({
                title: 'Enviando postulación...',
                text: 'Por favor espera mientras procesamos tu solicitud.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        }
    });
</script>
@endpush
@endsection