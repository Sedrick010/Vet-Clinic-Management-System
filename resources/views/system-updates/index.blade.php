@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">System Updates</h5>
                            <p class="text-sm mb-0">View and manage system updates for your clinic</p>
                        </div>
                        <div>
                            <a href="{{ route('updates.refresh') }}" class="btn btn-sm btn-primary" id="check-updates-btn">
                                <i class="fas fa-sync-alt me-1"></i> Check for Updates
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 mt-3" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 mt-3" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="alert alert-info mx-4 mt-3" role="alert">
                            {{ session('info') }}
                        </div>
                    @endif
                    
                    <div id="update-notifications">
                        @if($hasNewUpdate)
                            <div class="alert alert-warning mx-4 mt-3" role="alert">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        New update available: <strong>{{ $latestVersion }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-secondary opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($updates as $update)
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
                                            @if($update->is_critical)
                                                <span class="badge bg-gradient-danger">Critical</span>
                                            @endif
                                            @if($update->is_security)
                                                <span class="badge bg-gradient-warning">Security</span>
                                            @endif
                                            @if($update->is_mandatory)
                                                <span class="badge bg-gradient-primary">Mandatory</span>
                                            @endif
                                            @if(!$update->is_critical && !$update->is_security && !$update->is_mandatory)
                                                <span class="badge bg-gradient-info">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-secondary text-xs font-weight-bold">
                                                {{ $update->created_at->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
                                                $status = 'pending';
                                                
                                                if ($clinicId && $update->clinicUpdates) {
                                                    $clinicUpdate = $update->clinicUpdates->where('clinic_id', $clinicId)->first();
                                                    if ($clinicUpdate) {
                                                        if ($clinicUpdate->is_applied) {
                                                            $status = 'applied';
                                                        } elseif ($clinicUpdate->is_dismissed) {
                                                            $status = 'dismissed';
                                                        }
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($status == 'applied')
                                                <span class="badge bg-gradient-success">Applied</span>
                                            @elseif($status == 'dismissed')
                                                <span class="badge bg-gradient-secondary">Dismissed</span>
                                            @else
                                                <span class="badge bg-gradient-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-link text-secondary mb-0 me-2" data-bs-toggle="modal" data-bs-target="#updateModal{{ $update->id }}">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                
                                                @if($status != 'applied')
                                                    <form action="{{ route('updates.apply', $update->id) }}" method="POST" class="d-inline me-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-success mb-0" 
                                                            onclick="return confirm('Are you sure you want to apply this update?');">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($status == 'pending' && !$update->is_mandatory)
                                                    <form action="{{ route('updates.dismiss', $update->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-danger mb-0" 
                                                            onclick="return confirm('Are you sure you want to dismiss this update?');">
                                                            <i class="fas fa-times text-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Update Details Modal -->
                                    <div class="modal fade" id="updateModal{{ $update->id }}" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel{{ $update->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="updateModalLabel{{ $update->id }}">
                                                        {{ $update->name }} ({{ $update->version }})
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if($update->description)
                                                    <div class="mb-3">
                                                        <h6>Description</h6>
                                                        <p>{{ $update->description }}</p>
                                                    </div>
                                                    @endif
                                                    
                                                    @if($update->changes)
                                                    <div class="mb-3">
                                                        <h6>Changes</h6>
                                                        <p>{{ $update->changes }}</p>
                                                    </div>
                                                    @endif
                                                    
                                                    @if($update->features)
                                                        <div class="mb-3">
                                                            <h6>New Features</h6>
                                                            <ul>
                                                                @foreach(explode("\n", $update->features) as $feature)
                                                                    <li>{{ $feature }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($update->bug_fixes)
                                                        <div class="mb-3">
                                                            <h6>Bug Fixes</h6>
                                                            <ul>
                                                                @foreach(explode("\n", $update->bug_fixes) as $fix)
                                                                    <li>{{ $fix }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="mb-3">
                                                        <h6>Update Type</h6>
                                                        <div>
                                                            @if($update->is_critical)
                                                                <span class="badge bg-gradient-danger">Critical</span>
                                                            @endif
                                                            @if($update->is_security)
                                                                <span class="badge bg-gradient-warning">Security</span>
                                                            @endif
                                                            @if($update->is_mandatory)
                                                                <span class="badge bg-gradient-primary">Mandatory</span>
                                                            @endif
                                                            @if(!$update->is_critical && !$update->is_security && !$update->is_mandatory)
                                                                <span class="badge bg-gradient-info">Optional</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    @php
                                                        $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
                                                        $status = 'pending';
                                                        
                                                        if ($clinicId && $update->clinicUpdates) {
                                                            $clinicUpdate = $update->clinicUpdates->where('clinic_id', $clinicId)->first();
                                                            if ($clinicUpdate) {
                                                                if ($clinicUpdate->is_applied) {
                                                                    $status = 'applied';
                                                                } elseif ($clinicUpdate->is_dismissed) {
                                                                    $status = 'dismissed';
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    @if($status != 'applied')
                                                        <form action="{{ route('updates.apply', $update->id) }}" method="POST" class="d-inline me-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary" 
                                                                onclick="return confirm('Are you sure you want to apply this update?');">
                                                                Apply Update
                                                            </button>
                                                        </form>
                                                        
                                                        @if(!$update->is_mandatory)
                                                            <form action="{{ route('updates.dismiss', $update->id) }}" method="POST" class="d-inline me-2">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-secondary" 
                                                                    onclick="return confirm('Are you sure you want to dismiss this update?');">
                                                                    Dismiss Update
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-sm mb-0">No updates available.</p>
                                            <p class="text-xs text-secondary mb-0">Click "Check for Updates" to check for new updates.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkUpdatesBtn = document.getElementById('check-updates-btn');
        
        checkUpdatesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            checkUpdatesBtn.disabled = true;
            checkUpdatesBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
            
            // Make AJAX request to check for updates
            fetch('{{ route('updates.refresh') }}')
                .then(response => response.json())
                .then(data => {
                    // Reset button state
                    checkUpdatesBtn.disabled = false;
                    checkUpdatesBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Check for Updates';
                    
                    if (data.success) {
                        // Show success notification
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-success mx-4 mt-3';
                        notification.role = 'alert';
                        notification.innerHTML = data.message;
                        
                        const notificationsContainer = document.getElementById('update-notifications');
                        notificationsContainer.innerHTML = '';
                        notificationsContainer.appendChild(notification);
                        
                        // Reload page after a short delay to show updates
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Show error notification
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-danger mx-4 mt-3';
                        notification.role = 'alert';
                        notification.innerHTML = data.message;
                        
                        const notificationsContainer = document.getElementById('update-notifications');
                        notificationsContainer.innerHTML = '';
                        notificationsContainer.appendChild(notification);
                    }
                })
                .catch(error => {
                    // Reset button state
                    checkUpdatesBtn.disabled = false;
                    checkUpdatesBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Check for Updates';
                    
                    // Show error notification
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-danger mx-4 mt-3';
                    notification.role = 'alert';
                    notification.innerHTML = 'An error occurred while checking for updates.';
                    
                    const notificationsContainer = document.getElementById('update-notifications');
                    notificationsContainer.innerHTML = '';
                    notificationsContainer.appendChild(notification);
                    
                    console.error('Error checking for updates:', error);
                });
        });
    });
</script>
@endpush
@endsection 