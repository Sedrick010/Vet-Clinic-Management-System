@extends('layouts.guest')

@section('title', 'Login to VetClinic')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-primary px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Login to Your Clinic</h2>
        </div>
        <div class="p-6">
            @if (session('status'))
                <div class="mb-4 px-4 py-2 bg-green-100 border-l-4 border-green-500 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

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

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20" name="password" required autocomplete="current-password">
                </div>

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
        </div>
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="text-center">
                Don't have a clinic account yet? 
                <a href="{{ route('clinics.create') }}" class="text-primary hover:text-primary-dark font-medium">
                    Register your clinic here
                </a>
            </div>
        </div>
    </div>
    
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Clinic Subdomain Login</h3>
        <p class="text-gray-600 mb-4">If you want to access your clinic directly, you can use your clinic's unique subdomain:</p>
        
        <div class="flex w-full rounded-md shadow-sm">
            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                https://
            </span>
            <input type="text" id="subdomain" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border border-gray-300 focus:ring-primary focus:border-primary" placeholder="your-clinic-subdomain">
            <span class="inline-flex items-center px-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-r-md">
                .{{ Str::after(config('app.url'), 'http://') }}
            </span>
        </div>
        
        <div class="mt-4">
            <button onclick="goToSubdomain()" class="w-full border border-primary text-primary hover:bg-gray-50 focus:ring-4 focus:ring-primary-light font-medium rounded-md text-sm px-5 py-2.5 text-center inline-flex items-center justify-center">
                <i class="fas fa-external-link-alt mr-2"></i> Go to Clinic
            </button>
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Have access to multiple clinics? 
                <a href="{{ route('clinics.select') }}" class="text-primary hover:text-primary-dark">
                    Select from your clinics
                </a> 
                after logging in.
            </p>
        </div>
    </div>
</div>

@push('js')
<script>
    function goToSubdomain() {
        const subdomain = document.getElementById('subdomain').value.trim();
        if (subdomain) {
            const protocol = window.location.protocol;
            const domain = '{{ Str::after(config('app.url'), 'http://') }}';
            window.location.href = `${protocol}//${subdomain}.${domain}/login`;
        } else {
            alert('Please enter your clinic subdomain');
        }
    }
</script>
@endpush
@endsection
