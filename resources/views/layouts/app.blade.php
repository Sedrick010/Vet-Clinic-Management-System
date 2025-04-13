<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <title>{{ config('app.name', 'Vet Clinic System') }}</title>
        
        <!-- Fonts and icons -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
        <!-- You may remove this if you don't use Fullcalendar -->
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <!-- CSS Files -->
        <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css') }}" rel="stylesheet" />
        
        <!-- Custom CSS -->
        @stack('css')
        
        <!-- Only prevent back button after logout -->
        @if(session('just_logged_out'))
        <script type="text/javascript">
            window.history.forward();
            function noBack() {
                window.history.forward();
            }
        </script>
        @endif
    </head>

    <body class="g-sidenav-show bg-gray-100" @if(session('just_logged_out')) onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="" @endif>
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
        <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
        
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
        <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
        
        <!-- Session validation check -->
        <script>
            // Check session validity regularly on authenticated pages
            document.addEventListener('DOMContentLoaded', function() {
                // Only on authenticated pages
                @if(Auth::check() || session()->has('tenant_user'))
                    // Check session every 5 seconds
                    setInterval(function() {
                        fetch('{{ route("session.check") }}', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.valid) {
                                // Session is invalid, redirect to login
                                window.location.href = '{{ route("login") }}';
                            }
                        })
                        .catch(error => {
                            console.error('Session check failed:', error);
                        });
                    }, 5000);
                @endif
            });
        </script>
        
        @stack('js')
    </body>
</html>
