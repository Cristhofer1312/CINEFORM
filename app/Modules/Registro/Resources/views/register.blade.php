{{-- ============================================================
     Vista: Registro de Participante
     Extiende el layout base de login (kaiadmin-login).
     Ruta que la invoca: registro.usuario.store (POST)
     Variables requeridas desde el controlador:
       - $documentTypes  → tipos de documento (V, E, P, etc.)
       - $genders        → géneros disponibles
     Mismo mundo visual que el login: sala de cine (img/login-sala.svg)
     y una tarjeta blanca con el formulario en tres secciones.
     ============================================================ --}}
@extends('layouts.kaiadmin-login')
@section('content')

    {{-- ── Estilos locales de la vista ──────────────────────────────── --}}
    <style>
        .cf-registro {
            min-height: 100vh;
            min-height: 100dvh;
            padding: clamp(1.25rem, 4vw, 3rem) clamp(1rem, 4vw, 3rem) clamp(2rem, 5vw, 4rem);
            background: #070d22 url("{{ asset('img/login-sala.svg') }}?v={{ filemtime(public_path('img/login-sala.svg')) }}") 20% top / cover no-repeat;
        }

        /* En escritorio el fondo queda fijo: el formulario es largo y, con
           "cover" sobre toda la altura, la sala se estiraba de más. */
        @media (min-width: 992px) and (hover: hover) {
            .cf-registro { background-attachment: fixed; }
        }

        .cf-registro__marco {
            max-width: 880px;
            margin: 0 auto;
        }

        .cf-registro__barra {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem 1.5rem;
            margin-bottom: clamp(1.25rem, 3vw, 2rem);
            color: #cbd5e1;
            font-size: 0.9375rem;
        }

        .cf-registro__marca {
            margin: 0;
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1;
            color: #f8fafc;
        }

        .cf-registro__barra a {
            color: #f8fafc;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: rgba(248, 250, 252, 0.45);
            text-underline-offset: 3px;
        }

        .cf-registro__barra a:hover,
        .cf-registro__barra a:focus-visible {
            text-decoration-color: currentColor;
        }

        .cf-registro__tarjeta {
            padding: clamp(1.5rem, 4vw, 3rem);
            background: var(--cf-surface);
            border-radius: var(--cf-radius-card);
            box-shadow: 0 24px 64px rgba(2, 6, 23, 0.45), 0 2px 6px rgba(2, 6, 23, 0.2);
            animation: cf-registro-entrada 520ms var(--cf-ease) both;
        }

        @keyframes cf-registro-entrada {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .cf-registro__tarjeta { animation: none; }
        }

        .cf-registro__cabecera {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        /* Logo del CNAC en PNG transparente (img/marca); proporción 1.15:1. */
        .cf-registro__logo {
            width: auto;
            height: 72px;
            flex: none;
        }

        .cf-registro__cabecera h1 {
            margin: 0 0 0.25rem;
            font-size: clamp(1.5rem, 3vw, 1.875rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            color: var(--cf-ink);
        }

        .cf-registro__cabecera p {
            margin: 0;
            max-width: 52ch;
            color: var(--cf-muted);
            text-wrap: pretty;
        }

        /* Aviso plano: KaiAdmin pone a .alert borde izquierdo grueso y sombra. */
        .cf-registro .cf-registro__aviso {
            margin-bottom: 1.75rem;
            padding: 1rem 1.25rem;
            border: 1px solid transparent;
            border-radius: var(--cf-radius-control);
            box-shadow: none;
            font-size: 0.9375rem;
        }

        .cf-registro .cf-registro__aviso.alert-danger {
            background: var(--cf-danger-soft);
            border-color: rgba(220, 38, 38, 0.25);
            color: var(--cf-danger-ink);
        }

        .cf-registro .cf-registro__aviso.alert-success {
            background: var(--cf-success-soft);
            border-color: rgba(25, 135, 84, 0.25);
            color: var(--cf-success-ink);
        }

        .cf-registro__aviso ul {
            margin: 0.5rem 0 0;
            padding-left: 1.25rem;
        }

        /* Secciones: separadas por aire y una línea fina, sin tarjetas anidadas. */
        .cf-registro__seccion {
            margin: 0;
            padding: 0;
            border: 0;
            min-width: 0;
        }

        .cf-registro__seccion + .cf-registro__seccion {
            margin-top: 2.5rem;
            padding-top: 2.25rem;
            border-top: 1px solid var(--cf-border);
        }

        /* float + width 100%: la leyenda sale del borde del fieldset (si no, el
           navegador la dibuja encima de la línea divisoria). */
        .cf-registro__seccion legend {
            float: left;
            width: 100%;
            margin: 0 0 0.25rem;
            padding: 0;
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--cf-ink);
        }

        .cf-registro__ayuda {
            clear: both;
            margin: 0 0 1.25rem;
            font-size: 0.875rem;
            color: var(--cf-muted);
        }

        .cf-registro .form-floating > .form-control,
        .cf-registro .form-floating > .form-select {
            border-color: var(--cf-border-strong);
            border-radius: var(--cf-radius-control);
        }

        .cf-registro .form-floating > .form-control:focus,
        .cf-registro .form-floating > .form-select:focus {
            border-color: var(--cf-blue);
            box-shadow: var(--cf-focus-ring);
        }

        /* KaiAdmin anula el max-width de Bootstrap: sin esto, una etiqueta más
           larga que su campo se sale del borde en vez de cortarse con "…". */
        .cf-registro .form-floating > label {
            max-width: 100%;
            color: var(--cf-muted);
        }

        .cf-registro .form-floating > .form-control:focus ~ label,
        .cf-registro .form-floating > .form-select:focus ~ label {
            color: var(--cf-blue);
        }

        .cf-registro .form-floating > .is-invalid {
            border-color: var(--cf-danger);
        }

        .cf-registro .form-floating > .form-select:disabled {
            background-color: var(--cf-surface-subtle);
        }

        /* Bootstrap pinta la etiqueta de un campo deshabilitado con otro gris. */
        .cf-registro .form-floating > .form-select:disabled ~ label::after {
            background-color: var(--cf-surface-subtle);
        }

        .cf-registro textarea.form-control {
            min-height: 6.5rem;
        }

        .cf-registro .invalid-feedback {
            color: var(--cf-danger-ink);
        }

        .cf-registro .form-text {
            margin-top: 0.375rem;
            color: var(--cf-muted);
        }

        .cf-registro__obligatorio {
            color: var(--cf-danger-ink);
        }

        /* Teléfono: prefijo + número en una pieza. */
        /* Ancho fijo: input-group encoge sus piezas y cortaba el prefijo elegido. */
        .cf-registro__telefono > .cf-registro__prefijo {
            flex: 0 0 7.5rem;
            width: 7.5rem;
        }

        .cf-registro__telefono .cf-registro__prefijo > .form-select {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .cf-registro__telefono .form-floating + .form-floating > .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Código de verificación: campo + botón "Enviar código" en una pieza. */
        .cf-registro__codigo .form-floating > .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .cf-registro__codigo .btn {
            min-width: 9.5rem;
            border-radius: 0 var(--cf-radius-control) var(--cf-radius-control) 0;
            font-weight: 600;
        }

        .cf-registro__acciones {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem 1.5rem;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid var(--cf-border);
        }

        .cf-registro__acciones p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--cf-muted);
        }

        .cf-registro__enviar {
            min-width: 14rem;
            padding: 0.8rem 2rem;
            font-weight: 700;
            border-radius: var(--cf-radius-pill);
        }

        .cf-registro ::selection {
            background: rgba(21, 114, 232, 0.2);
        }

        @media (max-width: 575.98px) {
            .cf-registro__cabecera { align-items: flex-start; }
            .cf-registro__logo { height: 56px; }
            .cf-registro__enviar { width: 100%; }

            /* Sin espacio para campo y botón en una línea: el botón baja. */
            .cf-registro__codigo { flex-wrap: wrap; gap: 0.5rem; }
            .cf-registro__codigo .form-floating { flex: 1 1 100%; }
            .cf-registro__codigo .form-floating > .form-control {
                border-radius: var(--cf-radius-control) !important;
                border-right: 1px solid var(--cf-border-strong);
            }
            .cf-registro__codigo .btn {
                width: 100%;
                margin-left: 0 !important;
                padding-block: 0.7rem;
                border-radius: var(--cf-radius-control) !important;
            }
        }
    </style>

    @php
        // Clase de error de Bootstrap para un campo, si la validación lo rechazó.
        $invalido = fn (string $campo) => $errors->has($campo) ? ' is-invalid' : '';
    @endphp

    <div class="cf-registro">
        <div class="cf-registro__marco">

            <div class="cf-registro__barra">
                <p class="cf-registro__marca">CINEFORM</p>
                <span>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></span>
            </div>

            <main class="cf-registro__tarjeta">
                <header class="cf-registro__cabecera">
                    <img class="cf-registro__logo" src="{{ asset('img/marca/logo-cnac.png') }}?v={{ filemtime(public_path('img/marca/logo-cnac.png')) }}"
                        alt="Centro Nacional Autónomo de Cinematografía">
                    <div>
                        <h1>Crea tu cuenta</h1>
                        <p>Regístrate como participante para inscribirte en los cursos del CNAC. Los campos con
                            <span class="cf-registro__obligatorio" aria-hidden="true">*</span> son obligatorios.</p>
                    </div>
                </header>

                {{-- ── Mensajes de sesión y errores de validación ─────────── --}}
                @if(session('error'))
                    <div class="alert alert-danger cf-registro__aviso" role="alert">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success cf-registro__aviso" role="status">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger cf-registro__aviso" role="alert">
                        <strong>Revisa los campos marcados:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- POST → RegisterController@store --}}
                <form method="post" action="{{ route('registro.usuario.store') }}" id="frm_register">
                    @csrf

                    {{-- ═════ 1 · Datos personales ═════ --}}
                    <fieldset class="cf-registro__seccion">
                        <legend>Datos personales</legend>
                        <p class="cf-registro__ayuda">Tal como aparecen en tu documento de identidad.</p>

                        <div class="row g-3">
                            <div class="col-sm-5 col-md-4">
                                <div class="form-floating">
                                    <select id="tipo_dni" name="tipo_dni" class="form-select{{ $invalido('tipo_dni') }}" required>
                                        <option value="" disabled {{ old('tipo_dni') ? '' : 'selected' }}>Seleccione</option>
                                        @foreach($documentTypes as $tdoc)
                                            <option value="{{ $tdoc->id }}" {{ old('tipo_dni') == $tdoc->id ? 'selected' : '' }}>
                                                {{ $tdoc->code }} - {{ $tdoc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="tipo_dni">Tipo de documento <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('tipo_dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-7 col-md-8">
                                <div class="form-floating">
                                    <input id="dni" name="dni" type="text" maxlength="20" class="form-control{{ $invalido('dni') }}"
                                        placeholder="Número de documento" required value="{{ old('dni') }}" />
                                    <label for="dni">Número de documento <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="primer_nombre" name="primer_nombre" type="text" maxlength="100" class="form-control{{ $invalido('primer_nombre') }}"
                                        placeholder="Primer nombre" autocomplete="given-name" required value="{{ old('primer_nombre') }}" />
                                    <label for="primer_nombre">Primer nombre <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('primer_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="segundo_nombre" name="segundo_nombre" type="text" maxlength="100" class="form-control{{ $invalido('segundo_nombre') }}"
                                        placeholder="Segundo nombre" autocomplete="additional-name" value="{{ old('segundo_nombre') }}" />
                                    <label for="segundo_nombre">Segundo nombre</label>
                                    @error('segundo_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="primer_apellido" name="primer_apellido" type="text" maxlength="100" class="form-control{{ $invalido('primer_apellido') }}"
                                        placeholder="Primer apellido" autocomplete="family-name" required value="{{ old('primer_apellido') }}" />
                                    <label for="primer_apellido">Primer apellido <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('primer_apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="segundo_apellido" name="segundo_apellido" type="text" maxlength="100" class="form-control{{ $invalido('segundo_apellido') }}"
                                        placeholder="Segundo apellido" value="{{ old('segundo_apellido') }}" />
                                    <label for="segundo_apellido">Segundo apellido</label>
                                    @error('segundo_apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select id="genero" name="genero" class="form-select{{ $invalido('genero') }}" required>
                                        <option value="" disabled {{ old('genero') ? '' : 'selected' }}>Seleccione</option>
                                        @foreach($genders as $gen)
                                            <option value="{{ $gen->id }}" {{ old('genero') == $gen->id ? 'selected' : '' }}>
                                                {{ $gen->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="genero">Género <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- RIF: solo dígitos (inputmode numérico + filtro JS) --}}
                                <div class="form-floating">
                                    <input id="rif" name="rif" type="text" maxlength="20" class="form-control{{ $invalido('rif') }}" placeholder="RIF"
                                        inputmode="numeric" pattern="[0-9]*" aria-describedby="rif_ayuda" value="{{ old('rif') }}" />
                                    <label for="rif">RIF</label>
                                    @error('rif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div id="rif_ayuda" class="form-text">Opcional. Solo números.</div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- ═════ 2 · Contacto y ubicación ═════ --}}
                    <fieldset class="cf-registro__seccion">
                        <legend>Contacto y ubicación</legend>
                        <p class="cf-registro__ayuda">Para avisarte sobre tus cursos y emitir tus certificados.</p>

                        {{-- Teléfonos: prefijo móvil + 7 dígitos (máscara 000-0000 por JS).
                             El campo oculto con el nombre real ("telefono") se arma al
                             enviar: prefijo + dígitos, 11 en total, como en comun.personas. --}}
                        <div class="row g-3">
                            @foreach ([['telefono', 'Teléfono', true], ['telefono_opcional', 'Otro teléfono', false]] as [$campo, $etiqueta, $obligatorio])
                                @php
                                    $anterior = (string) old($campo);
                                    $prefijoAnterior = substr($anterior, 0, 4);
                                    $numeroAnterior = strlen($anterior) > 4 ? substr($anterior, 4, 3).'-'.substr($anterior, 7) : '';
                                @endphp
                                <div class="col-md-6">
                                    <div class="input-group cf-registro__telefono" data-telefono="{{ $campo }}">
                                        <div class="form-floating cf-registro__prefijo">
                                            <select id="{{ $campo }}_prefijo" class="form-select{{ $invalido($campo) }}" @if($obligatorio) required @endif
                                                aria-label="Prefijo de {{ mb_strtolower($etiqueta) }}">
                                                <option value="" {{ $prefijoAnterior === '' ? 'selected' : '' }} @if($obligatorio) disabled @endif>{{ $obligatorio ? 'Elija' : 'Ninguno' }}</option>
                                                @foreach ($prefijosTelefono as $prefijo => $operadora)
                                                    <option value="{{ $prefijo }}" title="{{ $operadora }}" {{ $prefijoAnterior === $prefijo ? 'selected' : '' }}>{{ $prefijo }}</option>
                                                @endforeach
                                            </select>
                                            <label for="{{ $campo }}_prefijo">Prefijo @if($obligatorio)<span class="cf-registro__obligatorio" aria-hidden="true">*</span>@endif</label>
                                        </div>
                                        <div class="form-floating">
                                            <input id="{{ $campo }}_numero" type="tel" inputmode="numeric" class="form-control{{ $invalido($campo) }}"
                                                placeholder="123-4567" autocomplete="{{ $obligatorio ? 'tel-local' : 'off' }}" pattern="[0-9]{3}-[0-9]{4}"
                                                title="7 dígitos, por ejemplo 123-4567" @if($obligatorio) required @endif value="{{ $numeroAnterior }}" />
                                            <label for="{{ $campo }}_numero">{{ $etiqueta }} @if($obligatorio)<span class="cf-registro__obligatorio" aria-hidden="true">*</span>@endif</label>
                                        </div>
                                    </div>
                                    <input type="hidden" id="{{ $campo }}" name="{{ $campo }}" value="{{ $anterior }}">
                                    @error($campo) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            @endforeach

                            {{-- Ubicación en cascada: Estado → Municipio → Parroquia por AJAX.
                                 data-anterior guarda lo elegido antes de un error de validación,
                                 para volver a seleccionarlo cuando cargan las opciones. --}}
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select id="id_estado" name="id_estado" class="form-select{{ $invalido('id_estado') }}" required disabled
                                        data-anterior="{{ old('id_estado') }}">
                                        <option value="">Cargando estados…</option>
                                    </select>
                                    <label for="id_estado">Estado <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('id_estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select id="id_municipio" name="id_municipio" class="form-select{{ $invalido('id_municipio') }}" required disabled
                                        data-anterior="{{ old('id_municipio') }}">
                                        <option value="">Elija un estado primero</option>
                                    </select>
                                    <label for="id_municipio">Municipio <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('id_municipio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select id="id_parroquia" name="id_parroquia" class="form-select{{ $invalido('id_parroquia') }}" required disabled
                                        data-anterior="{{ old('id_parroquia') }}">
                                        <option value="">Elija un municipio primero</option>
                                    </select>
                                    <label for="id_parroquia">Parroquia <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('id_parroquia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea id="direccion" name="direccion" maxlength="255" class="form-control{{ $invalido('direccion') }}"
                                        placeholder="Dirección" autocomplete="street-address" aria-describedby="direccion_ayuda" required>{{ old('direccion') }}</textarea>
                                    <label for="direccion">Dirección <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div id="direccion_ayuda" class="form-text">Urbanización, calle, casa o apartamento.</div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- ═════ 3 · Tu cuenta ═════ --}}
                    <fieldset class="cf-registro__seccion">
                        <legend>Tu cuenta</legend>
                        <p class="cf-registro__ayuda">Con tu usuario o correo y tu contraseña iniciarás sesión.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="username" name="username" maxlength="30" type="text" class="form-control{{ $invalido('username') }}"
                                        placeholder="Nombre de usuario" autocomplete="username" aria-describedby="username_ayuda" required value="{{ old('username') }}" />
                                    <label for="username">Nombre de usuario <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div id="username_ayuda" class="form-text">Letras, números, punto, guion o guion bajo; sin espacios.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="email" name="email" maxlength="100" type="email" class="form-control{{ $invalido('email') }}"
                                        placeholder="Correo electrónico" autocomplete="email" required value="{{ old('email') }}" />
                                    <label for="email">Correo electrónico <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Código de verificación: se envía al correo de arriba --}}
                            <div class="col-12">
                                <div class="input-group cf-registro__codigo">
                                    <div class="form-floating">
                                        <input id="code" name="code" type="text" inputmode="numeric" maxlength="6"
                                            class="form-control{{ $invalido('code') }}" placeholder="Código" autocomplete="one-time-code"
                                            aria-describedby="code_message" required />
                                        <label for="code">Código de verificación <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    </div>
                                    <button class="btn btn-outline-primary" type="button" id="btn_send_code">
                                        <i class="fas fa-paper-plane me-1" aria-hidden="true"></i> Enviar código
                                    </button>
                                </div>
                                <div id="code_message" class="form-text" aria-live="polite">Te enviaremos un código de 6 dígitos a tu correo. Vence a los 15 minutos.</div>
                                @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Contraseña y confirmación (política: 10-72 caracteres, mayúscula, minúscula, número y símbolo) --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input id="password" name="password" type="password" maxlength="72" minlength="10" class="form-control{{ $invalido('password') }}"
                                        placeholder="Contraseña" autocomplete="new-password" aria-describedby="password_ayuda" required />
                                    <label for="password">Contraseña <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div id="password_ayuda" class="form-text">Mínimo 10 caracteres, con mayúscula, minúscula, número y símbolo.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{-- No se valida aparte: Laravel lo usa para la regla "confirmed" --}}
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        maxlength="72" class="form-control" placeholder="Repite la contraseña" autocomplete="new-password" required />
                                    <label for="password_confirmation">Repite la contraseña <span class="cf-registro__obligatorio" aria-hidden="true">*</span></label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="cf-registro__acciones">
                        <p>Al registrarte podrás inscribirte en los cursos abiertos.</p>
                        <button type="submit" id="btn_registrar" class="btn btn-primary btn-lg cf-registro__enviar">
                            Crear cuenta
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    {{-- ── Dependencias JS ─────────────────────────────────────────────
         jQuery es necesario para inputmask y las llamadas AJAX.
         inputmask aplica la máscara telefónica (9999) 999-9999.
    ──────────────────────────────────────────────────────────────── --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    {{-- Build sin jQuery: el layout vuelve a cargar jQuery después y borraría $.fn.inputmask. --}}
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.10/dist/inputmask.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            const ICONO_ENVIAR = '<i class="fas fa-paper-plane me-1" aria-hidden="true"></i> Enviar código';

            // ── Teléfonos: prefijo + número ────────────────────────────
            if (window.Inputmask) {
                Inputmask('999-9999', { showMaskOnHover: false }).mask(document.querySelectorAll('#telefono_numero, #telefono_opcional_numero'));
            }

            // Arma el valor que se envía (prefijo + 7 dígitos) y, en el teléfono
            // opcional, exige las dos partes si se llenó una sola.
            function armarTelefono(grupo) {
                const campo = grupo.dataset.telefono;
                const prefijo = document.getElementById(campo + '_prefijo');
                const numero = document.getElementById(campo + '_numero');
                const digitos = numero.value.replace(/[^0-9]/g, '');

                if (!prefijo.required) {
                    prefijo.setCustomValidity(!prefijo.value && digitos ? 'Elija el prefijo de este teléfono.' : '');
                    numero.setCustomValidity(prefijo.value && !digitos ? 'Escriba el número o quite el prefijo.' : '');
                }

                document.getElementById(campo).value = prefijo.value && digitos ? prefijo.value + digitos : '';
            }

            $('.cf-registro__telefono').each(function () {
                const grupo = this;
                $(grupo).on('input change', 'select, input', function () { armarTelefono(grupo); });
                armarTelefono(grupo);
            });

            // ── RIF y código: solo dígitos ─────────────────────────────
            $('#rif, #code').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // ── ajaxGeo: carga geográfica en cascada ───────────────────
            // Llena el selector con [{id, name}] y, si había un valor elegido
            // antes de un error de validación (data-anterior), lo vuelve a
            // seleccionar y carga el nivel siguiente.
            function ajaxGeo(url, targetSelector, placeholder) {
                const $target = $(targetSelector);

                $target.empty().append($('<option value="">').text('Cargando…')).prop('disabled', true);

                return $.get(url)
                    .done(function (data) {
                        $target.empty().append($('<option value="">').text(placeholder));
                        if (data && data.length > 0) {
                            $.each(data, function (i, item) {
                                $target.append($('<option>').val(item.id).text(item.name));
                            });
                        } else {
                            $target.find('option').text('No hay opciones disponibles');
                        }
                        $target.prop('disabled', false);

                        const anterior = String($target.data('anterior') || '');
                        $target.removeData('anterior').removeAttr('data-anterior');
                        if (anterior && $target.find('option[value="' + anterior + '"]').length) {
                            $target.val(anterior).trigger('change');
                        }
                    })
                    .fail(function () {
                        $target.empty().append($('<option value="">').text('No se pudo cargar. Recargue la página.')).prop('disabled', false);
                    });
            }

            function reiniciar(selector, texto) {
                $(selector).empty().append($('<option value="">').text(texto)).prop('disabled', true);
            }

            // ── 1. Carga inicial de Estados (tabla exclusiva de Venezuela) ──
            ajaxGeo('{{ url("registro/ajax/estados") }}', '#id_estado', 'Seleccione el estado');

            // ── 2. Estado → Municipios ─────────────────────────────────
            $('#id_estado').on('change', function () {
                const estadoId = $(this).val();
                reiniciar('#id_parroquia', 'Elija un municipio primero');
                if (estadoId) {
                    ajaxGeo('{{ url("registro/ajax/municipios") }}/' + estadoId, '#id_municipio', 'Seleccione el municipio');
                } else {
                    reiniciar('#id_municipio', 'Elija un estado primero');
                }
            });

            // ── 3. Municipio → Parroquias ──────────────────────────────
            $('#id_municipio').on('change', function () {
                const municipioId = $(this).val();
                if (municipioId) {
                    ajaxGeo('{{ url("registro/ajax/parroquias") }}/' + municipioId, '#id_parroquia', 'Seleccione la parroquia');
                } else {
                    reiniciar('#id_parroquia', 'Elija un municipio primero');
                }
            });

            // ── 4. Envío de Código de Verificación ─────────────────────
            function mensajeCodigo(texto, tipo) {
                $('#code_message')
                    .removeClass('text-success text-danger')
                    .addClass(tipo === 'ok' ? 'text-success' : 'text-danger')
                    .text(texto);
            }

            $('#btn_send_code').on('click', function () {
                const email = $.trim($('#email').val());
                const $btn = $(this);

                if (!email || !$('#email')[0].checkValidity()) {
                    mensajeCodigo('Escriba un correo electrónico válido para recibir el código.', 'error');
                    $('#email').trigger('focus');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1" aria-hidden="true"></i> Enviando…');

                $.ajax({
                    url: '{{ route("registro.usuario.enviar_codigo") }}',
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: email
                    },
                    success: function (response) {
                        if (response.status == "1") {
                            mensajeCodigo(response.message + ' Revise también la carpeta de spam.', 'ok');
                            $('#code').trigger('focus');
                            // Espera antes de permitir otro envío.
                            let restante = 60;
                            $btn.text('Reenviar en ' + restante + ' s');
                            const intervalo = setInterval(function () {
                                restante--;
                                $btn.text('Reenviar en ' + restante + ' s');
                                if (restante <= 0) {
                                    clearInterval(intervalo);
                                    $btn.prop('disabled', false).html(ICONO_ENVIAR);
                                }
                            }, 1000);
                        } else {
                            mensajeCodigo(response.message, 'error');
                            $btn.prop('disabled', false).html(ICONO_ENVIAR);
                        }
                    },
                    error: function (xhr) {
                        const mensaje = xhr.status === 429
                            ? 'Demasiados intentos. Espere unos minutos e intente de nuevo.'
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo enviar el código. Intente de nuevo.');
                        mensajeCodigo(mensaje, 'error');
                        $btn.prop('disabled', false).html(ICONO_ENVIAR);
                    }
                });
            });

            // ── 5. Envío del formulario ────────────────────────────────
            // El navegador explica cada campo vacío o mal escrito y no envía;
            // aquí solo se marcan en rojo. Si todo está bien, el botón se
            // bloquea para no enviar el registro dos veces.
            $('#frm_register').find('input, select, textarea').not('[type="hidden"]').on('invalid', function () {
                $(this).addClass('is-invalid');
            });

            $('#frm_register').on('submit', function () {
                $('#btn_registrar').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i> Creando cuenta…');
            });

            // Al corregir un campo marcado, se quita la marca de error.
            $('#frm_register').on('input change', '.is-invalid', function () {
                if (this.checkValidity()) {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
@endsection
