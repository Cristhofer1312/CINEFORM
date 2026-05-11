<div class="sidebar custom-sidebar" data-background-color="dark">
    <style>
        .custom-sidebar {
            background-color: #1e293b !important;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.15) !important;
        }
        .custom-sidebar .nav.nav-secondary > .nav-item > a > p,
        .custom-sidebar .nav.nav-secondary > .nav-item > a > i,
        .custom-sidebar .nav.nav-secondary > .nav-item > a > span,
        .custom-sidebar .nav-collapse li a span.sub-item,
        .custom-sidebar .nav-section .text-section,
        .custom-sidebar .nav-section .sidebar-mini-icon i {
            color: #f8fafc !important;
            font-weight: 500;
        }
        .custom-sidebar .nav.nav-secondary > .nav-item > a:hover,
        .custom-sidebar .nav-collapse li a:hover {
            background-color: rgba(255,255,255,0.08) !important;
            border-radius: 8px;
        }
    </style>
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" style="background-color: #1e293b !important;">
            <a href="{{route('home')}}" class="logo">
                <img src="{{asset('template/kaiadmin/assets/img/kaiadmin/logo.jpeg')}}" alt="navbar brand"
                    class="navbar-brand" height="40" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section text-center">MENU</h4>
                </li>
                @foreach(Auth::user()->captureMenu() as $key => $value)
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#menu{{$key}}">
                            <i class="{{$value['icon']}}"></i>
                            <p>{{__($value['name'])}}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="menu{{$key}}">
                            <ul class="nav nav-collapse">
                                @foreach($value['processes'] as $value2)
                                    <li>
                                        <a href="{{route($value2['route'])}}">
                                            <span class="sub-item">{{__($value2['name'])}}</span>
                                        </a>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>