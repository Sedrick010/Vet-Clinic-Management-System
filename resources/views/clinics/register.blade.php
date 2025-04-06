<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Register Your Veterinary Clinic - VetClinic</title>

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
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-primary text-white px-6 py-4">
                            <h2 class="text-2xl font-bold">Register Your Veterinary Clinic</h2>
                            <p class="text-sm opacity-80 mt-1">Join our network of veterinary professionals</p>
                        </div>
                        
                        <div class="p-6">
                            <form method="POST" action="{{ route('clinics.store') }}">
                                @csrf

                                <!-- Clinic Information Section -->
                                <div class="mb-8">
                                    <div class="text-center mb-6">
                                        <h3 class="text-xl font-semibold text-gray-800">Clinic Information</h3>
                                        <div class="mt-2 h-0.5 w-24 bg-primary mx-auto"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Clinic Name -->
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Clinic Name</label>
                                            <input id="name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="name" value="{{ old('name') }}" required autofocus>
                                            @error('name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Subdomain -->
                                        <div>
                                            <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">Preferred Subdomain</label>
                                            <div class="flex rounded-md shadow-sm">
                                                <input id="subdomain" type="text" class="flex-1 rounded-none rounded-l-md border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="subdomain" value="{{ old('subdomain') }}" required>
                                                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                    .{{ Str::after(config('app.url'), 'http://') }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">This will be your unique URL for accessing your clinic system.</p>
                                            @error('subdomain')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Clinic Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Clinic Email</label>
                                            <input id="email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Clinic Phone -->
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Clinic Phone</label>
                                            <input id="phone" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="phone" value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Clinic Address -->
                                    <div class="mt-6">
                                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Clinic Address</label>
                                        <input id="address" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="address" value="{{ old('address') }}" required>
                                        @error('address')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Owner Information Section -->
                                <div class="mt-12 mb-8">
                                    <div class="text-center mb-6">
                                        <h3 class="text-xl font-semibold text-gray-800">Clinic Owner Information</h3>
                                        <div class="mt-2 h-0.5 w-24 bg-primary mx-auto"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Owner Name -->
                                        <div>
                                            <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
                                            <input id="owner_name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="owner_name" value="{{ old('owner_name') }}" required>
                                            @error('owner_name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Owner Email -->
                                        <div>
                                            <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-1">Owner Email</label>
                                            <input id="owner_email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="owner_email" value="{{ old('owner_email') }}" required>
                                            <p class="mt-1 text-xs text-gray-500">This will be used for logging into your clinic system.</p>
                                            @error('owner_email')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                            <input id="password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="password" required>
                                            @error('password')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Confirm Password -->
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                            <input id="password_confirmation" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="password_confirmation" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-10 text-center">
                                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-md font-medium transition-colors duration-200 inline-flex items-center">
                                        <i class="fas fa-clinic-medical mr-2"></i> Register Clinic
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">What Happens Next?</h3>
                        <p class="text-gray-600 mb-4">After registering your clinic, you'll need to wait for admin approval before you can start using the system.</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li>Our administrative team will review your application</li>
                            <li>You'll receive an email notification once your clinic is approved</li>
                            <li>You can then log in and start managing your clinic</li>
                            <li>Set up your staff accounts and permissions</li>
                            <li>Configure your clinic's settings</li>
                            <li>Start managing your patients and appointments</li>
                        </ul>
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
    </body>
</html> 