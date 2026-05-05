<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('template.head')
    </head>
    <body >
        <div class="wrapper">
            <!-- Sidebar -->
            @include('template.sidebar')
            <!-- End Sidebar -->

            <div class="main-panel">
                <div class="main-header">
                    <div class="main-header-logo">
                        <!-- Logo Header -->
                        @include('template.logo-header')
                        <!-- End Logo Header -->
                    </div>
                    <!-- Navbar Header -->
                    @include('template.navbar-header')
                    <!-- End Navbar -->
                </div>

                <div class="container container-menu">
                    <div class="page-inner">
                        
                        {{-- Start from here. --}}
                        @yield('content')
                    </div>
                </div>

                {{-- <footer class="footer" style="background-color: #1e293b !important; color: #f8fafc !important; border-top: 1px solid rgba(255,255,255,0.05);">
                    @include('template.footer')
                </footer> --}}
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
