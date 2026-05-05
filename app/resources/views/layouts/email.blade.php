<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>{{ config('app.name') }} </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
        <link rel="icon" href="{{ asset('template/kaiadmin/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />
        
        <!-- CSS Files -->
        <link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/plugins.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/kaiadmin.min.css') }}" />

        

        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link rel="stylesheet" href="{{ asset('template/kaiadmin/assets/css/demo.css') }}" />

        <link rel="stylesheet" href="{{ asset('css/main.css') }}" />

    </head>
    <body class="">
        
        @yield('content')
    </body>
</html>
