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
        
        <!-- Theme styles -->
        <style>
            :root {
                --primary-color: {{ $theme['colors']['primary'] }};
                --secondary-color: {{ $theme['colors']['secondary'] }};
                --success-color: {{ $theme['colors']['success'] }};
                --info-color: {{ $theme['colors']['info'] }};
                --warning-color: {{ $theme['colors']['warning'] }};
                --danger-color: {{ $theme['colors']['danger'] }};
                --background-color: {{ $theme['colors']['background'] ?? '#f8f9fe' }};
                --card-color: {{ $theme['colors']['card'] ?? '#ffffff' }};
                --card-secondary-color: {{ $theme['colors']['cardSecondary'] ?? '#f8f9fa' }};
                --card-accent-color: {{ $theme['colors']['cardAccent'] ?? '#f0f2f5' }};
                --text-color: {{ $theme['colors']['text'] ?? '#344767' }};
                --text-secondary-color: {{ $theme['colors']['textSecondary'] ?? '#67748e' }};
                --primary-gradient: {{ $theme['gradients']['primary'] }};
                --success-gradient: {{ $theme['gradients']['success'] }};
                --info-gradient: {{ $theme['gradients']['info'] }};
                --warning-gradient: {{ $theme['gradients']['warning'] }};
                --danger-gradient: {{ $theme['gradients']['danger'] }};
            }
            
            /* Sticky Footer Setup */
            html, body {
                height: 100%;
                margin: 0;
            }
            
            body {
                background-color: var(--background-color);
                color: var(--text-color);
                display: flex;
                flex-direction: column;
            }
            
            .main-content {
                flex: 1 0 auto;
                display: flex;
                flex-direction: column;
            }
            
            .container-fluid.py-4 {
                flex: 1 0 auto;
                display: flex;
                flex-direction: column;
            }
            
            .content-wrapper {
                flex: 1 0 auto;
            }
            
            .footer {
                flex-shrink: 0;
                background-color: var(--card-color);
                margin-top: auto;
                width: 100%;
            }
            
            .bg-primary {
                background-color: var(--primary-color) !important;
            }
            
            .bg-secondary {
                background-color: var(--secondary-color) !important;
            }
            
            .bg-success {
                background-color: var(--success-color) !important;
            }
            
            .bg-info {
                background-color: var(--info-color) !important;
            }
            
            .bg-warning {
                background-color: var(--warning-color) !important;
            }
            
            .bg-danger {
                background-color: var(--danger-color) !important;
            }
            
            .text-primary {
                color: var(--primary-color) !important;
            }
            
            .text-info {
                color: {{ $theme['name'] == 'dark' ? '#5dd6ff' : 'var(--info-color)' }} !important;
            }
            
            .text-success {
                color: {{ $theme['name'] == 'dark' ? '#4ade80' : 'var(--success-color)' }} !important;
            }
            
            .text-warning {
                color: {{ $theme['name'] == 'dark' ? '#fbbf24' : 'var(--warning-color)' }} !important;
            }
            
            .text-danger {
                color: {{ $theme['name'] == 'dark' ? '#f87171' : 'var(--danger-color)' }} !important;
            }
            
            .border-primary {
                border-color: var(--primary-color) !important;
            }
            
            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }
            
            .btn-primary:hover {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                opacity: 0.9;
            }
            
            .card {
                background-color: var(--card-color);
                color: var(--text-color);
            }
            
            .card-header {
                background-color: var(--card-accent-color);
            }
            
            .table {
                color: var(--text-color);
            }
            
            .navbar-light {
                background-color: var(--card-color);
            }
            
            .sidenav {
                background-color: var(--card-color);
            }
            
            .sidenav .nav-link {
                color: var(--text-color);
            }
            
            .sidenav .nav-link.active {
                background-image: var(--primary-gradient);
                color: white;
            }
            
            .form-control {
                background-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'white' }};
                color: var(--text-color);
                border-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.1)' : '#d2d6da' }};
            }
            
            .form-control:focus {
                background-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.07)' : 'white' }};
                color: var(--text-color);
            }
            
            .input-group-text {
                background-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.05)' : '#f8f9fa' }};
                color: var(--text-color);
                border-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.1)' : '#d2d6da' }};
            }
            
            .modal-content {
                background-color: var(--card-color);
                color: var(--text-color);
            }
            
            .dropdown-menu {
                background-color: var(--card-color);
            }
            
            .dropdown-item {
                color: var(--text-color);
            }
            
            .dropdown-item:hover {
                background-color: var(--card-accent-color);
            }
            
            .form-text.text-muted {
                color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.6)' : '#6c757d' }} !important;
            }
        </style>
        
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

    <body class="g-sidenav-show {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-gray-100' }}" @if(session('just_logged_out')) onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="" @endif>
        @if(isset($isSidebar) && $isSidebar)
            @include('layouts.sidebar')
        @endif
        
        <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg overflow-hidden">
            @include('layouts.navbar')
            
            <div class="container-fluid py-4">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                
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
                    
                    // Check subscription status every 30 seconds (only for tenant users)
                    @if(session()->has('tenant_user') && isset($clinic))
                    setInterval(function() {
                        fetch('/check-subscription-status', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== undefined) {
                                const statusBadge = document.getElementById('subscription-status-badge');
                                const premiumSection = document.getElementById('premium-features-section');
                                
                                if (statusBadge) {
                                    // Update badge
                                    statusBadge.className = data.status ? 
                                        'badge bg-gradient-success me-2' : 
                                        'badge bg-gradient-danger me-2';
                                    statusBadge.textContent = data.status ? 'ACTIVE' : 'INACTIVE';
                                }
                                
                                // If subscription status changed from inactive to active, reload the page
                                if (data.status === true && premiumSection === null) {
                                    window.location.reload();
                                }
                                
                                // If subscription status changed from active to inactive and premium section exists, reload
                                if (data.status === false && premiumSection !== null) {
                                    window.location.reload();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Subscription check failed:', error);
                        });
                    }, 30000);
                    @endif
                @endif
            });
        </script>
        
        @stack('js')
    </body>
</html>
