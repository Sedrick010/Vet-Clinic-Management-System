@extends('layouts.app')

@section('title', 'System Updates')
@section('page_name', 'System Updates')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Updates</h5>
                    <button id="check-updates-btn" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Check for Updates
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-1">System Information</h5>
                                <p class="mb-0">Current Version: <strong>{{ $currentVersion->version ?? 'Unknown' }}</strong> ({{ $currentVersion->name ?? 'Unknown' }})</p>
                                <p class="mb-0">Released: {{ $currentVersion && $currentVersion->released_at ? $currentVersion->released_at->format('F j, Y') : 'Unknown' }}</p>
                            </div>
                        </div>
                    </div>

                    <div id="updates-container">
                        @if(isset($updateCheck['success']) && $updateCheck['success'])
                            @if($updateCheck['has_updates'])
                                <div class="alert alert-success mb-4" id="updates-available">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-download fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-white">Updates Available!</h5>
                                            <p class="mb-0">There are {{ count($updateCheck['updates']) }} update(s) available for your system.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Name</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Released</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($updateCheck['updates'] as $update)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $update->version }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">{{ $update->name }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">{{ $update->available_from ? $update->available_from->format('M d, Y') : 'N/A' }}</p>
                                                </td>
                                                <td>
                                                    <span class="badge badge-sm bg-gradient-{{ $update->is_critical ? 'danger' : ($update->is_security ? 'warning' : 'info') }}">
                                                        {{ $update->is_critical ? 'Critical' : ($update->is_security ? 'Security' : 'Feature') }}
                                                    </span>
                                                    @if($update->is_mandatory)
                                                        <span class="badge badge-sm bg-gradient-primary ms-1">Required</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-sm font-weight-bold">
                                                        @php
                                                            $clinicUpdate = \App\Models\ClinicUpdate::where('clinic_id', $clinic->id)
                                                                ->where('system_update_id', $update->id)
                                                                ->first();
                                                            $status = 'Available';
                                                            $statusClass = 'info';
                                                            
                                                            if ($clinicUpdate) {
                                                                if ($clinicUpdate->is_applied) {
                                                                    $status = 'Applied';
                                                                    $statusClass = 'success';
                                                                } elseif ($clinicUpdate->is_dismissed) {
                                                                    $status = 'Dismissed';
                                                                    $statusClass = 'secondary';
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="badge badge-sm bg-gradient-{{ $statusClass }}">
                                                            {{ $status }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="{{ route('system.updates.show', $update->id) }}" class="btn btn-link text-secondary mb-0">
                                                        <i class="fa fa-eye text-xs"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success" id="no-updates">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Your system is up to date! No new updates available.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning" id="update-error">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">{{ $updateCheck['message'] ?? 'Error checking for updates. Please try again later.' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle check for updates button
        const checkUpdatesBtn = document.getElementById('check-updates-btn');
        if (checkUpdatesBtn) {
            checkUpdatesBtn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
                this.disabled = true;
                
                fetch('{{ route("system.updates.check") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show updated results
                            window.location.reload();
                        } else {
                            alert('Error checking for updates: ' + data.message);
                            this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Check for Updates';
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while checking for updates.');
                        this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Check for Updates';
                        this.disabled = false;
                    });
            });
        }
    });
</script>
@endsection 