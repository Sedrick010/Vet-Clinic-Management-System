@extends('layouts.app')

@section('title', 'Version Management')
@section('page_name', 'Version Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">System Versions</h5>
                        <p class="text-sm mb-0">Manage and update your system version</p>
                        <!-- VERSION INDICATOR - This helps verify successful updates -->
                        <div class="mt-2">
                            <span class="badge bg-primary">VERSION IDENTIFIER: MANAGE-UI-v4.0.3</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('version.roadmap') }}" class="btn btn-sm btn-dark me-2">
                            <i class="fas fa-road me-1"></i> Feature Roadmap
                        </a>
                        <a href="{{ route('version.backups') }}" class="btn btn-sm btn-info me-2">
                            <i class="fas fa-save me-1"></i> Version Backups
                        </a>
                        <a href="{{ route('version.check') }}" class="btn btn-sm btn-primary" id="check-updates-btn">
                            <i class="fas fa-sync-alt me-1"></i> Check for Updates
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="alert alert-info" role="alert">
                            {{ session('info') }}
                        </div>
                    @endif
                    
                    <!-- Current Version Card -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card card-body border shadow-none mb-4">
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
                            <div class="card card-body border shadow-none bg-gradient-success mb-4">
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
                    <ul class="nav nav-tabs mb-4" id="versionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="versions-tab" data-bs-toggle="tab" data-bs-target="#versions" type="button" role="tab" aria-controls="versions" aria-selected="true">
                                <i class="fas fa-history me-1"></i> Version History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">
                                <i class="fas fa-puzzle-piece me-1"></i> Version Features
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="versionTabsContent">
                        <!-- Version History Tab -->
                        <div class="tab-pane fade show active" id="versions" role="tabpanel" aria-labelledby="versions-tab">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Release Date</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Assets</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($versions as $version)
                                        @php
                                            $isCurrentVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '==');
                                            $isNewerVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '>');
                                            $isOlderVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '<');
                                            
                                            // Find release details in the pre-fetched data
                                            $releaseDetails = null;
                                            foreach ($allReleases as $release) {
                                                if ($release['version'] === $version->version) {
                                                    $releaseDetails = $release;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
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
                                                @if($releaseDetails && !empty($releaseDetails['assets']))
                                                    @foreach($releaseDetails['assets'] as $asset)
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="fas fa-file-archive text-primary me-1"></i>
                                                            <span class="text-sm">
                                                                {{ $asset['name'] }}
                                                                @if(isset($asset['size']))
                                                                    @php
                                                                        $size = $asset['size'];
                                                                        if ($size < 1024) {
                                                                            $formattedSize = $size . ' B';
                                                                        } elseif ($size < 1024 * 1024) {
                                                                            $formattedSize = round($size / 1024, 2) . ' KB';
                                                                        } else {
                                                                            $formattedSize = round($size / (1024 * 1024), 2) . ' MB';
                                                                        }
                                                                    @endphp
                                                                    <span class="text-xs text-muted">({{ $formattedSize }})</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-sm mb-0">
                                                        <i class="fas fa-code text-info me-1"></i>
                                                        Standard source code
                                                    </p>
                                                @endif
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
                                                <div class="btn-group">
                                                    @if($isNewerVersion)
                                                    <a href="{{ route('version.update', $version->id) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       onclick="return confirm('Are you sure you want to update to version {{ $version->version }}?');">
                                                        <i class="fas fa-arrow-up me-1"></i> Update
                                                    </a>
                                                    @elseif($isOlderVersion)
                                                    <form action="{{ route('version.rollback', $version->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                            onclick="return confirm('Are you sure you want to roll back to version {{ $version->version }}? This might affect functionality. Please backup your data before proceeding.');">
                                                            <i class="fas fa-arrow-down me-1"></i> Rollback
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Version Features Tab -->
                        <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
                            <div class="row">
                                @foreach($versions as $version)
                                @php
                                    $isCurrentVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '==');
                                    $isNewerVersion = version_compare(ltrim($version->version, 'v'), ltrim($currentVersion, 'v'), '>');
                                    
                                    // Find release details in the pre-fetched data
                                    $releaseDetails = null;
                                    foreach ($allReleases as $release) {
                                        if ($release['version'] === $version->version) {
                                            $releaseDetails = $release;
                                            break;
                                        }
                                    }
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header pb-0 px-3">
                                            <div class="d-flex align-items-center">
                                                @if($isCurrentVersion)
                                                <span class="badge bg-gradient-success me-2">Current</span>
                                                @elseif($isNewerVersion)
                                                <span class="badge bg-gradient-info me-2">Newer</span>
                                                @else
                                                <span class="badge bg-gradient-secondary me-2">Previous</span>
                                                @endif
                                                <h6 class="mb-0">{{ $version->version }} - {{ $version->name }}</h6>
                                            </div>
                                            <p class="text-sm text-muted mb-0">Released: {{ $version->released_at ? $version->released_at->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                        <div class="card-body px-3 pt-2 pb-3">
                                            <div class="mb-3">
                                                <h6 class="text-sm">Description</h6>
                                                <p class="text-sm mb-0">{{ $version->description }}</p>
                                            </div>
                                            
                                            @if($releaseDetails && !empty($releaseDetails['assets']))
                                            <div class="mb-3">
                                                <h6 class="text-sm">Release Assets</h6>
                                                <div class="list-group list-group-flush">
                                                    @foreach($releaseDetails['assets'] as $asset)
                                                        <div class="list-group-item border-0 p-2">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-file-archive text-primary me-2"></i>
                                                                <div>
                                                                    <h6 class="text-sm mb-0">{{ $asset['name'] }}</h6>
                                                                    @if(isset($asset['size']))
                                                                        @php
                                                                            $size = $asset['size'];
                                                                            if ($size < 1024) {
                                                                                $formattedSize = $size . ' B';
                                                                            } elseif ($size < 1024 * 1024) {
                                                                                $formattedSize = round($size / 1024, 2) . ' KB';
                                                                            } else {
                                                                                $formattedSize = round($size / (1024 * 1024), 2) . ' MB';
                                                                            }
                                                                        @endphp
                                                                        <p class="text-xs text-muted mb-0">Size: {{ $formattedSize }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            
                                            @if($releaseDetails && (!empty($releaseDetails['features']) || !empty($releaseDetails['bug_fixes'])))
                                            <div class="mb-3">
                                                <h6 class="text-sm">Release Notes</h6>
                                                
                                                @if(!empty($releaseDetails['features']))
                                                <div class="mb-2">
                                                    <p class="text-xs text-uppercase font-weight-bold mb-1">Features:</p>
                                                    <ul class="ps-3 mb-0">
                                                        @foreach($releaseDetails['features'] as $feature)
                                                            <li class="text-sm">{{ $feature }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif
                                                
                                                @if(!empty($releaseDetails['bug_fixes']))
                                                <div>
                                                    <p class="text-xs text-uppercase font-weight-bold mb-1">Bug Fixes:</p>
                                                    <ul class="ps-3 mb-0">
                                                        @foreach($releaseDetails['bug_fixes'] as $bugFix)
                                                            <li class="text-sm">{{ $bugFix }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            @if($isNewerVersion)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('version.update', $version->id) }}" 
                                                   class="btn btn-sm btn-primary me-2"
                                                   onclick="return confirm('Are you sure you want to update to version {{ $version->version }}?');">
                                                    <i class="fas fa-arrow-up me-1"></i> Update to this version
                                                </a>
                                            </div>
                                            @elseif(!$isCurrentVersion)
                                            <div class="text-center mt-3">
                                                <form action="{{ route('version.rollback', $version->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                        onclick="return confirm('Are you sure you want to roll back to version {{ $version->version }}? This might affect functionality. Please backup your data before proceeding.');">
                                                        <i class="fas fa-arrow-down me-1"></i> Roll back to this version
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Upgrade Guidelines -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-gradient-info">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-white shadow text-info rounded-circle">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="text-white mb-2">Version Management Guidelines</h5>
                                            <ul class="text-white mb-0">
                                                <li class="text-sm">Always backup your data before updating or rolling back versions</li>
                                                <li class="text-sm">Version-gated features will automatically become available when you update</li>
                                                <li class="text-sm">Rolling back to older versions may result in loss of newer features</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkUpdatesBtn = document.getElementById('check-updates-btn');
        
        if (checkUpdatesBtn) {
            checkUpdatesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Change button text to show loading state
                checkUpdatesBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
                checkUpdatesBtn.disabled = true;
                
                // Redirect to check updates route
                window.location.href = checkUpdatesBtn.getAttribute('href');
            });
        }
    });
</script>
@endsection 