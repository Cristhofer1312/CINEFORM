<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('template.head')
</head>

<body>
    <div class="wrapper">
        <div style="width:100%; margin-top:0px" class="main-panel">
            @yield('content')



            <footer class="footer" style="background-color: #1e293b !important; color: #f8fafc !important; border-top: 1px solid rgba(255,255,255,0.05);">
                @include('template.footer')
            </footer>
        </div>

        <!-- Custom template | don't include it in your project! -->

        <!-- End Custom template -->
    </div>

    @include('template.script')
</body>

</html>