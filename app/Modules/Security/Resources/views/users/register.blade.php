@extends('layouts.kaiadmin-login')
@section('content')
<style>
    input[type=text]:focus,  input[type=password]:focus{
        background-color: #FFFF99 !important;
    }
    .form-floating-custom .form-control:focus+label,.form-floating-custom .form-control:not(:placeholder-shown)+label,.form-floating-custom .form-select:focus+label,.form-floating-custom .form-select:not(:placeholder-shown)+label {
        font-weight: bold;
    }
</style>

<div class="wrapper wrapper-login">
    <div class="container container-login animated fadeIn">
        <div class="row mb-2 text-center">
             
             <div class="col-6 col-md-6 col-lg-6 text-center ">
                 <a href="{{route('language', 'es')}}">
                 <i style="display:inline-block" class="iti__flag iti__ve"></i>
                 </a>
             </div>
             <div class="col-6 col-md-6 col-lg-6  text-center ">
                 <a href="{{route('language', 'en')}}">
                 <i style="display:inline-block" class="iti__flag iti__gb"></i>
                 </a>
             </div>
            
            
        </div>
        <h3 class="text-center d-block d-sm-none">
            <img class="img-fluid" src="{{ asset('template/kaiadmin/assets/img/kaiadmin/logo_inac.jpeg')}}" alt="Card image cap">
       
        </h3>
        <h3 class="text-center">
            
            {{__('Sign Up')}}
        </h3>
        <form method="post" action="{{route('register')}}" autocomplete="off" id="frm1">
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
                            placeholder="{{__('username')}}"
                            required
                            />
                        <label for="email">{{__('Email')}}</label>
                        <div id="sendCode" class="icon-right">
                            <i  class="fas fa-qrcode"></i>
                        </div>

                    </div>
                    
                    @error('username')
                    <label id="email-error" class="error" for="email">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="code"
                            name="code"
                            type="text"
                            disabled="disabled"
                            maxlength="6"
                            class="form-control"
                            placeholder=""
                            required
                            />
                        <label for="code">{{__('Code')}}</label>
                        <div class="icon-right">
                            <i id="timer" class="">00:00</i>
                        </div>
                    </div>
                    @error('password')
                    <label id="code-error" class="error" for="code">{{__('This field is required')}}</label>
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
                            id="re_password"
                            name="re_password"
                            type="password"
                            maxlength="16"
                            class="form-control"
                            placeholder=""
                            required
                            />
                        <label for="re_password">{{__('Repeat your Password')}}</label>

                        <div class="show-password icon-right">
                            <i class="icon-eye"></i>
                        </div>

                    </div>
                    @error('password')
                    <label id="re_password-error" class="error" for="re_password">{{__('This field is required')}}</label>
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
                    <div class="row mt-3">
                        <div class="col-8 ">
                        
                            <img id="img-c" class="img-fluid" src="{{ route('captcha', rand(1,999)) }}" alt="captcha">
                        
                        </div>
                        <div class="col-4 mt-1">
                            <div class="row ">
                                <button id="reload-img"   type="button" class="btn btn-large btn-info">
                                    <i class="fa fa-undo"></i>
                                </button>

                            </div>
                        </div>
                        
                    </div>
                    
                </div>

                <div class="form-action mb-3">
                    <button id="checkUser" type="button" class="btn btn-primary w-100 btn-login"><i class="icon-login"></i> {{__('Sign Up')}}</button>

                </div>
                <div class="login-account">
                    
                    <a href="{{route('login')}}" id="show-signup" class="link">{{__("Sign In")}}</a>
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
        
        $("#sendCode").on('click', function (){
            //console.log(); //sfdgsfdg@fdgadsf.adf
            if (  $("#email").valid() && intervalo==null  ){
                showLoading({"icon":"info", "title":"{{__('Requesting Code')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
                $("#email").prop("readonly", true);
                $.post("{{route('register')}}", "email="+$("#email").val()+"&_method=PUT&_token={{ csrf_token() }}"  , function (response){
                    
                    Swal.close();
                    $.notify({
                        icon: 'icon-bell',
                        title: APP_NAME,
                        message: response.message
                    }, {
                        type: response.type,
                        placement: {
                            from: "top",
                            align: "center"
                        },
                        time: 1000
                    });
                    if (response.status==0){
                        $("#code").val("").prop("disabled", true);
                        $("#email").prop("readonly", false).focus();
                    }else{
                        $("#code").prop("disabled", false).focus();
                        intervalo = setInterval(actualizarContador, 1000);
                    }
                    
                }).fail(function (){
                    Swal.close();
                    $("#code").val("").prop("disabled", true);
                    $("#email").prop("readonly", false).focus();
                    $.notify({
                        icon: 'icon-bell',
                        title: APP_NAME,
                        message: "{{__('Error')}}",
                    }, {
                        type: 'danger',
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        time: 1000,
                    });
                });
            }
        });
        @if($errors->any())
            
            $.notify({
                icon: 'icon-bell',
                title: APP_NAME,
                message: "{{$errors->first()}}"
            }, {
                type: 'danger',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 1000,
            });
        @enderror
        
        
        
    });
    function actualizarContador() {
        // Calcular minutos y segundos
        const minutos = Math.floor(tiempoRestante / 60);
        const segundos = tiempoRestante % 60;

        // Formatear el tiempo para que siempre tenga dos dígitos
        const tiempoFormateado = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

        // Mostrar el tiempo en el elemento HTML
        $('#timer').html(tiempoFormateado);

        // Verificar si el tiempo ha llegado a cero
        if (tiempoRestante <= 0) {
            clearInterval(intervalo);
            intervalo= null;
            tiempoRestante=120;
            $("#code").val("").prop("disabled", true);
            $("#email").prop("readonly", false).focus();
        } else {
            tiempoRestante--; // Decrementar el tiempo
        }
    }

    // Llamar a la función cada segundo
    

</script>
@endsection
