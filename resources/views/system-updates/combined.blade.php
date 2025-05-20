@extends('layouts.app')

@section('title', 'System Updates & Version Management')
@section('page_name', 'System Updates & Version Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 bg-gradient-primary shadow-primary border-radius-lg">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h5 class="mb-0 text-white">System Updates & Version Management</h5>
                            <p class="text-sm mb-0 opacity-8">Manage your system version and apply updates</p>
                        </div>
                        <div>
                            <a href="{{ route('version.backups') }}" class="btn btn-sm btn-white me-2">
                                <i class="fas fa-save me-1"></i> Version Backups
                            </a>
                            <a href="{{ route('version.check') }}" class="btn btn-sm btn-white" id="check-updates-btn">
                                <i class="fas fa-sync-alt me-1"></i> Check for Updates
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-3 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="alert alert-info mx-4 mb-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                        </div>
                    @endif
                    
                    <!-- Current Version & Updates Cards -->
                    <div class="row mx-3 mb-4">
                        <div class="col-md-6">
                            <div class="card card-body border shadow-sm h-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-white rounded-circle">
                                        <i class="fas fa-code-branch"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">Current Version</h5>
                                        <p class="text-sm mb-0">You are running <span class="font-weight-bold">v{{ $currentVersion }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($hasNewUpdate)
                        <div class="col-md-6">
                            <div class="card card-body border shadow-sm bg-gradient-success h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-white shadow text-success rounded-circle">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="text-white mb-0">Update Available</h5>
                                            <p class="text-white text-sm mb-0">Version <span class="font-weight-bold">{{ $latestVersion }}</span> is available</p>
                                        </div>
                                    </div>
                                    
                                    @php
                                        $latestVersionObj = $versions->first();
                                    @endphp
                                    
                                    @if($latestVersionObj)
                                    <a href="{{ route('version.update', $latestVersionObj->id) }}" 
                                       class="btn btn-sm btn-outline-white" 
                                       onclick="return confirm('Are you sure you want to update to version {{ $latestVersionObj->version }}?');">
                                        <i class="fas fa-download me-1"></i> Update Now
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills nav-fill mx-4 mb-4" id="systemUpdatesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="versions-tab" data-bs-toggle="tab" data-bs-target="#versions" type="button" role="tab" aria-controls="versions" aria-selected="true">
                                <i class="fas fa-history me-1"></i> Version History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="updates-tab" data-bs-toggle="tab" data-bs-target="#updates" type="button" role="tab" aria-controls="updates" aria-selected="false">
                                <i class="fas fa-sync me-1"></i> Available Updates
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">
                                <i class="fas fa-puzzle-piece me-1"></i> Version Features
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="systemUpdatesTabsContent">
                        <!-- Version History Tab -->
                        <div class="tab-pane fade show active" id="versions" role="tabpanel" aria-labelledby="versions-tab">
                            <div class="table-responsive mx-4">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Release Date</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                            <th class="text-secondary opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($versions as $version)
                                        @php
                                            $isCurrentVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '==');
                                            $isNewerVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '>');
                                            $isOlderVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '<');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        @if($isCurrentVersion)
                                                        <div class="icon icon-shape bg-gradient-success text-white shadow text-center rounded-circle icon-sm me-2">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                        @elseif($isNewerVersion)
                                                        <div class="icon icon-shape bg-gradient-info text-white shadow text-center rounded-circle icon-sm me-2">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </div>
                                                        @else
                                                        <div class="icon icon-shape bg-gradient-secondary text-white shadow text-center rounded-circle icon-sm me-2">
                                                            <i class="fas fa-history"></i>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $version->version }}</h6>
                                                        <p class="text-xs text-muted mb-0">{{ $version->name }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $version->released_at ? $version->released_at->format('M d, Y') : 'N/A' }}</p>
                                            </td>
                                            <td>
                                                @if($isCurrentVersion)
                                                    <span class="badge bg-gradient-success">Current</span>
                                                @elseif($isNewerVersion)
                                                    <span class="badge bg-gradient-info">Update Available</span>
                                                @else
                                                    <span class="badge bg-gradient-secondary">Previous</span>
                                                @endif
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $version->description }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-link text-secondary mb-0 me-2" data-bs-toggle="modal" data-bs-target="#versionDetailsModal{{ $version->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($isNewerVersion)
                                                    <a href="{{ route('version.update', $version->id) }}" 
                                                       class="btn btn-link text-info mb-0 me-2"
                                                       onclick="return confirm('Are you sure you want to update to version {{ $version->version }}?');">
                                                        <i class="fas fa-arrow-up"></i>
                                                    </a>
                                                    @elseif($isOlderVersion)
                                                    <form action="{{ route('version.rollback', $version->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-warning mb-0" 
                                                            onclick="return confirm('Are you sure you want to roll back to version {{ $version->version }}? This might affect functionality. Please backup your data before proceeding.');">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Version Details Modal -->
                                        <div class="modal fade" id="versionDetailsModal{{ $version->id }}" tabindex="-1" role="dialog" aria-labelledby="versionDetailsModalLabel{{ $version->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="versionDetailsModalLabel{{ $version->id }}">
                                                            {{ $version->name }} ({{ $version->version }})
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-3">{{ $version->description }}</p>
                                                        <p class="text-sm mb-1">
                                                            <strong>Released:</strong> {{ $version->released_at ? $version->released_at->format('M d, Y') : 'N/A' }}
                                                        </p>
                                                        <p class="text-sm mb-1">
                                                            <strong>Status:</strong> 
                                                            @if($isCurrentVersion)
                                                                <span class="badge bg-gradient-success">Current</span>
                                                            @elseif($isNewerVersion)
                                                                <span class="badge bg-gradient-info">Update Available</span>
                                                            @else
                                                                <span class="badge bg-gradient-secondary">Previous</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        @if($isNewerVersion)
                                                        <a href="{{ route('version.update', $version->id) }}" 
                                                           class="btn btn-primary"
                                                           onclick="return confirm('Are you sure you want to update to version {{ $version->version }}?');">
                                                            <i class="fas fa-arrow-up me-1"></i> Update
                                                        </a>
                                                        @elseif($isOlderVersion)
                                                        <form action="{{ route('version.rollback', $version->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning" 
                                                                onclick="return confirm('Are you sure you want to roll back to version {{ $version->version }}? This might affect functionality. Please backup your data before proceeding.');">
                                                                <i class="fas fa-arrow-down me-1"></i> Rollback
                                                            </button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Available Updates Tab -->
                        <div class="tab-pane fade" id="updates" role="tabpanel" aria-labelledby="updates-tab">
                            <div class="table-responsive mx-4">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
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
                                                            <p class="text-xs text-muted mb-0">{{ $update->name }}</p>
                                                        </div>
                                                    </div>
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
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        @if($status != 'applied')
                                                            <form action="{{ route('updates.apply', $update->id) }}" method="POST" class="d-inline me-2">
                                                                @csrf
                                                                <button type="submit" class="btn btn-link text-success mb-0" 
                                                                    onclick="return confirm('Are you sure you want to apply this update?');">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        
                                                        @if($status == 'pending' && !$update->is_mandatory)
                                                            <form action="{{ route('updates.dismiss', $update->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-link text-danger mb-0" 
                                                                    onclick="return confirm('Are you sure you want to dismiss this update?');">
                                                                    <i class="fas fa-times"></i>
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
                                                                            @if(trim($feature))
                                                                                <li>{{ $feature }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                            
                                                            @if($update->bug_fixes)
                                                                <div class="mb-3">
                                                                    <h6>Bug Fixes</h6>
                                                                    <ul>
                                                                        @foreach(explode("\n", $update->bug_fixes) as $fix)
                                                                            @if(trim($fix))
                                                                                <li>{{ $fix }}</li>
                                                                            @endif
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
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            
                                                            @if($status != 'applied')
                                                                <form action="{{ route('updates.apply', $update->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success" 
                                                                        onclick="return confirm('Are you sure you want to apply this update?');">
                                                                        Apply Update
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <p class="text-sm mb-0">No updates available</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Version Features Tab -->
                        <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
                            <div class="row mx-3">
                                @foreach($versions->take(4) as $version)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header p-3 pb-0">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    @if(version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '=='))
                                                    <div class="icon icon-shape bg-gradient-success text-white shadow text-center rounded-circle">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                    @elseif(version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '>'))
                                                    <div class="icon icon-shape bg-gradient-info text-white shadow text-center rounded-circle">
                                                        <i class="fas fa-arrow-up"></i>
                                                    </div>
                                                    @else
                                                    <div class="icon icon-shape bg-gradient-secondary text-white shadow text-center rounded-circle">
                                                        <i class="fas fa-history"></i>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="ms-3">
                                                    <h5 class="mb-0">{{ $version->version }}</h5>
                                                    <p class="text-sm mb-0">{{ $version->name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <p class="mb-3">{{ $version->description }}</p>
                                            <p class="text-xs text-muted mb-0">Released: {{ $version->released_at ? $version->released_at->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Version Management Guidelines</h5>
                    <p class="text-sm mb-0">Important information about system updates</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading mb-1">Before updating your system:</h6>
                        <ul class="mb-0">
                            <li>Create a backup of your current system version</li>
                            <li>Review the update details to understand what's changing</li>
                            <li>Test updates in a non-production environment if possible</li>
                            <li>Check for compatibility with your current workflows</li>
                        </ul>
                    </div>
                    <p class="mb-0">If you encounter any issues during or after an update, please contact support or restore from a backup.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 