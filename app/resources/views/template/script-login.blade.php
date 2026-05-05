


<!-- intl-tel -->

<script src="{{asset('template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input.js')}}" integrity="sha512-LBJerAF8rqMX9jWBh3yhK+BTBJHy5GMiD7Krznwc8rRdbs2IMxsVWA9ROc+/Sn2Xvhh4bP2UDFPkXZPdDFmocQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('template/kaiadmin/assets/js/plugin/intl-tel/lang/'.app()->getLocale().'.js')}}" ></script>




<!--   Core JS Files   -->
<script src="{{asset('template/kaiadmin/assets/js/core/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('template/kaiadmin/assets/js/core/popper.min.js')}}"></script>
<script src="{{asset('template/kaiadmin/assets/js/core/bootstrap.min.js')}}"></script>

<!-- Moment JS -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/moment/moment.min.js')}}" ></script>
<script src="{{asset('template/kaiadmin/assets/js/plugin/moment/'.app()->getLocale().'.js')}}" ></script>

<!-- DateTimePicker -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/datepicker/bootstrap-datetimepicker.min.js')}}" ></script>


<!-- Kaiadmin JS -->
<script src="{{asset('template/kaiadmin/assets/js/kaiadmin.min.js')}}"></script>

<!-- Kaiadmin DEMO methods, don't include it in your project! -->
<script src="{{asset('template/kaiadmin/assets/js/setting-demo.js')}}"></script>
<script src="{{asset('template/kaiadmin/assets/js/login.js')}}"></script>


<!-- JQUERY validate -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/jquery.validate/jquery.validate.min.js')}}"></script>
<script src="{{asset('template/kaiadmin/assets/js/plugin/jquery.validate/localization/messages_'. str_replace('_', '-', app()->getLocale()) .'.min.js')}}"></script>


<!-- Sweet Alert -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/sweetalert/sweetalert2.min.js')}}"></script>

<!-- Select 2 -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/select2/select2.full.min.js')}}"></script>


<!-- Bootstrap Notify -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js')}}"></script>

<!-- Magnifi POPU -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/jquery.magnific-popup/jquery.magnific-popup.min.js')}}"></script>

<!-- MAIn -->
<script src="{{asset('js/menu.js')}}"></script>

<script type="text/javascript">
    var APP_NAME = "{{ config('app.name') }}";
    var APP_ROUTE = "{{ asset('/') }}";
  
    $(document).ready(function (){
        @if(session()->has('success'))
            showNotify('success', "{{ session()->get('success')}}");
        @endif
        
        
        @if($errors->any())
            showNotify('danger', '{{$errors->first()}}');
        @enderror


          
    });
</script>