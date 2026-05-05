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
            <img class="img-fluid mb-3" src="{{ asset('template/kaiadmin/assets/img/kaiadmin/logo.jpeg')}}" alt="Logo" style="max-height: 80px; border-radius: 10px;">
            <h2 class="fw-bold text-primary">{{__('Sign In')}}</h2>
            <p class="text-muted small">Bienvenido, ingrese sus credenciales para continuar</p>
        </div>
        <form method="post" action="{{route('login')}}" autocomplete="off" id="frm1">
             
            @csrf
            <div class="login-form">
                
                <div class="form-sub">
                    
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="username"
                            name="username"
                            maxlength="50"
                            type="text"
                            autofocus=""
                            class="form-control"
                            placeholder="{{__('username')}}"
                            required
                            />
                        <label for="username">{{__('Username')}}</label>
                    </div>
                    
                    @error('username')
                    <label id="username-error" class="error" for="username">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            maxlength="16"
                            class="form-control"
                            placeholder="{{__('password')}}"
                            required
                            />
                        <label for="password">{{__('Password')}}</label>

                        <div class="show-password">
                            <i class="icon-eye"></i>
                        </div>

                    </div>
                    @error('password')
                    <label id="password-error" class="error" for="password">{{__('This field is required')}}</label>
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
                        <i class="fas fa-sign-in-alt me-2"></i> {{__('Iniciar Sesión')}}
                    </button>
                </div>

                <div class="login-account text-center">
                    <p class="mb-1 text-muted small">¿No tienes cuenta todavía?</p>
                    <a href="{{route('registro.usuario')}}" id="show-signup" class="text-primary fw-bold" style="text-decoration: none;">
                        <i class="fas fa-user-plus me-1"></i> Regístrate aquí
                    </a>
                </div>
                
                <div class="login-account text-center mt-2">
                    <a href="{{route('recovery')}}" id="show-recovery" class="text-muted small">
                        <i class="fas fa-key me-1"></i> {{__("Recover Password")}}
                    </a>
                </div>
                
                
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
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
        
        setInterval(function (){  $("#reload-img").click(); }, 120000);
        
    });

</script>
@endsection
