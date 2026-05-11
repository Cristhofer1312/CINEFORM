@extends('layouts.kaiadmin-select-profile')
@section('content')
<body>
    <h3>Selecciona tu perfil</h3>
    <div class="row">
        @foreach ($perfiles as $perfil)
            <a href="{{ route('usuario.set_perfil', ['id_rol' => $perfil->crypt_id]) }}" class="btn btn-primary btn-block btn-lg">
                {{ __($perfil->name) }}
            </a>
        @endforeach
       {{--  @foreach($perfiles as $perfil)
            <button>{{ $perfil->name }}</button>
        @endforeach --}}
    </div>
</body>
<style>
    input[type=text]:focus,  input[type=password]:focus{
        background-color: #FFFF99 !important;
    }
    .form-floating-custom .form-control:focus+label,.form-floating-custom .form-control:not(:placeholder-shown)+label,.form-floating-custom .form-select:focus+label,.form-floating-custom .form-select:not(:placeholder-shown)+label {
        font-weight: bold;
    }

     <style>
        /* Solo afecta esta página, nada más */
        .custom-profile-select,
        .custom-profile-select .wrapper,
        .custom-profile-select-panel {
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #f8f9fc;
        }

        .custom-profile-select-panel {
            width: 100% !important;
            margin-top: 0 !important;
            padding: 0 !important;
        }

        .custom-profile-select .container,
        .custom-profile-select .container-fluid,
        .custom-profile-select .container-menu {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .custom-profile-select-footer {
            width: 100% !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
            position: relative !important;
            bottom: 0 !important;
        }
    </style>
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
