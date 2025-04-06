<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $clinic ? $clinic->name : 'VetClinic' }} - Login</title>

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
                                    <i class="fas fa-paw me-2"></i> {{ $clinic ? $clinic->name : 'VetClinic' }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            @if (!$is_subdomain && Route::has('clinics.create'))
                                <a href="{{ route('clinics.create') }}" class="text-sm text-gray-700 hover:text-gray-900">Register Clinic</a>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-primary text-white px-6 py-4">
                            @if($clinic)
                                <h2 class="text-2xl font-bold">Login to {{ $clinic->name }}</h2>
                                <p class="text-sm opacity-80 mt-1">Access your clinic dashboard</p>
                            @else
                                <h2 class="text-2xl font-bold">Login to VetClinic</h2>
                                <p class="text-sm opacity-80 mt-1">Access your veterinary clinic dashboard</p>
                            @endif
                        </div>
                        
                        <div class="p-6">
                            <!-- Session Status -->
                            @if (session('status'))
                                <div class="mb-4 px-4 py-2 bg-green-100 border-l-4 border-green-500 text-green-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="mb-4 px-4 py-2 bg-red-100 border-l-4 border-red-500 text-red-700">
                                    <p class="font-medium">Oops! There was a problem with your login.</p>
                                    <ul class="mt-2 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Auto-login troubleshooting -->
                            <div class="mb-6 text-right">
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('auto-logout-form').submit();" class="text-xs text-gray-500 hover:text-gray-700">
                                    Having trouble logging in? Click here to clear session
                                </a>
                                
                                <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>

                            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                                @csrf

                                <!-- Email Address -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input id="email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="email" value="{{ old('email') }}" required autofocus>
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <input id="password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="password" required>
                                </div>

                                <!-- Remember Me -->
                                <div class="flex items-center">
                                    <input class="rounded border-gray-300 text-primary focus:ring-primary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="ml-2 block text-sm text-gray-700" for="remember">
                                        Remember Me
                                    </label>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary-dark">
                                            Forgot Your Password?
                                        </a>
                                    @endif

                                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 inline-flex items-center">
                                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                                    </button>
                                </div>
                            </form>

                            <!-- Clinic Subdomain Login -->
                            @if(!$is_subdomain)
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Access Your Clinic Directly</h3>
                                <p class="text-gray-600 mb-3">Use your clinic's unique subdomain for direct access:</p>
                                
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <div class="flex items-stretch">
                                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md">
                                                https://
                                            </span>
                                            <input type="text" id="subdomain" class="flex-1 rounded-none bg-gray-50 border border-gray-300 text-gray-900 focus:ring-primary focus:border-primary block p-2.5" placeholder="your-clinic">
                                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                                .{{ Str::after(config('app.url'), 'http://') }}
                                            </span>
                                        </div>
                                    </div>
                                    <button onclick="goToSubdomain()" class="ml-2 px-4 py-2.5 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition-colors">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                                
                                <p class="mt-3 text-sm text-gray-600">
                                    Don't have a clinic account yet? 
                                    <a href="{{ route('clinics.create') }}" class="text-primary hover:text-primary-dark font-medium">
                                        Register your clinic here
                                    </a>
                                </p>
                            </div>
                            @endif
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
                                <p class="text-gray-600 mt-2">{{ $clinic ? 'Welcome to our veterinary clinic' : 'The complete solution for veterinary clinics' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">&copy; {{ date('Y') }} {{ $clinic ? $clinic->name : 'VetClinic' }}. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <script>
            function goToSubdomain() {
                const subdomain = document.getElementById('subdomain').value.trim();
                if (subdomain) {
                    const protocol = window.location.protocol;
                    const domain = "{{ Str::after(config('app.url'), 'http://') }}";
                    window.location.href = `${protocol}//${subdomain}.${domain}/login`;
                } else {
                    alert('Please enter your clinic subdomain');
                }
            }
        </script>

        @if(session('clear_history'))
            <script>
                if (window.history && window.history.pushState) {
                    window.history.pushState('', '', window.location.href);
                    window.onpopstate = function () {
                        window.history.pushState('', '', window.location.href);
                    };
                }
            </script>
        @endif
    </body>
</html>
