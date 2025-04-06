<x-app-layout>
    <div class="container py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold mb-6">Subdomain Demo</h1>
                    
                    <div class="mb-8 bg-blue-50 p-4 rounded-lg">
                        <h2 class="text-xl font-medium mb-3">Current Request Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Host:</strong> {{ $host }}</p>
                                <p><strong>Subdomain:</strong> {{ $current_subdomain ?? 'None (Main Domain)' }}</p>
                                <p><strong>Main Domain:</strong> {{ $main_domain }}</p>
                            </div>
                            <div>
                                @if($current_clinic)
                                    <p><strong>Current Clinic:</strong> {{ $current_clinic->name }}</p>
                                    <p><strong>Clinic ID:</strong> {{ $current_clinic->id }}</p>
                                    <p><strong>Approval Status:</strong> {{ $current_clinic->approval_status }}</p>
                                @else
                                    <p><strong>Current Clinic:</strong> None (Main Application)</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-medium mb-3">Available Clinic Subdomains</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b">Clinic Name</th>
                                        <th class="py-2 px-4 border-b">Subdomain</th>
                                        <th class="py-2 px-4 border-b">URLs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clinic_urls as $id => $clinic)
                                        <tr>
                                            <td class="py-2 px-4 border-b">{{ $clinic['name'] }}</td>
                                            <td class="py-2 px-4 border-b">{{ $clinic['subdomain'] }}</td>
                                            <td class="py-2 px-4 border-b">
                                                <div class="flex flex-col space-y-2">
                                                    <a href="{{ $clinic['home_url'] }}" class="text-blue-600 hover:underline" target="_blank">Home</a>
                                                    <a href="{{ $clinic['dashboard_url'] }}" class="text-blue-600 hover:underline" target="_blank">Dashboard</a>
                                                    <a href="{{ $clinic['demo_url'] }}" class="text-blue-600 hover:underline" target="_blank">This Demo</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-4 px-4 text-center">No approved clinics found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-medium mb-3">Subdomain Examples</h2>
                        <p class="mb-4">These examples show how to use the Subdomain service in your code:</p>
                        
                        <div class="bg-gray-100 p-4 rounded-lg mb-4">
                            <h3 class="font-medium mb-2">Get Current Subdomain</h3>
                            <pre class="bg-gray-800 text-white p-3 rounded"><code>$subdomain = \App\Facades\Subdomain::current();</code></pre>
                        </div>
                        
                        <div class="bg-gray-100 p-4 rounded-lg mb-4">
                            <h3 class="font-medium mb-2">Generate URL for a Clinic Subdomain</h3>
                            <pre class="bg-gray-800 text-white p-3 rounded"><code>// Using a Clinic model
$url = \App\Facades\Subdomain::url($clinic, '/path');

// Using a subdomain string
$url = \App\Facades\Subdomain::url('clinic-subdomain', '/path');</code></pre>
                        </div>
                        
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h3 class="font-medium mb-2">Generate Route URL for a Clinic Subdomain</h3>
                            <pre class="bg-gray-800 text-white p-3 rounded"><code>// Generate URL to a named route on a subdomain
$url = \App\Facades\Subdomain::route($clinic, 'dashboard');</code></pre>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h2 class="text-xl font-medium mb-3">Navigation</h2>
                        <div class="flex space-x-4">
                            <a href="{{ url('/') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Main Domain Home
                            </a>
                            <a href="{{ route('dashboard') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 