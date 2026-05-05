@extends('layouts.kaiadmin-login')
@section('content')
<style>
    input[type=text]:focus, input[type=password]:focus, select:focus {
        background-color: #f7faff !important;
        border-color: #3f67f0 !important;
        box-shadow: 0 0 0 0.25rem rgba(63, 103, 240, 0.15) !important;
        outline: none;
    }
    .form-floating-custom .form-control:focus+label,
    .form-floating-custom .form-control:not(:placeholder-shown)+label {
        font-weight: 700;
        color: #3f67f0;
    }
    .container-login {
        max-width: 500px !important;
        width: 100% !important;
    }
    .wrapper-login {
        min-height: 100vh;
    }
</style>

<div class="wrapper-login d-flex justify-content-center align-items-center py-5">
    <div class="container-login bg-white shadow-lg rounded-4 p-5 animated fadeIn">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="fas fa-key fa-3x text-primary"></i>
            </div>
            <h2 class="fw-bold text-primary">{{__('Recover Password')}}</h2>
            <p class="text-muted small">Ingrese su correo electrónico para recibir las instrucciones de recuperación</p>
        </div>
        <form method="post" action="{{route('recovery')}}" autocomplete="off" id="frm1">
            @csrf
            <input type="hidden" value="POST" name="_method" />
            
            <div class="login-form">
                <div class="form-sub">
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="email"
                            name="email"
                            maxlength="50"
                            type="text"
                            autofocus=""
                            class="form-control email"
                            placeholder="{{__('')}}"
                            required
                            />
                        <label for="email">{{__('Email')}}</label>
                        

                    </div>
                    
                    @error('username')
                    <label id="email-error" class="error" for="email">{{__('This field is required')}}</label>
                    @enderror
                    
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="captcha"
                            name="captcha"
                            maxlength="5"
                            minlength="5"
                            type="text"
                            style="text-transform: uppercase"
                            class="form-control"
                            placeholder="{{__('Captcha')}}"
                            required
                            />
                        <label for="captcha">{{__('Captcha')}}</label>
                    </div>
                    
                    @error('captcha')
                    <label id="captcha-error" class="error" for="captcha">{{__('This field is required')}}</label>
                    @enderror
                    <div class="row mt-4 align-items-center">
                        <div class="col-8">
                            <div class="border rounded p-1 bg-light text-center">
                                <img id="img-c" class="img-fluid" src="{{ route('captcha', rand(1,999)) }}" alt="captcha" style="max-height: 45px;">
                            </div>
                        </div>
                        <div class="col-4">
                            <button id="reload-img" type="button" class="btn btn-outline-primary w-100" title="Recargar Captcha">
                                <i class="fa fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-action mb-4 mt-4">
                    <button id="checkUser" type="button" class="btn btn-primary btn-lg w-100 shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i> {{__('Enviar Solicitud')}}
                    </button>
                </div>
                <div class="login-account text-center">
                    <a href="{{route('login')}}" id="show-signup" class="text-muted small" style="text-decoration: none;">
                        <i class="fas fa-arrow-left me-1"></i> {{__("Volver al Inicio de Sesión")}}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    let tiempoRestante = 120;
    let intervalo=null;
    document.addEventListener("DOMContentLoaded", function (event) {
        $("#checkUser").on('click', function () {
            if ($('#frm1').valid() == true) {
                $("#password").attr("type","password");
                showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
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
        
        $("#reload-img").on("click", function (){
            showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
            $('#img-c').attr('src', '{{url("security/captcha")}}/'+Math.floor(Math.random() * 100000));
        });
        
        $("#img-c").on("load", function() {
            Swal.close();
        });
        
        
        
        
        
    });
    

</script>
@endsection
