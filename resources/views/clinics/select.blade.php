@extends('layouts.guest')

@section('title', 'Select Your Clinic')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-primary px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Select Your Clinic</h2>
        </div>
        <div class="p-6">
            @if (session('error'))
                <div class="mb-4 px-4 py-2 bg-red-100 border-l-4 border-red-500 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-gray-700 text-lg mb-6">Welcome back, <span class="font-semibold">{{ $user->name }}</span>! Please select which clinic you want to access:</p>

            <div class="mb-6 space-y-3">
                @forelse ($clinics as $clinic)
                    <a href="{{ route('clinics.switch', $clinic->id) }}" class="block w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-800">{{ $clinic->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $clinic->subdomain }}.{{ Str::after(config('app.url'), 'http://') }}</p>
                        </div>
                        <span class="bg-primary text-white px-3 py-1 rounded-full text-sm font-medium flex items-center">
                            <i class="fas fa-arrow-right mr-1"></i> Select
                        </span>
                    </a>
                @empty
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <p class="text-gray-700">You don't have access to any clinics. Please contact your administrator.</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-2">
                <a href="{{ route('clinics.create') }}" class="w-full block text-center border border-primary text-primary hover:bg-gray-50 focus:ring-4 focus:ring-primary-light font-medium rounded-md px-5 py-2.5 transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i> Register a New Clinic
                </a>
            </div>
        </div>
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="text-center">
                Not the right account? 
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-800 font-medium">
                    <i class="fas fa-sign-out-alt mr-1"></i> Log out
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 