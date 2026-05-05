@extends('layouts.kaiadmin-menu')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center" style="min-height: 65vh;">
        <div class="text-center bg-white shadow-lg p-5" style="border-radius: 2.5rem; max-width: 600px;">
            <h1 class="display-5 fw-bold text-dark mb-2">¡Bienvenido(a)!</h1>
            <h2 class="text-secondary">{{ Auth::user()->full_name }}</h2>
            <p class="lead text-muted mt-4 mb-0">Selecciona una opción del menú lateral para comenzar a trabajar.</p>
        </div>
    </div>

    <style>
        input[type=text]:focus,
        input[type=password]:focus {
            background-color: #FFFF99 !important;
        }

        .form-floating-custom .form-control:focus+label,
        .form-floating-custom .form-control:not(:placeholder-shown)+label,
        .form-floating-custom .form-select:focus+label,
        .form-floating-custom .form-select:not(:placeholder-shown)+label {
            font-weight: bold;
        }
    </style>


    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function (event) {

            $.notify({
                icon: 'icon-bell',
                title: APP_NAME,
                message: '{{__("Welcome")}}',
            }, {
                type: 'secondary',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 1000,
            });
        });

    </script>
@endsection