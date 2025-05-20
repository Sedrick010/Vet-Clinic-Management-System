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
                                <h2 class="text-xl md:text-2xl mb-4">Veterinary Staff Portal</h2>
                                <p class="text-lg text-gray-600 mb-5 max-w-3xl mx-auto">
                                    Access our comprehensive clinic management system to provide the best care for our patients.
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
                                        <h3 class="text-xl font-bold">Staff Portal - {{ $clinic->name }}</h3>
                                    @else
                                        <h3 class="text-xl font-bold">Quick Access</h3>
                                    @endif
                                </div>
                                <div class="p-6">
                                    @if($clinic)
                                        <!-- Clinic-specific actions -->
                                        <div class="space-y-4">
                                            <div class="text-center mb-6">
                                                <a href="{{ route('login') }}" 
                                                   class="inline-block w-full bg-primary text-white px-6 py-4 rounded-md hover:bg-primary-dark transition-colors text-xl font-bold shadow-lg">
                                                    <i class="fas fa-clinic-medical me-2"></i> Staff Login
                                                </a>
                                                <p class="mt-2 text-sm text-gray-600">Secure access for clinic staff only</p>
                                            </div>
                                            <div class="border-t border-gray-200 pt-6">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Clinic Information</h3>
                                                <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-4">
                                                    <div class="flex items-center mb-3">
                                                        <i class="fas fa-map-marker-alt text-primary w-6"></i>
                                                        <span class="text-gray-700">{{ $clinic->address ?? 'Contact us for location details' }}</span>
                                                    </div>
                                                    <div class="flex items-center mb-3">
                                                        <i class="fas fa-phone text-primary w-6"></i>
                                                        <span class="text-gray-700">{{ $clinic->phone ?? 'Contact us by email' }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-envelope text-primary w-6"></i>
                                                        <span class="text-gray-700">{{ $clinic->email ?? 'info@example.com' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-4">
                                                    <p class="text-sm text-gray-600">
                                                        Need assistance? Contact our support team.
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <!-- Adding business hours section -->
                                            <div class="border-t border-gray-200 pt-6">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Business Hours</h3>
                                                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div class="text-gray-600">Monday - Friday:</div>
                                                        <div class="text-gray-900 font-medium">8:00 AM - 6:00 PM</div>
                                                        
                                                        <div class="text-gray-600">Saturday:</div>
                                                        <div class="text-gray-900 font-medium">9:00 AM - 4:00 PM</div>
                                                        
                                                        <div class="text-gray-600">Sunday:</div>
                                                        <div class="text-gray-900 font-medium">Closed</div>
                                                        
                                                        <div class="text-gray-600 mt-2">Emergency:</div>
                                                        <div class="text-red-600 font-medium mt-2">24/7 On-Call Service</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Adding services overview section -->
                                            <div class="border-t border-gray-200 pt-6">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Our Services</h3>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="bg-white p-3 rounded-lg shadow-sm flex items-center space-x-3">
                                                        <i class="fas fa-stethoscope text-primary text-xl"></i>
                                                        <span class="text-gray-800">Wellness Exams</span>
                                                    </div>
                                                    <div class="bg-white p-3 rounded-lg shadow-sm flex items-center space-x-3">
                                                        <i class="fas fa-medkit text-primary text-xl"></i>
                                                        <span class="text-gray-800">Vaccinations</span>
                                                    </div>
                                                    <div class="bg-white p-3 rounded-lg shadow-sm flex items-center space-x-3">
                                                        <i class="fas fa-tooth text-primary text-xl"></i>
                                                        <span class="text-gray-800">Dental Care</span>
                                                    </div>
                                                    <div class="bg-white p-3 rounded-lg shadow-sm flex items-center space-x-3">
                                                        <i class="fas fa-briefcase-medical text-primary text-xl"></i>
                                                        <span class="text-gray-800">Surgery</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Main site actions -->
                                        <div class="space-y-6">
                                            <div>
                                                <h4 class="text-lg font-semibold mb-3">For Pet Owners</h4>
                                                <div class="space-y-3">
                                                    <a href="{{ route('clinics.browse') }}" 
                                                       class="block w-full bg-primary text-white px-6 py-3 rounded-md hover:bg-primary-dark transition-colors text-center">
                                                        <i class="fas fa-search me-2"></i> Browse Veterinary Clinics
                                                    </a>
                                                    <p class="text-sm text-gray-600 text-center">Find and register with your preferred veterinary clinic</p>
                                                </div>
                                            </div>
                                            
                                            <div class="border-t pt-6">
                                                <h4 class="text-lg font-semibold mb-3">For Veterinary Clinics</h4>
                                                <div class="space-y-3">
                                                    <a href="{{ route('clinics.create') }}" 
                                                       class="block w-full border border-primary text-primary px-6 py-3 rounded-md hover:bg-gray-50 transition-colors text-center">
                                                        <i class="fas fa-clinic-medical me-2"></i> Register Your Clinic
                                                    </a>
                                                    <a href="{{ route('login') }}" 
                                                       class="block w-full bg-gray-800 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors text-center">
                                                        <i class="fas fa-sign-in-alt me-2"></i> Clinic Staff Login
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

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
