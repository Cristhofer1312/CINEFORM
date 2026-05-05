<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>{{ config('app.name') }} </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
<link rel="icon" href="{{ asset('template/kaiadmin/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />

<!-- Fonts and icons -->
<script src="{{ asset('template/kaiadmin/assets/js/plugin/webfont/webfont.min.js') }}"></script>
<script>
WebFont.load({
    google: {families: ['Public Sans:300,400,500,600,700']},
    custom: {
        families: [
            'Font Awesome 5 Solid',
            'Font Awesome 5 Regular',
            'Font Awesome 5 Brands',
            'simple-line-icons'
        ],
        urls: ['{{ asset("template/kaiadmin/assets/css/fonts.min.css") }}']
    },
    active: function () {
        sessionStorage.fonts = true;
    }
});
</script>

<!-- CSS Files -->
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/bootstrap.min.css') }}" />
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/plugins.min.css') }}" />
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/kaiadmin.min.css') }}" />

<!-- Jquery Crop -->
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/js/plugin/jquery.crop/cropper.min.css') }}" />
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/js/plugin/jquery.crop/dropzone.css') }}" />


<!-- intl-tel -->
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input.css') }}" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Datatable Responsive -->
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/js/plugin/datatables/responsive/css/responsive.bootstrap4.min.css') }}" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- CSS Just for demo purpose, don't include it in your project -->
<link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/demo.css') }}" />

<link rel="stylesheet" href="{{ asset('css/main.css') }}" />


