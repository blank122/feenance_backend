<aside class="main-sidebar sidebar-light-purple elevation-4">
    {{-- Brand Logo --}}
    {{-- <a href="{{ action('App\Http\Controllers\MainController@home') }}" class="brand-link">
        <img src="{{ asset('images/logo.png') }}" alt="Infinit logo" class="brand-image text-center" style="width:100px;height:100px;">
        <span class="brand-text font-weight-light">&nbsp;</span>
    </a> --}}

     <!-- Brand Logo -->
    <a href="{{ action('App\Http\Controllers\MainController@home') }}" class="brand-link" id="logoPic">
        <img src="{{ asset('images/accounts/logo/logo.png') }}" alt="School Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">INSPIRE-MS</span>
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">
      {{-- Sidebar user panel --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset(getAvatar(session('usr_id'))) }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="javascipt:void(0)"data-toggle="modal" data-target="#userInfoModal"  class="d-block">{{ session('usr_first_name') }}</a>
                <span class="brand-text font-weight-light" style="color:gray;"><small>{{ session('acc_name') }}</small></span>
            </div>
        </div>

        {{-- Sidebar Menu --}}
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                {{-- Home  --}}
                <li class="nav-item">
                    <a href="{{ action('App\Http\Controllers\MainController@home') }}" class="nav-link {{ request()->is('admin/home') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-home"></i>
                        <p>Home</p>
                    </a>
                </li>

                {{-- Admin --}}
                {{-- @if(session('usr_is_admin') == '1') --}}
                    <li class="nav-header">ADMINISTRATOR</li>
                    <li class="nav-item {{ request()->is('user/*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('user/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                User Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ action('App\Http\Controllers\UserController@active') }}" class="nav-link {{ request()->is('user/list/active') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Active Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ action('App\Http\Controllers\UserController@inactive') }}" class="nav-link {{ request()->is('user/list/inactive') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Inactive Users</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                {{-- @endif --}}

                {{-- Signout  --}}
                <li class="nav-item">
                    <a href="{{ action('App\Http\Controllers\LoginController@logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-sign-out"></i>
                        <p>Sign out</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
