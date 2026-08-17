@extends('layouts.kaiadmin-menu')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Asignar Perfiles al Estudiante / Usuario</div>
                </div>
                <div class="card-body p-4">
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dni_search" class="form-label fw-bold">Buscar por Cédula de Identidad</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" id="dni_search" class="form-control border-start-0 ps-0"
                                        placeholder="Ej: 12345678" autofocus>
                                    <button class="btn btn-primary px-4" type="button" id="btn_search">
                                        <i class="fas fa-search me-2"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="loading_spinner" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Buscando información de la persona...</p>
                    </div>

                    <div id="user_display_area" style="display: none;" class="animated fadeIn mt-4">
                        <div class="row">
                            <!-- Perfil del Usuario Encontrado -->
                            <div class="col-md-4">
                                <div class="card card-profile shadow-sm">
                                    <div class="card-header bg-primary text-center" style="padding-bottom: 50px;">
                                        <h5 class="text-white fw-bold mt-2">Detalles del Usuario</h5>
                                    </div>
                                    <div class="card-body text-center" style="margin-top: -45px;">
                                        <div class="avatar avatar-xxl mb-3">
                                            <span
                                                class="avatar-title rounded-circle border border-4 border-white bg-white text-primary shadow-sm"
                                                style="font-size: 2.5rem;">
                                                <i class="fas fa-user-tie"></i>
                                            </span>
                                        </div>
                                        <h3 class="name fw-bold" id="display_nombre"
                                            style="color: #2a2f5b; line-height: 1.2;">-</h3>
                                        <p class="desc text-muted mb-4" style="font-size: 0.95rem;">Usuario Registrado</p>

                                        <div class="border-top pt-3">
                                            <h6 class="fw-bold text-uppercase text-muted"
                                                style="font-size: 0.75rem; letter-spacing: 1px;">Áreas de Especialización
                                            </h6>
                                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-2"
                                                id="display_especializaciones">
                                                <!-- Las especializaciones se cargarán aquí -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Perfiles Disponibles (Checkboxes) -->
                            <div class="col-md-8">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-success-subtle text-success">
                                                <i class="fas fa-user-tag"></i>
                                            </span>
                                        </div>
                                        <h4 class="card-title fw-bold text-dark mb-0">Seleccione los Roles / Perfiles</h4>
                                    </div>
                                    <div class="card-body p-4 bg-light">
                                        <form id="form_assign_profiles">
                                            @csrf
                                            <input type="hidden" name="id_persona" id="hidden_id_persona">
                                            <div class="row"
                                                style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                                                @forelse($perfiles as $perfil)
                                                    <div class="col-12 mb-3">
                                                        <label class="card perfil-card mb-0 cursor-pointer d-block p-3">
                                                            <div class="d-flex align-items-center">
                                                                <div
                                                                    class="form-check form-switch ps-0 m-0 d-flex align-items-center">
                                                                    <input
                                                                        class="form-check-input profile-checkbox ms-0 me-4 mt-0"
                                                                        type="checkbox" role="switch" name="perfiles[]"
                                                                        value="{{ $perfil->id }}" id="profile_{{ $perfil->id }}"
                                                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h5 class="mb-1 text-dark fw-bold">{{ $perfil->name }}</h5>
                                                                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                                                        {{ $perfil->description ?? 'Acceso al sistema como ' . $perfil->name }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                @empty
                                                    <div class="col-12 py-5 text-center text-muted">
                                                        <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                                                        <h5>No hay perfiles definidos en el sistema.</h5>
                                                    </div>
                                                @endforelse
                                            </div>
                                            @if(count($perfiles) > 0)
                                                <div class="text-end mt-4 border-top pt-3">
                                                    <button type="submit" class="btn btn-primary shadow-sm btn-lg fw-bold px-5"
                                                        id="btn_save">
                                                        <i class="fas fa-save me-2"></i> Guardar Asignación
                                                    </button>
                                                </div>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="not_found_message" class="text-center my-5" style="display: none;">
                        <div class="mb-3">
                            <i class="fas fa-search-minus fa-4x text-muted opacity-25"></i>
                        </div>
                        <h4 class="text-muted fw-bold">Usuario No Encontrado</h4>
                        <p class="text-muted">Asegúrese de que el usuario esté correctamente registrado con la cédula
                            ingresada.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .perfil-card {
            transition: all 0.2s ease-in-out;
            border: 2px solid transparent !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .perfil-card:hover {
            border-color: rgba(21, 114, 232, 0.2) !important;
            background-color: #f8fbff;
            transform: translateY(-2px);
        }

        .form-check-input:checked {
            background-color: #1572E8;
            border-color: #1572E8;
        }

        .bg-success-subtle {
            background-color: #d1e7dd;
        }

        .animated {
            animation-duration: 0.5s;
        }

        .fadeIn {
            animation-name: fadeIn;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnSearch = document.getElementById('btn_search');
            const dniInput = document.getElementById('dni_search');
            const displayArea = document.getElementById('user_display_area');
            const notFoundArea = document.getElementById('not_found_message');
            const loadingSpinner = document.getElementById('loading_spinner');
            const formAssign = document.getElementById('form_assign_profiles');

            function showAlert(title, text, icon) {
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire(title, text, icon);
                } else if (typeof swal !== 'undefined') {
                    swal(title, text, icon);
                } else {
                    alert(`${title}: ${text}`);
                }
            }

            function search() {
                const dni = dniInput.value.trim();
                if (!dni) {
                    showAlert("Atención", "Por favor ingrese una cédula", "warning");
                    return;
                }

                // Reset UI
                displayArea.style.display = 'none';
                notFoundArea.style.display = 'none';
                loadingSpinner.style.display = 'block';

                // Cache-buster para evitar que el navegador devuelva respuestas GET cacheadas
                const timestamp = new Date().getTime();
                fetch(`{{ url('usuarios/search-persona') }}/${encodeURIComponent(dni)}?_t=${timestamp}`)
                    .then(response => {
                        return response.json();
                    })
                    .then(result => {
                        loadingSpinner.style.display = 'none';
                        if (result.success) {
                            const data = result.data;
                            document.getElementById('display_nombre').textContent = data.nombre_completo;
                            document.getElementById('hidden_id_persona').value = data.id_persona;

                            // Cargar Especializaciones
                            const specContainer = document.getElementById('display_especializaciones');
                            specContainer.innerHTML = '';
                            if (data.especializaciones && data.especializaciones.length > 0) {
                                data.especializaciones.forEach(spec => {
                                    const badge = document.createElement('span');
                                    badge.className = 'badge bg-info text-white px-3 py-2 rounded-pill';
                                    badge.textContent = spec;
                                    specContainer.appendChild(badge);
                                });
                            } else {
                                specContainer.innerHTML = '<span class="text-muted fst-italic small">Sin especializaciones</span>';
                            }

                            // Marcar perfiles actuales
                            const perfilesActuales = (data.perfiles_actuales || []).map(Number);
                            const checkboxes = document.querySelectorAll('.profile-checkbox');
                            checkboxes.forEach(cb => {
                                cb.checked = perfilesActuales.includes(parseInt(cb.value, 10));
                            });

                            displayArea.style.display = 'block';

                            // Notificación de éxito al encontrar (protegida si $.notify no está definido)
                            if (typeof $ !== 'undefined' && typeof $.notify === 'function') {
                                $.notify({
                                    icon: 'fas fa-check-circle',
                                    title: 'Persona Encontrada',
                                    message: `Se cargaron los datos de ${data.nombre_completo}`,
                                }, {
                                    type: 'success',
                                    placement: { from: "bottom", align: "right" },
                                    time: 1000,
                                });
                            }

                        } else {
                            notFoundArea.style.display = 'block';
                            showAlert("No Encontrado", result.message || "No se hallaron registros con esa cédula", "info");
                        }
                    })
                    .catch(error => {
                        console.error('Error en búsqueda:', error);
                        loadingSpinner.style.display = 'none';
                        showAlert("Error", "Ocurrió un error al procesar la búsqueda. Verifique su conexión.", "error");
                    });
            }

            btnSearch.addEventListener('click', search);
            dniInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') search();
            });

            formAssign.addEventListener('submit', function (e) {
                e.preventDefault();
                const btnSave = document.getElementById('btn_save');
                const originalContent = btnSave.innerHTML;

                const executeSubmit = () => {
                    btnSave.disabled = true;
                    btnSave.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';

                    const formData = new FormData(formAssign);

                    fetch(`{{ route('users.assign_profiles') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(result => {
                            btnSave.disabled = false;
                            btnSave.innerHTML = originalContent;

                            if (result.success) {
                                showAlert("¡Guardado!", result.message, "success");
                            } else {
                                showAlert("Error", result.message, "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error al guardar:', error);
                            btnSave.disabled = false;
                            btnSave.innerHTML = originalContent;
                            showAlert("Error", "Ocurrió un error al guardar los perfiles", "error");
                        });
                };

                // Confirmación antes de guardar
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({
                        title: "¿Está seguro?",
                        text: "Se actualizarán los perfiles de acceso para este usuario.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, asignar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executeSubmit();
                        }
                    });
                } else if (confirm("¿Está seguro de actualizar los perfiles para este usuario?")) {
                    executeSubmit();
                }
            });
        });
    </script>
@endsection