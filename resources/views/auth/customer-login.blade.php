<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Clinic Logo -->
    <div class="mb-6 text-center">
        <div class="flex justify-center mb-4">
            <img src="{{ $clinic->getLogoUrl() }}" alt="{{ $clinic->name }} Logo" class="max-w-[200px] max-h-[150px] object-contain rounded-lg shadow-md p-3 bg-white hover:shadow-lg transition-all duration-300" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ $clinic->name }}</h2>
        <p class="text-gray-600">Customer Portal Update</p>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    The customer portal is currently unavailable. Please contact our clinic directly for any assistance.
                </p>
            </div>
        </div>
    </div>

    <div class="text-center space-y-6">
        <p class="text-gray-600">Are you a clinic staff member?</p>
        <a href="{{ route('login') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-md hover:bg-primary-dark transition-colors">
            <i class="fas fa-clinic-medical mr-2"></i> Staff Login
        </a>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <a href="{{ route('welcome') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Return to home page
            </a>
        </div>
    </div>
</x-guest-layout> 