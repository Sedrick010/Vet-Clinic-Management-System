<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $clinic ? $clinic->name : 'VetClinic' }} - Welcome</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Navigation -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <a href="{{ route('welcome') }}" class="text-xl font-bold text-primary">
                                    <i class="fas fa-paw me-2"></i> {{ $clinic ? $clinic->name : 'VetClinic' }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Dashboard</a>
                                    @else
                                        @if(!$is_subdomain)
                                            <a href="{{ route('clinics.create') }}" class="text-sm text-gray-700 hover:text-gray-900">Register Clinic</a>
                                        @endif
                                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900 ml-4">Login</a>
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Flash Messages -->
            @if(session('error') && !str_contains(session('error'), 'SECURITY ALERT'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <!-- Page Content -->
            <main>
                <div class="container mx-auto px-4 py-12">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-center mb-12">
                            @if($clinic)
                                <h1 class="text-4xl md:text-5xl font-bold text-primary mb-4">Welcome to {{ $clinic->name }}</h1>
                                <h2 class="text-xl md:text-2xl mb-4">Your Trusted Veterinary Care Partner</h2>
                                <p class="text-lg text-gray-600 mb-5 max-w-3xl mx-auto">
                                    Access our comprehensive veterinary services and manage your pet's healthcare with our state-of-the-art clinic management system.
                                </p>
                            @else
                                <h1 class="text-4xl md:text-5xl font-bold text-primary mb-4">VetClinic Management System</h1>
                                <h2 class="text-xl md:text-2xl mb-4">The complete solution for veterinary clinics and independent vets</h2>
                                <p class="text-lg text-gray-600 mb-5 max-w-3xl mx-auto">
                                    Manage your entire veterinary practice with our powerful, easy-to-use platform.
                                </p>
                            @endif
                            
                            <!-- Quick Access Box -->
                            <div class="mx-auto max-w-lg mt-8 mb-8 bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-4 bg-primary text-white">
                                    @if($clinic)
                                        <h3 class="text-xl font-bold">Access {{ $clinic->name }}</h3>
                                    @else
                                        <h3 class="text-xl font-bold">Already have an account?</h3>
                                    @endif
                                </div>
                                <div class="p-6">
                                    <div class="mb-4">
                                        @if($clinic)
                                            <p class="mb-2">Log in to access your veterinary services:</p>
                                            <div class="flex justify-center">
                                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark transition-colors">
                                                    <i class="fas fa-sign-in-alt me-2"></i> Log In to {{ $clinic->name }}
                                                </a>
                                            </div>
                                        @else
                                            <p class="mb-2">Log in to manage your veterinary clinic:</p>
                                            <div class="flex justify-center gap-4">
                                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark transition-colors">
                                                    <i class="fas fa-sign-in-alt me-2"></i> Log In
                                                </a>
                                                <a href="{{ route('clinics.create') }}" class="inline-flex items-center justify-center border border-primary text-primary px-6 py-2 rounded-md hover:bg-gray-50 transition-colors">
                                                    <i class="fas fa-clinic-medical me-2"></i> Register New Clinic
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$is_subdomain)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <p class="text-sm text-gray-600 mb-3">Access your clinic directly:</p>
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div class="flex items-stretch">
                                                    <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md">
                                                        https://
                                                    </span>
                                                    <input type="text" id="welcome-subdomain" class="flex-1 rounded-none bg-gray-50 border border-gray-300 text-gray-900 focus:ring-primary focus:border-primary block p-2.5" placeholder="your-clinic">
                                                    <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                                        .{{ Str::after(config('app.url'), 'http://') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <button onclick="goToClinicFromWelcome()" class="ml-2 px-4 py-2.5 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition-colors">
                                                <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="mb-4 md:mb-0">
                                <h3 class="text-lg font-semibold">{{ $clinic ? $clinic->name : 'VetClinic Management System' }}</h3>
                                <p class="text-gray-600 mt-2">{{ $clinic ? 'Your trusted veterinary care partner' : 'The complete solution for veterinary clinics' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">&copy; {{ date('Y') }} {{ $clinic ? $clinic->name : 'VetClinic' }}. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- SweetAlert for Flash Messages -->
        @if(session('error') && !str_contains(session('error'), 'SECURITY ALERT'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ session('error') }}",
                    timer: 5000,
                    showConfirmButton: true
                });
            });
        </script>
        @endif

        @if(session('error') && str_contains(session('error'), 'SECURITY ALERT'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Security Alert',
                    html: "The subdomain is not registered in our system.<br>You have been redirected to the main site for your safety.",
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>
        @endif

        <!-- Subdomain navigation script -->
        <script>
            function goToClinicFromWelcome() {
                const subdomain = document.getElementById('welcome-subdomain').value.trim();
                if (subdomain) {
                    window.location.href = `${window.location.protocol}//${subdomain}.{{ Str::after(config('app.url'), 'http://') }}`;
                } else {
                    alert('Please enter your clinic subdomain');
                }
            }
        </script>
    </body>
</html>
