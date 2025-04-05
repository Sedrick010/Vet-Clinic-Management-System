<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold mb-6">Database Connection Check</h1>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-medium mb-3">Main Database</h2>
                        <div class="bg-gray-100 p-4 rounded">
                            <p><strong>Database Name:</strong> <span id="main-db-name">{{ DB::connection()->getDatabaseName() }}</span></p>
                            <p><strong>Connection Status:</strong> <span class="text-green-600 font-semibold">Connected</span></p>
                            <p><strong>MySQL Version:</strong> <span id="mysql-version">{{ DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown' }}</span></p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-medium mb-3">Registered Clinics</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b">ID</th>
                                        <th class="py-2 px-4 border-b">Name</th>
                                        <th class="py-2 px-4 border-b">Subdomain</th>
                                        <th class="py-2 px-4 border-b">Database</th>
                                        <th class="py-2 px-4 border-b">Status</th>
                                        <th class="py-2 px-4 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="clinics-table-body">
                                    @forelse(\App\Models\Clinic::all() as $clinic)
                                    <tr>
                                        <td class="py-2 px-4 border-b">{{ $clinic->id }}</td>
                                        <td class="py-2 px-4 border-b">{{ $clinic->name }}</td>
                                        <td class="py-2 px-4 border-b">{{ $clinic->subdomain }}</td>
                                        <td class="py-2 px-4 border-b">{{ $clinic->database_name }}</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="{{ $clinic->approval_status === 'approved' ? 'text-green-600' : ($clinic->approval_status === 'pending' ? 'text-yellow-600' : 'text-red-600') }} font-semibold">
                                                {{ ucfirst($clinic->approval_status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="{{ route('admin.clinics.index') }}" class="text-blue-600 hover:underline">Manage</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-4 px-4 text-center">No clinics found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h2 class="text-xl font-medium mb-3">System Information</h2>
                        <div class="bg-gray-100 p-4 rounded">
                            <p><strong>PHP Version:</strong> <span id="php-version">{{ phpversion() }}</span></p>
                            <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                            <p><strong>APP_URL:</strong> {{ config('app.url') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 