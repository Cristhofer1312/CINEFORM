@extends('layouts.kaiadmin-login')
@section('content')
    <style>
        /* Login: sala de cine a oscuras con el haz del proyector (img/login-sala.svg).
           Solo esta pantalla; registro y recuperación siguen con body.login. */
        .cf-login {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 440px);
            gap: clamp(2rem, 5vw, 5rem);
            align-items: center;
            padding: clamp(1.5rem, 4vw, 4rem) clamp(1.5rem, 6vw, 6rem);
            background: #070d22 url("{{ asset('img/login-sala.svg') }}?v={{ filemtime(public_path('img/login-sala.svg')) }}") 20% center / cover no-repeat;
        }

        .cf-login__marca {
            align-self: end;
            max-width: 34rem;
            padding-bottom: clamp(0rem, 4vh, 3rem);
            color: #f8fafc;
        }

        .cf-login__titulo {
            margin: 0 0 1rem;
            font-size: clamp(2.75rem, 6vw, 5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.035em;
            color: #f8fafc;
        }

        .cf-login__lema {
            margin: 0;
            max-width: 30ch;
            font-size: clamp(1rem, 1.4vw, 1.25rem);
            line-height: 1.5;
            color: #cbd5e1;
            text-wrap: pretty;
        }

        .cf-login__tarjeta {
            width: 100%;
            padding: clamp(1.75rem, 3vw, 2.5rem);
            background: var(--cf-surface);
            border-radius: var(--cf-radius-card);
            box-shadow: 0 24px 64px rgba(2, 6, 23, 0.45), 0 2px 6px rgba(2, 6, 23, 0.2);
            animation: cf-login-entrada 520ms var(--cf-ease) both;
        }

        @keyframes cf-login-entrada {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .cf-login__tarjeta { animation: none; }
        }

        .cf-login__cabecera {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        /* Logo del CNAC en PNG transparente (img/marca); proporción 1.15:1. */
        .cf-login__logo {
            width: auto;
            height: 72px;
            flex: none;
        }

        .cf-login__cabecera h2 {
            margin: 0 0 0.25rem;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--cf-ink);
        }

        .cf-login__cabecera p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--cf-muted);
        }

        .cf-login .form-floating > .form-control {
            border-color: var(--cf-border-strong);
            border-radius: var(--cf-radius-control);
        }

        .cf-login .form-floating > .form-control:focus {
            border-color: var(--cf-blue);
            box-shadow: var(--cf-focus-ring);
        }

        .cf-login .form-floating > .form-control:focus ~ label {
            color: var(--cf-blue);
        }

        .cf-login .form-floating > label {
            color: var(--cf-muted);
        }

        /* Espacio para el ojo de "mostrar contraseña" (KaiAdmin lo posiciona a 20px). */
        .cf-login #password {
            padding-right: 3.25rem;
        }

        .cf-login .show-password {
            color: var(--cf-muted);
            border-radius: 6px;
        }

        .cf-login .show-password:hover,
        .cf-login .show-password:focus-visible {
            color: var(--cf-blue);
            outline: none;
            box-shadow: var(--cf-focus-ring);
        }

        .cf-login__olvido {
            display: block;
            margin: -0.25rem 0 1.25rem;
            text-align: right;
            font-size: 0.8125rem;
        }

        .cf-login a {
            color: var(--cf-blue);
            font-weight: 600;
            text-decoration: none;
            text-underline-offset: 3px;
        }

        .cf-login a:hover,
        .cf-login a:focus-visible {
            color: var(--cf-blue-hover);
            text-decoration: underline;
        }

        .cf-login__captcha {
            display: flex;
            align-items: stretch;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .cf-login__captcha-img {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 58px;
            padding: 0.25rem;
            background: var(--cf-surface-subtle);
            border: 1px solid var(--cf-border);
            border-radius: var(--cf-radius-control);
        }

        .cf-login__captcha-img img {
            max-height: 48px;
        }

        .cf-login__captcha .btn {
            width: 58px;
            border-radius: var(--cf-radius-control);
        }

        .cf-login__entrar {
            margin-top: 1.5rem;
            padding-block: 0.8rem;
            font-weight: 700;
            border-radius: var(--cf-radius-pill);
        }

        .cf-login__registro {
            margin: 1.5rem 0 0;
            padding-top: 1.25rem;
            border-top: 1px solid var(--cf-border);
            text-align: center;
            font-size: 0.875rem;
            color: var(--cf-muted);
        }

        @media (max-width: 991.98px) {
            .cf-login {
                grid-template-columns: minmax(0, 440px);
                justify-content: center;
                align-content: center;
                gap: 1.75rem;
            }

            .cf-login__marca {
                align-self: auto;
                padding-bottom: 0;
                text-align: center;
            }

            .cf-login__titulo {
                margin-bottom: 0.5rem;
                font-size: clamp(2.25rem, 10vw, 3rem);
            }

            .cf-login__lema {
                margin-inline: auto;
                font-size: 0.9375rem;
            }
        }

        @media (max-width: 399.98px) {
            .cf-login { padding-inline: 1rem; }
            .cf-login__cabecera { gap: 0.75rem; }
            .cf-login__logo { height: 56px; }
        }
    </style>

    <div class="cf-login">
        <section class="cf-login__marca">
            <h1 class="cf-login__titulo">CINEFORM</h1>
            <p class="cf-login__lema">Cursos y certificaciones del Centro Nacional Autónomo de Cinematografía.</p>
        </section>

        <main class="container-login cf-login__tarjeta">
            <div class="cf-login__cabecera">
                <img class="cf-login__logo" src="{{ asset('img/marca/logo-cnac.png') }}?v={{ filemtime(public_path('img/marca/logo-cnac.png')) }}"
                    alt="Centro Nacional Autónomo de Cinematografía">
                <div>
                    <h2>{{ __('Iniciar Sesión') }}</h2>
                    <p>Bienvenido, ingrese sus credenciales para continuar.</p>
                </div>
            </div>

            <form method="post" action="{{ route('login') }}" autocomplete="off" id="frm1">
                @csrf
                <div class="login-form">
                    <div class="form-floating mb-3">
                        <input id="username" name="username" maxlength="50" type="text" autofocus
                            class="form-control" placeholder="{{ __('username') }}" autocomplete="username" required />
                        <label for="username">{{ __('Username') }}</label>
                    </div>
                    @error('username')
                        <label id="username-error" class="error" for="username">{{ __('This field is required') }}</label>
                    @enderror

                    <div class="form-floating mb-2">
                        <input id="password" name="password" type="password" maxlength="16" class="form-control"
                            placeholder="{{ __('password') }}" autocomplete="current-password" required />
                        <label for="password">{{ __('Password') }}</label>
                        <div class="show-password" role="button" tabindex="0" aria-label="Mostrar u ocultar la contraseña">
                            <i class="icon-eye"></i>
                        </div>
                    </div>
                    @error('password')
                        <label id="password-error" class="error" for="password">{{ __('This field is required') }}</label>
                    @enderror

                    <a href="{{ route('recovery') }}" id="show-recovery" class="cf-login__olvido">{{ __('Recover Password') }}</a>

                    <div class="cf-login__captcha">
                        <div class="cf-login__captcha-img">
                            <img id="img-c" src="{{ route('captcha', rand(1, 999)) }}" alt="Código de verificación">
                        </div>
                        <button id="reload-img" type="button" class="btn btn-outline-primary"
                            title="Recargar código" aria-label="Recargar código de verificación">
                            <i class="fa fa-sync-alt"></i>
                        </button>
                    </div>

                    <div class="form-floating">
                        <input id="captcha" name="captcha" maxlength="5" minlength="5" type="text"
                            style="text-transform: uppercase" class="form-control" placeholder="{{ __('Captcha') }}"
                            autocomplete="off" required />
                        <label for="captcha">{{ __('Captcha') }}</label>
                    </div>
                    @error('captcha')
                        <label id="captcha-error" class="error" for="captcha">{{ __('This field is required') }}</label>
                    @enderror

                    <button id="checkUser" type="button" class="btn btn-primary btn-lg w-100 cf-login__entrar">
                        {{ __('Iniciar Sesión') }}
                    </button>

                    <p class="cf-login__registro">
                        ¿No tienes cuenta todavía?
                        <a href="{{ route('registro.usuario') }}" id="show-signup">Regístrate aquí</a>
                    </p>
                </div>
            </form>
        </main>
    </div>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function (event) {
            $("#checkUser").on('click', function () {
                if ($('#frm1').valid() == true) {
                    $("#password").attr("type", "password");
                    showLoading({ "icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>' });
                    $('#frm1').submit();
                } else {

                    $.notify({
                        icon: 'icon-bell',
                        title: APP_NAME,
                        message: '{{__("Missing Required Fields")}}',
                    }, {
                        type: 'secondary',
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        time: 1000,
                    });


                }
            });

            // El botón es type="button": sin esto, Enter en un campo no envía el formulario.
            $("#frm1 input").on("keydown", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    $("#checkUser").trigger("click");
                }
            });

            $(".show-password").on("keydown", function (e) {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    $(this).trigger("click");
                }
            });

            $("#reload-img").on("click", function () {
                showLoading({ "icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>' });
                $('#img-c').attr('src', '{{url("security/captcha")}}/' + Math.floor(Math.random() * 100000));
            });

            $("#img-c").on("load", function () {
                Swal.close();
            });


            @if(session()->has('success'))
                $.notify({
                    icon: 'icon-bell',
                    title: APP_NAME,
                    message: "{{session()->get('success')}}",
                }, {
                    type: 'success',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 1000,
                });

            @endif

            @if($errors->any() && $errors->has('error-message'))
                $.notify({
                    icon: 'icon-bell',
                    title: APP_NAME,
                    message: "{{ $errors->first('error-message') }}",
                }, {
                    type: 'danger',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 5000,
                });

            @endif

        });

    </script>
@endsection
