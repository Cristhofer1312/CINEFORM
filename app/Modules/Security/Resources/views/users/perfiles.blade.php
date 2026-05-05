@extends('layouts.kaiadmin-select-profile')

@section('content')
<body>
    <h3 class="text-center mb-4">{{ __('Selecciona tu perfil') }}</h3>
    <div class="row justify-content-center">
        <div class="col-md-4 profile-btn">
                <a href="#"
                   class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-shield mr-2"></i> NOMRE
                </a>
            </div>
       {{--  @foreach(Auth::user()->getProfiles() as $perfiles =>$perfil)
        {{dd($perfil)}}
            <div class="col-md-4 profile-btn">
                <a href="{{ route('usuario.seleccionarPerfil', ['id_rol' => $perfil['id_rol']]) }}"
                   class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-shield mr-2"></i> {{ __($perfil['name']) }}
                </a>
            </div>
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
