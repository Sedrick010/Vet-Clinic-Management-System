@extends('layouts.app')

@section('title', 'Version Downloads')
@section('page_name', 'Version Downloads')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Downloaded Versions</h5>
                        <p class="text-sm mb-0">Manage the downloaded version packages</p>
                    </div>
                    <div>
                        <a href="{{ route('version.download.all') }}" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-download me-1"></i> Download All Versions
                        </a>
                        <a href="{{ route('version.manage') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-arrow-left me-1"></i> Back to Version Management
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
                    
                    @if(session('warning'))
                        <div class="alert alert-warning" role="alert">
                            {{ session('warning') }}
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
                        
                        <div class="col-md-6">
                            <div class="card card-body border shadow-none bg-gradient-info mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-white shadow text-info rounded-circle">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="text-white mb-0">Downloaded Packages</h5>
                                        <p class="text-white text-sm mb-0">{{ count($downloadedVersions) }} version packages available</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Downloaded Versions Table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">File Size</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Downloaded Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($downloadedVersions) > 0)
                                    @foreach($downloadedVersions as $downloadedVersion)
                                    @php
                                        $versionObj = $versions->where('version', $downloadedVersion['version'])->first();
                                        $isCurrentVersion = $downloadedVersion['version'] === $currentVersion;
                                        $filesize = $downloadedVersion['size'];
                                        
                                        // Format file size
                                        if ($filesize < 1024) {
                                            $formattedSize = $filesize . ' B';
                                        } elseif ($filesize < 1024 * 1024) {
                                            $formattedSize = round($filesize / 1024, 2) . ' KB';
                                        } else {
                                            $formattedSize = round($filesize / (1024 * 1024), 2) . ' MB';
                                        }
                                        
                                        // Format download date
                                        $downloadDate = date('M d, Y H:i', $downloadedVersion['modified']);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div>
                                                    @if($isCurrentVersion)
                                                    <div class="icon icon-shape bg-gradient-success text-white shadow text-center rounded-circle icon-sm me-2">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                    @else
                                                    <div class="icon icon-shape bg-gradient-dark text-white shadow text-center rounded-circle icon-sm me-2">
                                                        <i class="fas fa-file-archive"></i>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $downloadedVersion['version'] }}</h6>
                                                    <p class="text-xs text-muted mb-0">{{ $versionObj->name ?? 'Unknown version' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $formattedSize }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">{{ $downloadDate }}</p>
                                        </td>
                                        <td>
                                            @if($isCurrentVersion)
                                                <span class="badge bg-gradient-success">Current</span>
                                            @else
                                                <span class="badge bg-gradient-dark">Available</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group">
                                                @if($versionObj && !$isCurrentVersion)
                                                <form action="{{ route('version.download.delete', $versionObj->id) }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this downloaded version?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-sm mb-0">No downloaded versions found.</p>
                                            <a href="{{ route('version.download.all') }}" class="btn btn-sm btn-primary mt-3">
                                                <i class="fas fa-download me-1"></i> Download All Versions
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Available Versions for Download -->
                    <h5 class="mt-5 mb-3">Available Versions for Download</h5>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Version</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Release Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Assets</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($versions as $version)
                                @php
                                    $isDownloaded = collect($downloadedVersions)->pluck('version')->contains($version->version);
                                    $isCurrentVersion = $version->version === $currentVersion;
                                    
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
                                                @elseif($isDownloaded)
                                                <div class="icon icon-shape bg-gradient-dark text-white shadow text-center rounded-circle icon-sm me-2">
                                                    <i class="fas fa-file-archive"></i>
                                                </div>
                                                @else
                                                <div class="icon icon-shape bg-gradient-secondary text-white shadow text-center rounded-circle icon-sm me-2">
                                                    <i class="fas fa-download"></i>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $version->version }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $version->name }}</p>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0">{{ $version->released_at ? $version->released_at->format('M d, Y') : 'N/A' }}</p>
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
                                        @elseif($isDownloaded)
                                            <span class="badge bg-gradient-dark">Downloaded</span>
                                        @else
                                            <span class="badge bg-gradient-secondary">Not Downloaded</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if(!$isDownloaded)
                                        <a href="{{ route('version.download.single', $version->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                        @elseif(!$isCurrentVersion)
                                        <div class="btn-group">
                                            <a href="{{ route('version.update', $version->id) }}" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Are you sure you want to update to version {{ $version->version }}?');">
                                                <i class="fas fa-arrow-up me-1"></i> Update
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 