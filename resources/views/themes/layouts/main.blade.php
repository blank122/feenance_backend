@include('themes.partials.auth')
<!DOCTYPE html>
<html lang="en">
    <head>
        @include('themes.partials.head')
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            @include('sweetalert::alert')
            {{-- @include('themes.partials.preloader') --}}
            @include('themes.partials.navbar')
            @include('themes.partials.sidebar')
            <div class="content-wrapper">
                @yield('content')
                <a class="btn btn-primary back-to-top no-print" id="back-to-top" role="button" aria-label="Scroll to top" href="#">
                    <i class="fas fa-chevron-up"></i>
                </a>
            </div>
            @include('themes.partials.footer')
            @include('themes.partials.controlsidebar')
            @include('themes.partials.modals')
        </div>
        @include('themes.partials.scripts')
    </body>
</html>
