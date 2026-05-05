<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('template.head')
    </head>
    <body class="login bg-primary">
        @yield('content')
 
        @include('template.script-login')
        
    </body>
</html>
