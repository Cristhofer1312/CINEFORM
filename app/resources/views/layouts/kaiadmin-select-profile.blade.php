<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('template.head')
        <title>{{ __('Seleccionar Perfil') }}</title>
        <style>
            .profile-btn {
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body >
        <div class="wrapper">
            <div style="width:100%; margin-top:0px; " class="main-panel">
                <div style="width:100%; margin-top:0px; "  class="main-header">
                    <div class="main-header-logo">
                        <!-- Logo Header -->
                        @include('template.logo-header')
                        <!-- End Logo Header -->
                    </div>
                    <!-- Navbar Header -->
                    @include('template.navbar-header')
                    <!-- End Navbar -->
                </div>

             <div style="margin-top:0px" class="container container-menu">
                    <div class="page-inner">   
                        @yield('content')
                    </div>
                </div> 
                <footer class="footer">
                    @include('template.footer')
                </footer>
            </div>

            <!-- Custom template | don't include it in your project! -->
            {{-- <div class="custom-template">
        <div class="title">Settings</div>
        @include('template.color')
      </div> --}}
            <!-- End Custom template -->
        </div>

        @include('template.script')
    </body>
</html>

{{-- <body style="margin: 0; padding: 0; height: 100vh; display: flex; flex-direction: column;">
    <div class="wrapper" style="flex: 1; display: flex; flex-direction: column;">
        <div style="flex: 1; overflow-y: auto;"> <!-- Contenido que crece -->
            <div style="width:100%; margin-top:0px;" class="main-panel">
                <div style="width:100%; margin-top:0px;" class="main-header">
                    @include('template.logo-header')
                    @include('template.navbar-header')
                </div>

                <div style="margin-top:0px" class="container container-menu">
                    <div class="page-inner">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer" style="width:100%; margin-top:0px;">
            @include('template.footer')
        </footer>
    </div>

    @include('template.script')
</body> --}}