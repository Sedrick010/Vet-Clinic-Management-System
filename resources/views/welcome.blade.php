<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>VetClinic - Complete Veterinary Clinic Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                                    <i class="fas fa-paw me-2"></i> VetClinic
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>

                                        @if (Route::has('clinics.create'))
                                            <a href="{{ route('clinics.create') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Register Clinic</a>
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
                <div class="container mx-auto px-4 py-12">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-center mb-12">
                            <h1 class="text-4xl md:text-5xl font-bold text-primary mb-4">VetClinic Management System</h1>
                            <h2 class="text-xl md:text-2xl mb-4">The complete solution for veterinary clinics and independent vets</h2>
                            <p class="text-lg text-gray-600 mb-5 max-w-3xl mx-auto">Manage your entire veterinary practice with our powerful, easy-to-use platform.</p>
                            
                            <!-- Quick Access Box -->
                            <div class="mx-auto max-w-lg mt-8 mb-8 bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-4 bg-primary text-white">
                                    <h3 class="text-xl font-bold">Already have an account?</h3>
                                </div>
                                <div class="p-6">
                                    <div class="mb-4">
                                        <p class="mb-2">Log in to manage your veterinary clinic:</p>
                                        <div class="flex justify-center gap-4">
                                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark transition-colors">
                                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                                            </a>
                                            <a href="{{ route('clinics.create') }}" class="inline-flex items-center justify-center border border-primary text-primary px-6 py-2 rounded-md hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-clinic-medical me-2"></i> Register New Clinic
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <p class="text-sm text-gray-600 mb-2">Access your clinic directly via subdomain:</p>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="p-6 text-center">
                                <i class="fas fa-hospital text-4xl text-primary mb-4"></i>
                                <h3 class="text-xl font-bold mb-2">For Veterinary Clinics</h3>
                                <p class="text-gray-600 mb-4">Perfect for clinics of all sizes with multiple staff members and locations.</p>
                                <ul class="text-left mb-6">
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Multi-user access control</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Comprehensive patient records</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Inventory management</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Reporting and analytics</li>
                                </ul>
                            </div>
                            <div class="px-6 pb-6 text-center">
                                <a href="{{ route('clinics.create') }}" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark transition-colors">Register Your Clinic</a>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="p-6 text-center">
                                <i class="fas fa-user-md text-4xl text-primary mb-4"></i>
                                <h3 class="text-xl font-bold mb-2">For Independent Vets</h3>
                                <p class="text-gray-600 mb-4">Streamlined system for individual practitioners and mobile veterinarians.</p>
                                <ul class="text-left mb-6">
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Simple appointment scheduling</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Client and patient management</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Digital medical records</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Billing and invoicing</li>
                                </ul>
                            </div>
                            <div class="px-6 pb-6 text-center">
                                <a href="{{ route('clinics.create') }}" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark transition-colors">Get Started</a>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="p-6 text-center">
                                <i class="fas fa-paw text-4xl text-primary mb-4"></i>
                                <h3 class="text-xl font-bold mb-2">Key Features</h3>
                                <p class="text-gray-600 mb-4">Everything you need to run your practice efficiently.</p>
                                <ul class="text-left mb-6">
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Appointment management</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Patient & owner records</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Treatment plans</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Inventory tracking</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Billing and payment processing</li>
                                    <li class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Reporting and analytics</li>
                                </ul>
                            </div>
                            <div class="px-6 pb-6 text-center">
                                <a href="#features" class="border border-primary text-primary px-6 py-2 rounded-md hover:bg-gray-50 transition-colors">Learn More</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-16 mb-12">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold">Why Choose VetClinic?</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="flex items-start">
                                <div class="mr-4 bg-primary rounded-full p-3 text-white">
                                    <i class="fas fa-cloud"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Cloud-Based Solution</h4>
                                    <p class="text-gray-600">Access your clinic's information from anywhere, on any device, at any time. No software to install.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 bg-primary rounded-full p-3 text-white">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Secure & Private</h4>
                                    <p class="text-gray-600">Your data is protected with enterprise-grade security and encryption. HIPAA compliant.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 bg-primary rounded-full p-3 text-white">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Cost Effective</h4>
                                    <p class="text-gray-600">Affordable subscription plans for practices of all sizes. No expensive hardware required.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 bg-primary rounded-full p-3 text-white">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Dedicated Support</h4>
                                    <p class="text-gray-600">Our team is always available to help you get the most out of the system.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-16 mb-12 text-center">
                        <h2 class="text-3xl font-bold mb-6">Ready to transform your veterinary practice?</h2>
                        <a href="{{ route('clinics.create') }}" class="bg-primary text-white px-8 py-3 rounded-md text-lg hover:bg-primary-dark transition-colors">Register Your Clinic Today</a>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white py-8">
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

        <script>
            function goToClinicFromWelcome() {
                const subdomain = document.getElementById('welcome-subdomain').value.trim();
                if (subdomain) {
                    const protocol = window.location.protocol;
                    const domain = '{{ Str::after(config('app.url'), 'http://') }}';
                    window.location.href = `${protocol}//${subdomain}.${domain}`;
                } else {
                    alert('Please enter your clinic subdomain');
                }
            }
        </script>
    </body>
</html>
