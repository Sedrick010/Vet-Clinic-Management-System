@extends('layouts.app')

@section('title', 'Version Backups')
@section('page_name', 'Version Backups')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">System Version Backups</h5>
                        <p class="text-sm mb-0">Manage backup files for system rollbacks</p>
                    </div>
                    <div>
                        <a href="{{ route('version.manage') }}" class="btn btn-sm btn-primary">
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
                                        <i class="fas fa-save"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="text-white mb-0">System Backups</h5>
                                        <p class="text-white text-sm mb-0">{{ count($backups) }} backup files available</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Backups Table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Backup</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Version</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Size</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($backups) > 0)
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div>
                                                    <div class="icon icon-shape bg-gradient-dark text-white shadow text-center rounded-circle icon-sm me-2">
                                                        <i class="fas fa-archive"></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $backup['filename'] }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $backup['version'] }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">{{ $backup['date'] }}</p>
                                        </td>
                                        <td>
                                            @php
                                                $size = $backup['size'];
                                                if ($size < 1024) {
                                                    $formattedSize = $size . ' B';
                                                } elseif ($size < 1024 * 1024) {
                                                    $formattedSize = round($size / 1024, 2) . ' KB';
                                                } else {
                                                    $formattedSize = round($size / (1024 * 1024), 2) . ' MB';
                                                }
                                            @endphp
                                            <p class="text-sm mb-0">{{ $formattedSize }}</p>
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group">
                                                <form action="{{ route('version.backups.restore') }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to restore from this backup? This will overwrite your current system state.');">
                                                    @csrf
                                                    <input type="hidden" name="backup_filename" value="{{ $backup['filename'] }}">
                                                    <button type="submit" class="btn btn-sm btn-success me-2">
                                                        <i class="fas fa-sync-alt me-1"></i> Restore
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('version.backups.delete') }}" method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this backup?');">
                                                    @csrf
                                                    <input type="hidden" name="backup_filename" value="{{ $backup['filename'] }}">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-sm mb-0">No backup files found.</p>
                                            <p class="text-sm text-muted mt-2">Backups are automatically created when you update or roll back your system.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Backup Guidelines -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-gradient-info">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-white shadow text-info rounded-circle">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="text-white mb-2">Backup Information</h5>
                                            <ul class="text-white mb-0">
                                                <li class="text-sm">Backups are automatically created before any version update or rollback</li>
                                                <li class="text-sm">Backups contain all your system files except for vendor, storage, and other excluded directories</li>
                                                <li class="text-sm">Custom settings, themes, and configurations are included in the backup</li>
                                                <li class="text-sm">Database content is not included in the backup - please back up your database separately</li>
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
@endsection 