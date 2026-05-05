@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">

            {{-- Alertas Actividades --}}
            @if(session('success_actividad'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show">
                    <div class="icon-shape icon-sm bg-success-light text-success rounded-circle me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">¡Listo!</h6><small>{{ session('success_actividad') }}</small>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Alertas Aspectos --}}
            @if(session('success_aspecto'))
                <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show">
                    <div class="icon-shape icon-sm bg-info-light text-info rounded-circle me-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">¡Listo!</h6><small>{{ session('success_aspecto') }}</small>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size:0.8rem;">
                            <li class="breadcrumb-item"><a href="#"
                                    class="text-decoration-none text-muted">Administración</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Agregar Tipo de Actividad</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">Tipos de Actividad</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-layer-group me-1 text-primary"></i> Gestiona las
                        actividades formativas y aspectos de formación disponibles.</p>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
            SECCIÓN 1: ACTIVIDADES FORMATIVAS
            ════════════════════════════════════════════════════════════ --}}
            <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4 border-primary">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm">
                            <i class="fas fa-graduation-cap fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Actividades Formativas</h5>
                            <small class="text-muted">Tipo de actividad educativa (Taller, Foro, Conferencia…)</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalCrearActividad">
                        <i class="fas fa-plus me-2"></i> Agregar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-muted fw-bold text-center">#</th>
                                    <th class="text-muted small fw-bold">NOMBRE</th>
                                    <th class="text-muted small fw-bold text-center">ABREV.</th>
                                    <th class="text-muted small fw-bold text-center">CURSOS</th>
                                    <th class="text-muted small fw-bold text-center">STATUS</th>
                                    <th class="text-muted small fw-bold text-center pe-4">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actividades as $act)
                                    <tr class="{{ $act->status !== 'Activo' ? 'opacity-50' : '' }} catalogo-row">
                                        <td class="text-muted text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $act->nombre }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-soft text-primary fw-bold rounded-pill px-3 py-1">
                                                {{ $act->abreviatura }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $act->cursos_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($act->status === 'Activo')
                                                <span class="badge bg-success rounded-pill px-3">Activo</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            {{-- Editar --}}
                                            <button type="button"
                                                class="btn btn-sm btn-light border shadow-xs rounded-3 me-1 btn-editar-actividad"
                                                data-id="{{ $act->id_actividad_formativa }}" data-nombre="{{ $act->nombre }}"
                                                data-abreviatura="{{ $act->abreviatura }}"
                                                data-url="{{ route('taller.catalogos.actividades.update', $act->id_actividad_formativa) }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarActividad" title="Editar">
                                                <i class="fas fa-pen text-primary"></i>
                                            </button>
                                            {{-- Toggle --}}
                                            <form method="POST"
                                                action="{{ route('taller.catalogos.actividades.toggle', $act->id_actividad_formativa) }}"
                                                class="d-inline form-toggle-actividad" data-nombre="{{ $act->nombre }}"
                                                data-status="{{ $act->status }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $act->status === 'Activo' ? 'btn-outline-danger' : 'btn-outline-success' }} border shadow-xs rounded-3"
                                                    title="{{ $act->status === 'Activo' ? 'Desactivar' : 'Activar' }}">
                                                    <i
                                                        class="fas {{ $act->status === 'Activo' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-graduation-cap fs-1 opacity-25 d-block mb-3"></i>
                                            No hay actividades formativas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
            SECCIÓN 2: ASPECTOS DE FORMACIÓN
            ════════════════════════════════════════════════════════════ --}}
            <div class="card border-0 shadow-card rounded-4 mb-5 overflow-hidden border-top border-4"
                style="border-color: #0891b2 !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-box text-white rounded-3 me-3 p-2 shadow-sm" style="background:#0891b2;">
                            <i class="fas fa-film fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Aspectos de Formación</h5>
                            <small class="text-muted">Área cinematográfica del curso (Guion, Dirección, Sonido…)</small>
                        </div>
                    </div>
                    <button type="button" class="btn rounded-pill px-4 shadow-sm fw-bold text-white"
                        style="background:#0891b2;" data-bs-toggle="modal" data-bs-target="#modalCrearAspecto">
                        <i class="fas fa-plus me-2"></i> Agregar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-muted small fw-bold text-center">#</th>
                                    <th class="text-muted small fw-bold">NOMBRE</th>
                                    <th class="text-muted small fw-bold text-center">ABREV.</th>
                                    <th class="text-muted small fw-bold text-center">CURSOS</th>
                                    <th class="text-muted small fw-bold text-center">STATUS</th>
                                    <th class="text-muted small fw-bold text-center pe-4">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($aspectos as $asp)
                                    <tr class="{{ $asp->status !== 'Activo' ? 'opacity-50' : '' }} catalogo-row">
                                        <td class="text-muted text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $asp->nombre }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge fw-bold rounded-pill px-3 py-1"
                                                style="background:rgba(8,145,178,0.12); color:#0891b2;">
                                                {{ $asp->abreviatura }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $asp->cursos_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($asp->status === 'Activo')
                                                <span class="badge bg-success rounded-pill px-3">Activo</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            {{-- Editar --}}
                                            <button type="button"
                                                class="btn btn-sm btn-light border shadow-xs rounded-3 me-1 btn-editar-aspecto"
                                                data-id="{{ $asp->id_aspecto }}" data-nombre="{{ $asp->nombre }}"
                                                data-abreviatura="{{ $asp->abreviatura }}"
                                                data-url="{{ route('taller.catalogos.aspectos.update', $asp->id_aspecto) }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarAspecto" title="Editar">
                                                <i class="fas fa-pen" style="color:#0891b2;"></i>
                                            </button>
                                            {{-- Toggle --}}
                                            <form method="POST"
                                                action="{{ route('taller.catalogos.aspectos.toggle', $asp->id_aspecto) }}"
                                                class="d-inline form-toggle-aspecto" data-nombre="{{ $asp->nombre }}"
                                                data-status="{{ $asp->status }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $asp->status === 'Activo' ? 'btn-outline-danger' : 'btn-outline-success' }} border shadow-xs rounded-3"
                                                    title="{{ $asp->status === 'Activo' ? 'Desactivar' : 'Activar' }}">
                                                    <i
                                                        class="fas {{ $asp->status === 'Activo' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-film fs-1 opacity-25 d-block mb-3"></i>
                                            No hay aspectos de formación registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
    MODALES — ACTIVIDADES FORMATIVAS
    ═══════════════════════════════════════════════════ --}}

    {{-- Modal: Crear Actividad --}}
    <div class="modal fade" id="modalCrearActividad" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-plus-circle me-2"></i> Nueva Actividad
                        Formativa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('taller.catalogos.actividades.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                <i class="fas fa-graduation-cap me-2 opacity-50"></i> NOMBRE
                            </label>
                            <input type="text" name="nombre" class="form-control border-2 rounded-3 py-2 border-light-2"
                                placeholder="Ej: Taller, Foro, Seminario…" required autofocus>
                        </div>
                        <div>
                            <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                <i class="fas fa-tag me-2 opacity-50"></i> ABREVIATURA <span
                                    class="ms-1 text-danger">*</span>
                                <span class="ms-auto text-muted" style="font-size:0.75rem;">Máx. 4 caracteres</span>
                            </label>
                            <input type="text" name="abreviatura"
                                class="form-control border-2 rounded-3 py-2 border-light-2 text-center fw-bold text-uppercase"
                                placeholder="Ej: TA" maxlength="4" required oninput="this.value = this.value.toUpperCase()">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>La abreviatura se
                                usa en el código PROCINEC del curso.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Actividad --}}
    <div class="modal fade" id="modalEditarActividad" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-pen me-2"></i> Editar Actividad Formativa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEditarActividad" action="">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small fw-bold mb-2"><i
                                    class="fas fa-graduation-cap me-2 opacity-50"></i> NOMBRE</label>
                            <input type="text" name="nombre" id="editActividadNombre"
                                class="form-control border-2 rounded-3 py-2 border-light-2" required>
                        </div>
                        <div>
                            <label class="text-muted small fw-bold mb-2"><i class="fas fa-tag me-2 opacity-50"></i>
                                ABREVIATURA</label>
                            <input type="text" name="abreviatura" id="editActividadAbreviatura"
                                class="form-control border-2 rounded-3 py-2 border-light-2 text-center fw-bold text-uppercase"
                                maxlength="4" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
    MODALES — ASPECTOS DE FORMACIÓN
    ═══════════════════════════════════════════════════ --}}

    {{-- Modal: Crear Aspecto --}}
    <div class="modal fade" id="modalCrearAspecto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-4 px-4 text-white" style="background:#0891b2;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i> Nuevo Aspecto de Formación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('taller.catalogos.aspectos.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                <i class="fas fa-film me-2 opacity-50"></i> NOMBRE
                            </label>
                            <input type="text" name="nombre" class="form-control border-2 rounded-3 py-2 border-light-2"
                                placeholder="Ej: Fotografía, Dirección, Guion…" required autofocus>
                        </div>
                        <div>
                            <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                <i class="fas fa-tag me-2 opacity-50"></i> ABREVIATURA <span
                                    class="ms-1 text-danger">*</span>
                                <span class="ms-auto text-muted" style="font-size:0.75rem;">Máx. 4 caracteres</span>
                            </label>
                            <input type="text" name="abreviatura"
                                class="form-control border-2 rounded-3 py-2 border-light-2 text-center fw-bold text-uppercase"
                                placeholder="Ej: FO" maxlength="4" required oninput="this.value = this.value.toUpperCase()">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>La abreviatura se
                                usa en el código PROCINEC del curso.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn rounded-pill px-5 fw-bold shadow-sm text-white"
                            style="background:#0891b2;">
                            <i class="fas fa-save me-2"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Aspecto --}}
    <div class="modal fade" id="modalEditarAspecto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-4 px-4 text-white" style="background:#0891b2;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen me-2"></i> Editar Aspecto de Formación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEditarAspecto" action="">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small fw-bold mb-2"><i class="fas fa-film me-2 opacity-50"></i>
                                NOMBRE</label>
                            <input type="text" name="nombre" id="editAspectoNombre"
                                class="form-control border-2 rounded-3 py-2 border-light-2" required>
                        </div>
                        <div>
                            <label class="text-muted small fw-bold mb-2"><i class="fas fa-tag me-2 opacity-50"></i>
                                ABREVIATURA</label>
                            <input type="text" name="abreviatura" id="editAspectoAbreviatura"
                                class="form-control border-2 rounded-3 py-2 border-light-2 text-center fw-bold text-uppercase"
                                maxlength="4" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn rounded-pill px-5 fw-bold shadow-sm text-white"
                            style="background:#0891b2;">
                            <i class="fas fa-save me-2"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .bg-primary-soft {
                background-color: rgba(30, 58, 138, 0.1);
            }

            .bg-info-light {
                background-color: rgba(8, 145, 178, 0.12);
            }

            .bg-success-light {
                background-color: rgba(16, 185, 129, 0.1);
            }

            .border-light-2 {
                border: 1.5px solid #d1d5db !important;
            }

            .shadow-xs {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .shadow-card {
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.09);
            }

            .catalogo-row {
                transition: background 0.2s ease;
            }

            .catalogo-row:hover {
                background-color: #f8fafc !important;
            }

            .icon-box {
                width: 38px;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .icon-shape {
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .table thead th {
                font-size: 0.82rem;
                letter-spacing: 0.05em;
                border-bottom: 1px solid #e2e8f0;
                padding-top: 0.85rem;
                padding-bottom: 0.85rem;
            }

            .table tbody td {
                font-size: 0.95rem;
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
            }

            .table tbody tr:last-child td {
                border-bottom: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // ── Poblar modal Editar Actividad ─────────────────────────────────────────
            document.querySelectorAll('.btn-editar-actividad').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editActividadNombre').value = btn.dataset.nombre;
                    document.getElementById('editActividadAbreviatura').value = btn.dataset.abreviatura;
                    document.getElementById('formEditarActividad').action = btn.dataset.url;
                });
            });

            // ── Poblar modal Editar Aspecto ───────────────────────────────────────────
            document.querySelectorAll('.btn-editar-aspecto').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editAspectoNombre').value = btn.dataset.nombre;
                    document.getElementById('editAspectoAbreviatura').value = btn.dataset.abreviatura;
                    document.getElementById('formEditarAspecto').action = btn.dataset.url;
                });
            });

            // ── Confirmación de toggle con SweetAlert ─────────────────────────────────
            function confirmarToggle(formEl, nombre, status) {
                const activando = status !== 'Activo';
                Swal.fire({
                    icon: activando ? 'question' : 'warning',
                    title: activando ? `¿Activar "${nombre}"?` : `¿Desactivar "${nombre}"?`,
                    text: activando
                        ? 'Este elemento volverá a aparecer en los selectores de cursos nuevos.'
                        : 'Este elemento ya no aparecerá en los selectores de cursos nuevos. Los cursos existentes no se ven afectados.',
                    showCancelButton: true,
                    confirmButtonText: activando ? 'Sí, activar' : 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: activando ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) formEl.submit();
                });
            }

            document.querySelectorAll('.form-toggle-actividad, .form-toggle-aspecto').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    confirmarToggle(this, this.dataset.nombre, this.dataset.status);
                });
            });
        </script>
    @endpush
@endsection