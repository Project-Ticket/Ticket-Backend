@php
    use App\Services\SettingService;
@endphp
<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ url('/admin') }}/" data-template="horizontal-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    @php
        $setting = SettingService::get('app_name', 'default app');
    @endphp
    <title>{{ $setting }}</title>

    @include('layouts.admin.components.style_css')
    <style>
        .bg-menu-theme.menu-horizontal .menu-item.active>.menu-link:not(.menu-toggle) {
            background: linear-gradient(72.47deg, #7367f0 22.16%, rgba(115, 103, 240, 0.7) 76.47%) !important;
            color: #fff !important;
            box-shadow: 0px 2px 6px 0px rgba(115, 103, 240, 0.48);
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Header -->
            @include('layouts.admin.header')
            <!-- / Header -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Navbar -->
                    @include('layouts.admin.navbar')
                    <!-- / Navbar -->

                    <!-- Content -->
                    <div class="container-fluid flex-grow-1 container-p-y">
                        @stack('page-header')
                        @yield('content')
                    </div>
                    <!--/ Content -->

                    <!-- Footer -->
                    @include('layouts.admin.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!--/ Content wrapper -->
            </div>

            <!--/ Layout container -->
        </div>
    </div>

    <!-- Global Modal -->
    <div class="modal modal-blur fade" id="globalModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modal-content-container">
                <!-- Konten AJAX akan dimuat di sini -->
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="globalOffcanvas" aria-labelledby="globalOffcanvasTitle">
        <div id="offcanvas-content-container">
            <div class="offcanvas-body text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>

    <!--/ Layout wrapper -->
    @include('layouts.admin.components.style_js')
    @stack('scripts')
</body>

</html>