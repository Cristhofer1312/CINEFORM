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
                        @if(View::hasSection('breadcrumbs'))
                            <div class="page-header d-flex align-items-center justify-content-between mb-3 pt-2">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                                                <i class="fas fa-home me-1"></i> {{ __('Inicio') }}
                                            </a>
                                        </li>
                                        @yield('breadcrumbs')
                                    </ol>
                                </nav>
                                @hasSection('page-actions')
                                    <div class="page-actions">
                                        @yield('page-actions')
                                    </div>
                                @endif
                            </div>
                        @elseif(!request()->routeIs('home') && !request()->routeIs('security.home') && View::hasSection('title'))
                            <div class="page-header d-flex align-items-center justify-content-between mb-3 pt-2">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                                                <i class="fas fa-home me-1"></i> {{ __('Inicio') }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">
                                            @yield('title')
                                        </li>
                                    </ol>
                                </nav>
                                @hasSection('page-actions')
                                    <div class="page-actions">
                                        @yield('page-actions')
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Start from here. --}}
                        @yield('content')
                    </div>
                </div>

                {{-- <footer class="footer" style="background-color: var(--cf-chrome) !important; color: var(--cf-chrome-text) !important; border-top: 1px solid rgba(255,255,255,0.05);">
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
