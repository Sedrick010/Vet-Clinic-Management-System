<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Select a Veterinary Clinic</h2>
                <p class="mt-2 text-gray-600">Choose a clinic to register or login as a customer</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($clinics as $clinic)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $clinic->name }}</h3>
                            
                            @if($clinic->address)
                                <p class="text-gray-600 mb-4">
                                    <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $clinic->address }}
                                </p>
                            @endif

                            <div class="mt-4">
                                <a href="{{ route('clinic.select', ['subdomain' => $clinic->subdomain]) }}" 
                                   class="block w-full text-center bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors">
                                    Select This Clinic
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($clinics->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-600">No clinics are currently available. Please check back later.</p>
                </div>
            @endif
        </div>
    </div>
</x-guest-layout> 