<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('template.head')
    </head>
    <body class="login">
        @yield('content')

        @include('template.script')
    </body>
</html>
