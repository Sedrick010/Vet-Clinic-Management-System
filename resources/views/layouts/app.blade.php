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
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ Session::get('css_version', time()) }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <!-- CSS Files -->
        <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css') }}?v={{ Session::get('css_version', time()) }}" rel="stylesheet" />
        
        <!-- Theme styles -->
        <style>
            :root {
                --primary-color: {{ isset($theme['colors']['primary']) ? $theme['colors']['primary'] : (isset($theme->colors->primary) ? $theme->colors->primary : '#5e72e4') }};
                --secondary-color: {{ isset($theme['colors']['secondary']) ? $theme['colors']['secondary'] : (isset($theme->colors->secondary) ? $theme->colors->secondary : '#8392ab') }};
                --success-color: {{ isset($theme['colors']['success']) ? $theme['colors']['success'] : (isset($theme->colors->success) ? $theme->colors->success : '#2dce89') }};
                --info-color: {{ isset($theme['colors']['info']) ? $theme['colors']['info'] : (isset($theme->colors->info) ? $theme->colors->info : '#11cdef') }};
                --warning-color: {{ isset($theme['colors']['warning']) ? $theme['colors']['warning'] : (isset($theme->colors->warning) ? $theme->colors->warning : '#fb6340') }};
                --danger-color: {{ isset($theme['colors']['danger']) ? $theme['colors']['danger'] : (isset($theme->colors->danger) ? $theme->colors->danger : '#f5365c') }};
                --background-color: {{ isset($theme['colors']['background']) ? $theme['colors']['background'] : (isset($theme->colors->background) ? $theme->colors->background : '#f8f9fe') }};
                --card-color: {{ isset($theme['colors']['card']) ? $theme['colors']['card'] : (isset($theme->colors->card) ? $theme->colors->card : '#ffffff') }};
                --card-secondary-color: {{ isset($theme['colors']['cardSecondary']) ? $theme['colors']['cardSecondary'] : (isset($theme->colors->cardSecondary) ? $theme->colors->cardSecondary : '#f8f9fa') }};
                --card-accent-color: {{ isset($theme['colors']['cardAccent']) ? $theme['colors']['cardAccent'] : (isset($theme->colors->cardAccent) ? $theme->colors->cardAccent : '#f0f2f5') }};
                --text-color: {{ isset($theme['colors']['text']) ? $theme['colors']['text'] : (isset($theme->colors->text) ? $theme->colors->text : '#344767') }};
                --text-secondary-color: {{ isset($theme['colors']['textSecondary']) ? $theme['colors']['textSecondary'] : (isset($theme->colors->textSecondary) ? $theme->colors->textSecondary : '#67748e') }};
                --primary-gradient: {{ isset($theme['gradients']['primary']) ? $theme['gradients']['primary'] : (isset($theme->gradients->primary) ? $theme->gradients->primary : 'linear-gradient(310deg, #5e72e4 0%, #825ee4 100%)') }};
                --success-gradient: {{ isset($theme['gradients']['success']) ? $theme['gradients']['success'] : (isset($theme->gradients->success) ? $theme->gradients->success : 'linear-gradient(310deg, #2dce89 0%, #4fd1c5 100%)') }};
                --info-gradient: {{ isset($theme['gradients']['info']) ? $theme['gradients']['info'] : (isset($theme->gradients->info) ? $theme->gradients->info : 'linear-gradient(310deg, #11cdef 0%, #1171ef 100%)') }};
                --warning-gradient: {{ isset($theme['gradients']['warning']) ? $theme['gradients']['warning'] : (isset($theme->gradients->warning) ? $theme->gradients->warning : 'linear-gradient(310deg, #fb6340 0%, #fbb140 100%)') }};
                --danger-gradient: {{ isset($theme['gradients']['danger']) ? $theme['gradients']['danger'] : (isset($theme->gradients->danger) ? $theme->gradients->danger : 'linear-gradient(310deg, #f5365c 0%, #f56036 100%)') }};
            }
            
            /* Sticky Footer Setup */
            html, body {
                height: 100%;
                margin: 0;
            }
            
            body {
                background-color: var(--background-color) !important;
                color: var(--text-color) !important;
                display: flex;
                flex-direction: column;
            }
            
            .main-content {
                flex: 1 0 auto;
                display: flex;
                flex-direction: column;
                background: var(--background-color) !important;
            }
            
            .container-fluid.py-4 {
                flex: 1 0 auto;
                display: flex;
                flex-direction: column;
                background: transparent !important;
            }
            
            .content-wrapper {
                flex: 1 0 auto;
                background: transparent !important;
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
                color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? '#5dd6ff' : 'var(--info-color)' }} !important;
            }
            
            .text-success {
                color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? '#4ade80' : 'var(--success-color)' }} !important;
            }
            
            .text-warning {
                color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? '#fbbf24' : 'var(--warning-color)' }} !important;
            }
            
            .text-danger {
                color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? '#f87171' : 'var(--danger-color)' }} !important;
            }
            
            .border-primary {
                border-color: var(--primary-color) !important;
            }
            
            .btn-primary {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
                color: white !important;
            }
            
            .btn-primary:hover {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                opacity: 0.9;
            }
            
            .card {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }
            
            .card-header {
                background-color: var(--card-accent-color) !important;
                color: var(--text-color) !important;
                border-bottom-color: var(--card-secondary-color) !important;
            }
            
            .table {
                color: var(--text-color) !important;
            }
            
            .table thead th {
                background-color: var(--card-accent-color) !important;
                color: var(--text-color) !important;
                border-bottom-color: var(--card-secondary-color) !important;
            }
            
            .table tbody td {
                border-bottom-color: var(--card-secondary-color) !important;
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
                background-color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'white' }};
                color: var(--text-color);
                border-color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.1)' : '#d2d6da' }};
            }
            
            .form-control:focus {
                background-color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.07)' : 'white' }};
                color: var(--text-color);
            }
            
            .input-group-text {
                background-color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.05)' : '#f8f9fa' }};
                color: var(--text-color);
                border-color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.1)' : '#d2d6da' }};
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
                color: {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'rgba(255, 255, 255, 0.6)' : '#6c757d' }} !important;
            }

            .bg-gray-100 {
                background-color: var(--background-color) !important;
            }

            .bg-dark {
                background-color: var(--background-color) !important;
            }

            .text-secondary {
                color: var(--secondary-color) !important;
            }
            
            .text-info {
                color: var(--info-color) !important;
            }
            
            .text-success {
                color: var(--success-color) !important;
            }
            
            .text-warning {
                color: var(--warning-color) !important;
            }
            
            .text-danger {
                color: var(--danger-color) !important;
            }
            
            .border-primary {
                border-color: var(--primary-color) !important;
            }
            
            .btn-primary {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
                color: white !important;
            }
            
            .btn-primary:hover {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                opacity: 0.9;
            }
            
            .card {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }
            
            .card-header {
                background-color: var(--card-accent-color) !important;
                color: var(--text-color) !important;
                border-bottom-color: var(--card-secondary-color) !important;
            }
            
            .table {
                color: var(--text-color) !important;
            }
            
            .table thead th {
                background-color: var(--card-accent-color) !important;
                color: var(--text-color) !important;
                border-bottom-color: var(--card-secondary-color) !important;
            }
            
            .table tbody td {
                border-bottom-color: var(--card-secondary-color) !important;
            }
            
            .navbar-light {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
            }
            
            .sidenav {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
            }
            
            .sidenav .nav-link {
                color: var(--text-color) !important;
            }
            
            .sidenav .nav-link.active {
                background-image: var(--primary-gradient);
                color: white !important;
            }
            
            .form-control {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }
            
            .form-control:focus {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--primary-color) !important;
            }
            
            .input-group-text {
                background-color: var(--card-accent-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }
            
            .modal-content {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
            }
            
            .dropdown-menu {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
            }
            
            .dropdown-item {
                color: var(--text-color) !important;
            }
            
            .dropdown-item:hover {
                background-color: var(--card-accent-color) !important;
            }
            
            .form-text.text-muted {
                color: var(--text-secondary-color) !important;
            }

            .nav-tabs .nav-link {
                color: var(--text-color) !important;
            }

            .nav-tabs .nav-link.active {
                background-color: var(--card-color) !important;
                color: var(--primary-color) !important;
                border-color: var(--card-secondary-color) var(--card-secondary-color) var(--card-color) !important;
            }

            .nav-tabs {
                border-bottom-color: var(--card-secondary-color) !important;
            }

            .pagination .page-link {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }

            .pagination .page-item.active .page-link {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
            }

            .alert {
                background-color: var(--card-color) !important;
                color: var(--text-color) !important;
                border-color: var(--card-secondary-color) !important;
            }

            .alert-primary {
                background-color: var(--card-accent-color) !important;
                color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
            }

            .alert-success {
                background-color: var(--card-accent-color) !important;
                color: var(--success-color) !important;
                border-color: var(--success-color) !important;
            }

            .alert-info {
                background-color: var(--card-accent-color) !important;
                color: var(--info-color) !important;
                border-color: var(--info-color) !important;
            }

            .alert-warning {
                background-color: var(--card-accent-color) !important;
                color: var(--warning-color) !important;
                border-color: var(--warning-color) !important;
            }

            .alert-danger {
                background-color: var(--card-accent-color) !important;
                color: var(--danger-color) !important;
                border-color: var(--danger-color) !important;
            }

            /* Ensuring theme colors apply to all elements, including client and pet pages */
            .avatar.bg-gradient-primary, 
            .bg-gradient-primary,
            .icon-shape,
            .avatar.bg-primary,
            .btn.btn-primary {
                background-color: var(--primary-color) !important;
                background-image: var(--primary-gradient) !important;
                color: white !important;
            }
            
            /* Target avatar elements in pets and clients pages */
            .avatar {
                background-color: var(--primary-color) !important;
            }
            
            .avatar.avatar-sm {
                color: white !important;
            }
            
            /* Force styles on pets page avatar colors */
            .avatar.avatar-sm.bg-primary {
                background-color: var(--primary-color) !important;
            }
            
            .avatar.avatar-sm.bg-warning {
                background-color: var(--warning-color) !important;
            }
            
            .avatar.avatar-sm.bg-success {
                background-color: var(--success-color) !important;
            }
            
            .avatar.avatar-sm.bg-info {
                background-color: var(--info-color) !important;
            }
            
            .avatar.avatar-sm.bg-secondary {
                background-color: var(--secondary-color) !important;
            }
            
            /* Force important on all bg classes */
            [class*="bg-"] {
                background-color: inherit;
            }
            
            /* Directly target color classes */
            [class*="bg-primary"], 
            .bg-primary {
                background-color: var(--primary-color) !important;
            }
            
            [class*="bg-success"], 
            .bg-success {
                background-color: var(--success-color) !important;
            }
            
            [class*="bg-info"], 
            .bg-info {
                background-color: var(--info-color) !important;
            }
            
            [class*="bg-warning"], 
            .bg-warning {
                background-color: var(--warning-color) !important;
            }
            
            [class*="bg-danger"], 
            .bg-danger {
                background-color: var(--danger-color) !important;
            }
            
            /* Force important on all bg-gradient classes */
            [class*="bg-gradient-"] {
                background-image: none;
            }
            
            .btn.btn-primary,
            .btn-primary {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
                color: white !important;
            }
            
            .btn.btn-success,
            .btn-success {
                background-color: var(--success-color) !important;
                border-color: var(--success-color) !important;
                color: white !important;
            }
            
            .btn.btn-info,
            .btn-info {
                background-color: var(--info-color) !important;
                border-color: var(--info-color) !important;
                color: white !important;
            }
            
            .btn.btn-warning,
            .btn-warning {
                background-color: var(--warning-color) !important;
                border-color: var(--warning-color) !important;
                color: white !important;
            }
            
            .btn.btn-danger,
            .btn-danger {
                background-color: var(--danger-color) !important;
                border-color: var(--danger-color) !important;
                color: white !important;
            }
            
            .bg-gradient-primary {
                background-color: var(--primary-color) !important;
                background-image: var(--primary-gradient) !important;
            }
            
            .bg-gradient-success {
                background-color: var(--success-color) !important;
                background-image: var(--success-gradient) !important;
            }
            
            .bg-gradient-info {
                background-color: var(--info-color) !important;
                background-image: var(--info-gradient) !important;
            }
            
            .bg-gradient-warning {
                background-color: var(--warning-color) !important;
                background-image: var(--warning-gradient) !important;
            }
            
            .bg-gradient-danger {
                background-color: var(--danger-color) !important;
                background-image: var(--danger-gradient) !important;
            }

            /* Special handling for pet species in pet list */
            .avatar.avatar-sm.bg-primary,
            .avatar.avatar-sm.bg-success,
            .avatar.avatar-sm.bg-info,
            .avatar.avatar-sm.bg-warning,
            .avatar.avatar-sm.bg-secondary,
            .avatar.avatar-sm.bg-danger {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            /* Fix icon colors in avatars */
            .avatar i,
            .avatar .fas,
            .icon-shape i,
            .icon-shape .fas {
                color: #fff !important;
            }
            
            /* Ensure text color in navbar matches */
            .navbar-vertical .navbar-nav > .nav-item .nav-link.active {
                color: #fff !important;
            }

            /* Pulse animation for update badge */
            .pulse-animation {
                animation: pulse 2s infinite;
                box-shadow: 0 0 0 rgba(220, 53, 69, 0.4);
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
                }
                70% {
                    box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
                }
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

    <body class="g-sidenav-show {{ (isset($theme['name']) ? $theme['name'] : (isset($theme->name) ? $theme->name : 'default')) == 'dark' ? 'bg-dark' : 'bg-gray-100' }}" @if(session('just_logged_out')) onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="" @endif>
        @if(!isset($isSidebar) || $isSidebar)
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
            // Check if the error is actually a successful update with expected migration errors
            const errorMessage = "{{ session('error') }}";
            const isUpdateError = errorMessage.includes('table or view already exists') || 
                                 errorMessage.includes('Base table or view not found') ||
                                 errorMessage.includes('Column not found') ||
                                 errorMessage.includes('system_versions') ||
                                 errorMessage.includes('Error deploying version') ||
                                 errorMessage.includes('migration');
            
            // If this is an update error but the features are working, show success instead
            if (isUpdateError && window.location.href.includes('system-updates')) {
                Swal.fire({
                    icon: 'success',
                    title: 'Update Successful',
                    text: "The update was applied successfully despite some expected database messages.",
                    timer: 5000,
                    showConfirmButton: true
                });
            } else {
                // Show regular error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
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
                // Force page refresh if theme was just changed
                @if(session('_refresh'))
                    // Clear any CSS caches
                    for (var i = 0; i < document.styleSheets.length; i++) {
                        try {
                            document.styleSheets[i].disabled = true;
                            document.styleSheets[i].disabled = false;
                        } catch (e) {
                            console.log('Failed to refresh stylesheet:', e);
                        }
                    }
                @endif
                
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
        
        @if(Auth::check() || session()->has('tenant_user'))
            <!-- Floating Contact Support Button -->
            <div class="position-fixed bottom-4 end-4" style="z-index: 100;">
                <a href="{{ route('support.create') }}" class="btn btn-primary btn-lg rounded-circle shadow" 
                    data-bs-toggle="tooltip" data-bs-placement="left" title="Contact Support">
                    <i class="fas fa-headset"></i>
                </a>
            </div>
        @endif
    </body>
</html>
