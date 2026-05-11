@extends($layout ?? 'layouts.kaiadmin-login')
@section('content')

@php
    $withinSession  = $withinSession ?? false;
    $perfilActualId = session()->get('profile_id');
    $perfilActual   = $withinSession ? ($perfiles->firstWhere('id', $perfilActualId) ?? null) : null;
@endphp

<style>
    .profile-card {
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        padding: 25px 20px;
        text-align: center;
        background-color: #ffffff;
        color: #555555;
        text-decoration: none;
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ebedf2;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04);
    }
    .profile-card:hover, .profile-card:focus {
        transform: translateY(-5px);
        background-color: #1572E8;
        color: #ffffff;
        box-shadow: 0 8px 15px rgba(21, 114, 232, 0.2);
        text-decoration: none;
        border-color: #1572E8;
    }
    .profile-card i {
        font-size: 35px;
        margin-bottom: 10px;
        display: block;
    }
    .profile-card h4 {
        margin: 0;
        font-weight: 600;
        font-size: 18px;
    }
    .container-login {
        width: 100% !important;
        max-width: 500px !important;
    }
    /* Pastilla de perfil activo */
    .current-profile-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(40, 208, 148, 0.12);
        border: 1px solid rgba(40, 208, 148, 0.35);
        color: #555;
        font-size: 13px;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .current-profile-pill strong {
        color: #1572E8;
        font-weight: 700;
    }
</style>

<div class="wrapper wrapper-login">
    <div class="container container-login animated fadeIn">

        {{-- Logo en móvil --}}
        <h3 class="text-center d-block d-sm-none mb-4">
            <img class="img-fluid" src="{{ asset('template/kaiadmin/assets/img/kaiadmin/logo.jpeg') }}" alt="Logo" style="max-height: 80px;">
        </h3>

        {{-- Saludo --}}
        <h3 class="text-center mb-1">
            {{ __('Bienvenido') }},
            <span class="text-primary">{{ Auth::user()->full_name ?? Auth::user()->username }}</span>
        </h3>

        {{-- Perfil activo (solo en modo sesión) --}}
        @if ($withinSession && $perfilActual)
            <div class="text-center mb-1">
                <span class="current-profile-pill">
                    <i class="fas fa-circle" style="font-size:7px; color:#28d094;"></i>
                    {{ __('Perfil activo') }}: <strong>{{ __($perfilActual->name) }}</strong>
                </span>
            </div>
        @endif

        <p class="text-center mb-4" style="color: #777; font-size: 15px;">
            {{ __('Seleccione el perfil con el que desea trabajar') }}
        </p>

        {{-- Lista de perfiles --}}
        <div class="row">
            @foreach ($perfiles as $perfil)
                @php $esCurrent = ($perfil->id == $perfilActualId); @endphp
                {{-- En sesión activa, omitir el perfil ya seleccionado --}}
                @if ($withinSession && $esCurrent) @continue @endif
                <div class="col-12">
                    <a href="{{ route('usuario.set_perfil', ['id_rol' => $perfil->crypt_id]) }}" class="profile-card">
                        <i class="icon-user"></i>
                        <h4>{{ __($perfil->name) }}</h4>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Botón inferior: Cancelar (sesión) o Cerrar Sesión (login) --}}
        <div class="form-action mb-1 text-center mt-4">
            @if ($withinSession)
                <a href="{{ route('home') }}" class="btn btn-border btn-secondary w-100">
                    <i class="icon-close"></i> {{ __('Cancel') }}
                </a>
            @else
                <a href="{{ route('logout') }}" class="btn btn-border btn-danger w-100">
                    <i class="icon-logout"></i> {{ __('Cancel and Logout') }}
                </a>
            @endif
        </div>

    </div>
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function (event) {
        @if ($withinSession)
            {{-- Feedback visual al seleccionar en sesión activa --}}
            document.querySelectorAll('.profile-card').forEach(function (card) {
                card.addEventListener('click', function (e) {
                    e.preventDefault();
                    var href = this.getAttribute('href');

                    document.querySelectorAll('.profile-card').forEach(function (c) {
                        c.style.pointerEvents = 'none';
                        c.style.opacity = '0.5';
                    });
                    this.style.opacity = '1';

                    showLoading({
                        "icon": "info",
                        "title": "{{ __('Switching profile...') }}",
                        "html": '<i class="fas fa-spinner fa-spin"></i>'
                    });

                    setTimeout(function () { window.location.href = href; }, 300);
                });
            });
        @else
            {{-- Notificación de bienvenida en login --}}
            $.notify({
                icon: 'icon-bell',
                title: APP_NAME,
                message: '{{ __("Authentication successful. Select profile.") }}',
            }, {
                type: 'info',
                placement: { from: "bottom", align: "right" },
                time: 1000,
            });
        @endif
    });
</script>

@endsection
