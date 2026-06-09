{{-- ============================================================
     Vista: Registro de Participante
     Extiende el layout base de login (kaiadmin-login).
     Ruta que la invoca: registro.usuario.store (POST)
     Variables requeridas desde el controlador:
       - $documentTypes  → tipos de documento (V, E, P, etc.)
       - $genders        → géneros disponibles
     ============================================================ --}}
@extends('layouts.kaiadmin-login')
@section('content')

    {{-- ── Estilos locales de la vista ────────────────────────────────
         Solo aplican dentro de esta página. Se definen aquí para no
         contaminar el CSS global con reglas exclusivas del formulario.
    ──────────────────────────────────────────────────────────────── --}}
    <style>
        /* Resalta el borde y fondo al enfocar cualquier campo de texto */
        input[type=text]:focus,
        input[type=password]:focus,
        input[type=email]:focus,
        select:focus {
            background-color: #f7faff !important;
            border-color: #3f67f0 !important;
            box-shadow: 0 0 0 0.25rem rgba(63, 103, 240, 0.15) !important;
            outline: none;
        }

        /* Hace que el label flote en negrita y azul cuando el campo está activo o lleno */
        .form-floating-custom .form-control:focus+label,
        .form-floating-custom .form-control:not(:placeholder-shown)+label,
        .form-floating-custom .form-select:focus+label,
        .form-floating-custom .form-select:not(:placeholder-shown)+label {
            font-weight: 700;
            color: #3f67f0;
        }

        /* Contenedor principal del formulario: centrado y ancho máximo */
        .container-login {
            max-width: 1100px !important;
            width: 100% !important;
        }

        /* Envuelve toda la página para centrado vertical */
        .wrapper-login {
            min-height: 100vh;
        }
    </style>

    {{-- ── Contenedor principal centrado verticalmente ──────────────── --}}
    <div class="wrapper-login d-flex justify-content-center align-items-center py-5">
        <div class="container-login bg-white shadow-lg rounded-4 p-5 animated fadeIn">

            <h2 class="text-center mb-5 fw-extrabold text-primary">
                <i class="fas fa-user-plus me-2"></i> {{__('Registro de Participante')}}
            </h2>

            {{-- ── Mensajes de sesión y errores de validación ───────────
                 Se muestran si el controlador los envía tras un redirect.
                 $errors es llenado automáticamente por Laravel al fallar
                 la validación del Request.
            ──────────────────────────────────────────────────────────── --}}
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    Varios campos contienen errores, por favor verifique.
                </div>
            @endif

            {{-- ── Formulario principal ────────────────────────────────
                 POST → RegistroController@store
                 autocomplete="off" evita que el navegador rellene datos
                 automáticamente (importante para campos de contraseña).
            ──────────────────────────────────────────────────────────── --}}
            <form method="post" action="{{ route('registro.usuario.store') }}" autocomplete="off" id="frm_register">
                @csrf

                <div class="login-form">
                    <div class="form-sub mb-5">

                        {{-- ══════════════════════════════════════════
                             SECCIÓN 1 · DATOS CLAVE
                             Documento de identidad y nombre completo.
                        ══════════════════════════════════════════════ --}}
                        <h5 class="section-title text-primary border-bottom pb-2 mb-4 fw-bold">
                            <i class="fas fa-id-card me-2"></i> Datos Clave
                        </h5>
                        <div class="row g-4">

                            {{-- Tipo de documento (V, E, P…) → col 4 --}}
                            <div class="col-md-4">
                                <div class="form-floating form-floating-custom">
                                    <select id="tipo_dni" name="tipo_dni" class="form-select" required>
                                        {{-- Opción vacía: deshabilitada; si old() existe, ya no es "selected" --}}
                                        <option value="" disabled {{ old('tipo_dni') ? '' : 'selected' }}>Seleccione
                                        </option>
                                        @foreach($documentTypes as $tdoc)
                                            <option value="{{ $tdoc->id }}" {{ old('tipo_dni') == $tdoc->id ? 'selected' : '' }}>
                                                {{ $tdoc->code }} - {{ $tdoc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="tipo_dni">{{__('Tipo de Documento')}} <span class="text-danger">*</span></label>
                                    @error('tipo_dni') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- Número de documento o pasaporte → col 8 --}}
                            <div class="col-md-8">
                                <div class="form-floating form-floating-custom">
                                    <input id="dni" name="dni" type="text" class="form-control"
                                        placeholder="Número de Documento" required value="{{ old('dni') }}" />
                                    <label for="dni">{{__('Número de Documento / Pasaporte')}} <span class="text-danger">*</span></label>
                                    @error('dni') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- Nombre completo: primer y segundo nombre --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="primer_nombre" name="primer_nombre" type="text" class="form-control"
                                        placeholder="Primer Nombre" required value="{{ old('primer_nombre') }}" />
                                    <label for="primer_nombre">{{__('Primer Nombre')}} <span class="text-danger">*</span></label>
                                    @error('primer_nombre') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="segundo_nombre" name="segundo_nombre" type="text" class="form-control"
                                        placeholder="Segundo Nombre" value="{{ old('segundo_nombre') }}" />
                                    <label for="segundo_nombre">{{__('Segundo Nombre')}}</label>
                                </div>
                            </div>

                            {{-- Apellidos: primero y segundo --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="primer_apellido" name="primer_apellido" type="text" class="form-control"
                                        placeholder="Primer Apellido" required value="{{ old('primer_apellido') }}" />
                                    <label for="primer_apellido">{{__('Primer Apellido')}} <span class="text-danger">*</span></label>
                                    @error('primer_apellido') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="segundo_apellido" name="segundo_apellido" type="text" class="form-control"
                                        placeholder="Segundo Apellido" value="{{ old('segundo_apellido') }}" />
                                    <label for="segundo_apellido">{{__('Segundo Apellido')}}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Teléfonos (máscara (0000) 000-0000 aplicada por JS) --}}
                        <div class="row g-4 mt-1">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom">
                                    <input id="telefono" name="telefono" type="text" class="form-control"
                                        placeholder="Teléfono" required value="{{ old('telefono') }}" />
                                    <label for="telefono">{{__('Teléfono Principal')}} <span class="text-danger">*</span></label>
                                    @error('telefono') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom">
                                    <input id="telefono_opcional" name="telefono_opcional" type="text" class="form-control"
                                        placeholder="Teléfono Opc." value="{{ old('telefono_opcional') }}" />
                                    <label for="telefono_opcional">Teléfono Opcional</label>
                                </div>
                            </div>
                        </div>

                        {{-- ══════════════════════════════════════════
                             SECCIÓN 2 · DATOS DE ACCESO
                             Credenciales para iniciar sesión.
                        ══════════════════════════════════════════════ --}}
                        <h5 class="section-title text-primary border-bottom pb-2 mb-4 mt-5 fw-bold">
                            <i class="fas fa-user-shield me-2"></i> Datos de Acceso
                        </h5>
                        <div class="row g-4">

                            {{-- Nombre de usuario único (máx 30 caracteres) --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="username" name="username" maxlength="30" type="text" class="form-control"
                                        placeholder="{{__('Username')}}" required value="{{ old('username') }}" />
                                    <label for="username">{{__('Nombre de Usuario')}} <span class="text-danger">*</span></label>
                                    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- Correo electrónico (máx 100 caracteres) --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="email" name="email" maxlength="100" type="email" class="form-control"
                                        placeholder="{{__('Email')}}" required value="{{ old('email') }}" />
                                    <label for="email">{{__('Correo Electrónico')}} (Email) <span class="text-danger">*</span></label>
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- Código de Verificación --}}
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <div class="form-floating form-floating-custom flex-grow-1">
                                        <input id="code" name="code" type="text" class="form-control"
                                            placeholder="Código" required />
                                        <label for="code">{{__('Código de Verificación')}} <span class="text-danger">*</span></label>
                                    </div>
                                    <button class="btn btn-outline-primary" type="button" id="btn_send_code">
                                        <i class="fas fa-paper-plane me-1"></i> Enviar
                                    </button>
                                </div>
                                <div id="code_message" class="small mt-1"></div>
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Contraseña y confirmación (máx 16 caracteres) --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    <input id="password" name="password" type="password" maxlength="16" class="form-control"
                                        placeholder="{{__('Password')}}" required />
                                    <label for="password">{{__('Contraseña')}} <span class="text-danger">*</span></label>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom mb-2">
                                    {{-- Este campo no se valida en backend; Laravel lo usa para la regla "confirmed" --}}
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        maxlength="16" class="form-control" placeholder="{{__('Confirm Password')}}"
                                        required />
                                    <label for="password_confirmation">{{__('Repita Contraseña')}} <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>

                        {{-- ══════════════════════════════════════════
                             SECCIÓN 3 · INFORMACIÓN COMPLEMENTARIA
                             RIF, género y ubicación geográfica.
                        ══════════════════════════════════════════════ --}}
                        <h5 class="section-title text-primary border-bottom pb-2 mb-4 mt-5 fw-bold">
                            <i class="fas fa-address-card me-2"></i> Información Complementaria
                        </h5>
                        <div class="row g-4">

                            {{-- RIF: solo dígitos (inputmode numérico + filtro JS) --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom">
                                    <input id="rif" name="rif" type="text" class="form-control" placeholder="RIF"
                                        inputmode="numeric" pattern="[0-9]*"
                                        value="{{ old('rif') }}" />
                                    <label for="rif">{{__('RIF')}}</label>
                                    @error('rif') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- Género (poblado desde $genders en el controlador) --}}
                            <div class="col-md-6">
                                <div class="form-floating form-floating-custom">
                                    <select id="genero" name="genero" class="form-select" required>
                                        <option value="" disabled {{ old('genero') ? '' : 'selected' }}>Seleccione</option>
                                        @foreach($genders as $gen)
                                            <option value="{{ $gen->id }}" {{ old('genero') == $gen->id ? 'selected' : '' }}>
                                                {{ $gen->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="genero">{{__('Género')}} <span class="text-danger">*</span></label>
                                    @error('genero') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            {{-- ── Subsección: Ubicación geográfica en cascada ──────────
                                 Los selectores Estado, Municipio y Parroquia se llenan
                                 dinámicamente vía AJAX. Se deshabilitan hasta que el
                                 selector padre tenga un valor seleccionado.
                                 Ver lógica completa en el bloque <script> al pie.
                            ──────────────────────────────────────────────────────────── --}}
                            <h5 class="section-title text-primary border-bottom pb-2 mb-4 mt-5 fw-bold">
                                <i class="fas fa-map-marker-alt me-2"></i> Ubicación
                            </h5>

                            <div class="row g-4">

                                {{-- Estado: se llena al cargar la página via AJAX (solo Venezuela) --}}
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-custom">
                                        <select id="id_estado" name="id_estado" class="form-select" required disabled>
                                            <option value="">Cargando estados...</option>
                                        </select>
                                        <label for="id_estado">{{__('Estado')}} <span class="text-danger">*</span></label>
                                        @error('id_estado') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                {{-- Municipio: se habilita al seleccionar un Estado --}}
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-custom">
                                        <select id="id_municipio" name="id_municipio" class="form-select" required disabled>
                                            <option value="">Seleccione Municipio</option>
                                        </select>
                                        <label for="id_municipio">{{__('Municipio')}} <span class="text-danger">*</span></label>
                                        @error('id_municipio') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                {{-- Parroquia: se habilita al seleccionar un Municipio --}}
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-custom">
                                        <select id="id_parroquia" name="id_parroquia" class="form-select" required disabled>
                                            <option value="">Seleccione Parroquia</option>
                                        </select>
                                        <label for="id_parroquia">{{__('Parroquia')}} <span class="text-danger">*</span></label>
                                        @error('id_parroquia') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                {{-- Dirección exacta (texto libre) --}}
                                <div class="col-12">
                                    <div class="form-floating form-floating-custom mb-3">
                                        <textarea id="direccion" name="direccion" class="form-control" style="height: 100px"
                                            placeholder="Dirección exacta" required>{{ old('direccion') }}</textarea>
                                        <label for="direccion">{{__('Dirección Exacta')}} <span class="text-danger">*</span></label>
                                        @error('direccion') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>{{-- /row ubicación --}}

                            {{-- ── Botón de envío y enlace al login ───────────────────── --}}
                            <div class="form-action mb-3 mt-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg w-50 shadow">
                                    <i class="fas fa-check-circle me-2"></i> Registrarse Ahora
                                </button>
                                <div class="mt-3">
                                    <a href="{{ route('login') }}" class="btn btn-link text-muted"
                                        style="text-decoration: none;">
                                        <i class="fas fa-arrow-left me-1"></i> {{__('¿Ya tienes cuenta? Volver al Login')}}
                                    </a>
                                </div>
                            </div>

                        </div>{{-- /row info complementaria --}}
                    </div>{{-- /form-sub --}}
                </div>{{-- /login-form --}}
            </form>
        </div>{{-- /container-login --}}
    </div>{{-- /wrapper-login --}}

    {{-- ── Dependencias JS ─────────────────────────────────────────────
         jQuery es necesario para inputmask y las llamadas AJAX.
         inputmask aplica la máscara telefónica (9999) 999-9999.
    ──────────────────────────────────────────────────────────────── --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            // ── Máscara de teléfono ────────────────────────────────────
            // Aplica formato (0424) 123-4567 a ambos campos de teléfono.
            if ($.fn.inputmask) {
                $('#telefono, #telefono_opcional').inputmask('(9999) 999-9999');
            }

            // ── RIF: solo dígitos ──────────────────────────────────────
            // Elimina en tiempo real cualquier carácter no numérico.
            $('#rif').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // ── ajaxGeo: carga geográfica en cascada ───────────────────
            // Recibe la URL del endpoint, el selector destino y el texto
            // del placeholder mientras carga. Deshabilita el selector
            // durante la petición y lo habilita al recibir los datos.
            // Endpoint esperado: devuelve JSON con [{id, nombre}, ...]
            function ajaxGeo(url, targetSelector, placeholder) {
                const $target = $(targetSelector);

                // Vacía el selector y lo bloquea mientras espera la respuesta
                $target.empty().append('<option value="">' + placeholder + '</option>').prop('disabled', true);

                return $.get(url)
                    .done(function (data) {
                        if (data && data.length > 0) {
                            // Rellena las opciones con los datos recibidos
                            $.each(data, function (i, item) {
                                $target.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                            $target.prop('disabled', false);
                        } else {
                            // El endpoint respondió vacío (sin registros)
                            $target.append('<option value="">No disponible</option>').prop('disabled', false);
                        }
                    })
                    .fail(function () {
                        // Error de red o respuesta no-200
                        $target.append('<option value="">Error de conexión</option>').prop('disabled', false);
                    });
            }

            // ── 1. Carga inicial de Estados ────────────────────────────
            // Se ejecuta al abrir la página. La tabla es exclusiva de
            // Venezuela, por lo que no se necesita filtrar por país.
            ajaxGeo('{{ url("registro/ajax/estados") }}', '#id_estado', 'Seleccione Estado');

            // ── 2. Estado → Municipios ─────────────────────────────────
            // Al cambiar el Estado se cargan sus Municipios y se
            // resetea la Parroquia para evitar datos inconsistentes.
            $('#id_estado').on('change', function () {
                const estadoId = $(this).val();
                if (estadoId) {
                    ajaxGeo('{{ url("registro/ajax/municipios") }}/' + estadoId, '#id_municipio', 'Seleccione Municipio');
                    $('#id_parroquia').empty().append('<option value="">Seleccione Parroquia</option>').prop('disabled', true);
                }
            });

            // ── 3. Municipio → Parroquias ──────────────────────────────
            // Al cambiar el Municipio se cargan sus Parroquias.
            $('#id_municipio').on('change', function () {
                const municipioId = $(this).val();
                if (municipioId) {
                    ajaxGeo('{{ url("registro/ajax/parroquias") }}/' + municipioId, '#id_parroquia', 'Seleccione Parroquia');
                }
            });

            // ── 4. Envío de Código de Verificación ─────────────────────
            $('#btn_send_code').on('click', function() {
                const email = $('#email').val();
                const $btn = $(this);
                const $message = $('#code_message');

                if (!email) {
                    $message.removeClass('text-success').addClass('text-danger').text('Por favor, ingrese su correo electrónico primero.');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                $message.empty();

                $.ajax({
                    url: '{{ route("registro.usuario.enviar_codigo") }}',
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: email
                    },
                    success: function(response) {
                        if (response.status == "1") {
                            $message.removeClass('text-danger').addClass('text-success').text(response.message);
                            // Iniciar temporizador de 15 minutos (opcional, por ahora solo mostramos el mensaje)
                            let timeLeft = 60; // Ejemplo: 60 segundos para reintentar
                            const interval = setInterval(() => {
                                timeLeft--;
                                $btn.text('Reenviar en ' + timeLeft + 's');
                                if (timeLeft <= 0) {
                                    clearInterval(interval);
                                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar');
                                }
                            }, 1000);
                        } else {
                            $message.removeClass('text-success').addClass('text-danger').text(response.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al enviar el código.';
                        $message.removeClass('text-success').addClass('text-danger').text(errorMsg);
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar');
                    }
                });
            });

        });
    </script>
@endsection