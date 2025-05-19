@extends('layouts.app')

@section('title', 'Update Successful')
@section('page_name', 'System Update Successful')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Display success message for direct URL access -->
            @if(!session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: "{{ ucfirst($updateType) }} to version {{ $version }} completed successfully!",
                        timer: 3000,
                        showConfirmButton: false
                    });
                });
            </script>
            @endif
            
            <div class="card mb-4">
                <div class="card-header pb-0 bg-gradient-success shadow-success border-radius-lg">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h5 class="mb-0 text-white">System Update Successful</h5>
                            <p class="text-sm mb-0 opacity-8">Your system has been successfully updated</p>
                            <div class="mt-2 bg-white px-3 py-1 rounded d-inline-block">
                                <span class="badge bg-success">VERSION IDENTIFIER: SUCCESS-UI-v4.1.2</span>
                            </div>
                        </div>
                        <a href="{{ route('version.manage') }}" class="btn btn-sm btn-outline-white">
                            <i class="fas fa-arrow-left me-1"></i> Back to Version Management
                        </a>
                    </div>
                </div>
                <div class="card-body px-4 pt-4 pb-2">

                    <div class="alert alert-success mb-4" role="alert">
                        <div class="d-flex">
                            <div class="pe-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Update completed successfully!</h5>
                                <p class="mb-0">Your system has been updated to version <strong>{{ $version }}</strong>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-4" role="alert">
                        <div class="d-flex">
                            <div class="pe-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Note About Database Messages</h5>
                                <p class="mb-0">If you saw any database-related messages during the update process (such as "table already exists" or "table not found"), these are expected and do not affect the functionality of your system. These messages occur because the system is ensuring all required database tables are properly set up.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header pb-0 bg-light">
                                    <h6 class="mb-0">Update Details</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Previous Version:</strong> {{ $previousVersion }}</p>
                                    <p><strong>New Version:</strong> {{ $version }}</p>
                                    <p><strong>Update Type:</strong> {{ $updateType }}</p>
                                    <p><strong>Update Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header pb-0 bg-light">
                                    <h6 class="mb-0">Database Migration Status</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Main Database:</strong> <span class="text-success">Migrated successfully</span></p>
                                    <p><strong>Tenant Databases:</strong> <span class="{{ $tenantMigrationSuccess ? 'text-success' : 'text-warning' }}">
                                        {{ $tenantMigrationSuccess ? 'All migrated successfully' : 'Some migrations completed with warnings' }}
                                    </span></p>
                                    @if(!$tenantMigrationSuccess)
                                        <p class="text-sm text-muted">Some tenant migrations may have warnings. Check the log for details.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header pb-0 bg-light">
                                    <h6 class="mb-0">What's Next?</h6>
                                </div>
                                <div class="card-body">
                                    <p>Your system is now running on version {{ $version }}. Here are some steps you might want to take:</p>
                                    <ul>
                                        <li>Check that all features are working as expected</li>
                                        <li>Review the system logs if you encounter any issues</li>
                                        <li>Update any documentation for your staff about new features</li>
                                        <li>If you experience any problems, you can restore from a backup in the Version Management section</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-1"></i> Go to Dashboard
                        </a>
                        <a href="{{ route('version.manage') }}" class="btn btn-info">
                            <i class="fas fa-code-branch me-1"></i> Version Management
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 