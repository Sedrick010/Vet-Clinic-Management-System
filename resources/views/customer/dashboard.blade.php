<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Welcome, {{ $customer->name }}!</h3>
                        <p class="text-gray-600">You are registered at {{ $clinic->name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Profile Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h4>
                            <div class="space-y-2">
                                <p><span class="font-medium">Email:</span> {{ $customer->email }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $customer->phone ?? 'Not provided' }}</p>
                                <p><span class="font-medium">Address:</span> {{ $customer->address ?? 'Not provided' }}</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h4>
                            <div class="space-y-4">
                                <a href="#" class="block w-full text-center bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                                    Book Appointment
                                </a>
                                <a href="#" class="block w-full text-center bg-white text-indigo-600 border border-indigo-600 py-2 px-4 rounded-md hover:bg-indigo-50">
                                    View Medical History
                                </a>
                                <a href="#" class="block w-full text-center bg-white text-indigo-600 border border-indigo-600 py-2 px-4 rounded-md hover:bg-indigo-50">
                                    Update Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 