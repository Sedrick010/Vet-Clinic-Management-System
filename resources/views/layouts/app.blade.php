<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        <title>
            @yield('title', 'Vet Clinic Management System')
        </title>
        
        <!-- Fonts and icons -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
        
        <!-- Font Awesome Icons -->
        <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
        
        <!-- CSS Files -->
        <link id="pagestyle" href="{{ asset('soft-ui-dashboard-laravel-master/public/assets/css/soft-ui-dashboard.css') }}" rel="stylesheet" />
        
        <!-- Custom CSS -->
        @stack('css')
    </head>

    <body class="g-sidenav-show bg-gray-100">
        @if(isset($isSidebar) && $isSidebar)
            @include('layouts.sidebar')
        @endif
        
        <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg overflow-hidden">
            @include('layouts.navbar')
            
            <div class="container-fluid py-4">
                @yield('content')
                
                @include('layouts.footer')
            </div>
        </main>
        
        <!-- Core JS Files -->
        <script src="{{ asset('soft-ui-dashboard-laravel-master/public/assets/js/core/popper.min.js') }}"></script>
        <script src="{{ asset('soft-ui-dashboard-laravel-master/public/assets/js/core/bootstrap.min.js') }}"></script>
        <script src="{{ asset('soft-ui-dashboard-laravel-master/public/assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('soft-ui-dashboard-laravel-master/public/assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
        
        <!-- Sweet Alert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        </script>
        @endif
        
        @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                timer: 3000,
                showConfirmButton: false
            });
        </script>
        @endif
        
        <script>
            var win = navigator.platform.indexOf('Win') > -1;
            if (win && document.querySelector('#sidenav-scrollbar')) {
                var options = {
                    damping: '0.5'
                }
                Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
            }
        </script>
        
        <!-- Custom JS -->
        <script src="{{ asset('soft-ui-dashboard-laravel-master/public/assets/js/soft-ui-dashboard.min.js') }}"></script>
        @stack('js')
    </body>
</html>
