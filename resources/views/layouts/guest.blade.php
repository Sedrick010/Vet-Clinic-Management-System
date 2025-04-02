<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'VetClinic'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        @stack('css')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <a href="{{ url('/') }}" class="text-xl font-bold text-primary">
                                    <i class="fas fa-paw me-2"></i> VetClinic
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="font-medium text-gray-600 hover:text-gray-900">
                                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="bg-primary text-white px-4 py-2 rounded-md font-medium hover:bg-primary-dark transition-colors">
                                            <i class="fas fa-sign-in-alt me-1"></i> Log in
                                        </a>

                                        @if (Route::has('register'))
                                            <a href="{{ route('clinics.create') }}" class="font-medium text-gray-600 hover:text-gray-900 ms-4">
                                                <i class="fas fa-clinic-medical me-1"></i> Register Clinic
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white py-8 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="mb-4 md:mb-0">
                                <h3 class="text-lg font-semibold">VetClinic Management System</h3>
                                <p class="text-gray-600 mt-2">The complete solution for veterinary clinics</p>
                            </div>
                            <div>
                                <p class="text-gray-600">&copy; {{ date('Y') }} VetClinic. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        
        @stack('js')
    </body>
</html>
