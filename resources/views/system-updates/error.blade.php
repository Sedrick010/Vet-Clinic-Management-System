@extends('layouts.app')

@section('title', 'System Updates Error')
@section('page_name', 'System Updates Error')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 bg-gradient-danger shadow-danger border-radius-lg">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h5 class="mb-0 text-white">System Updates Error</h5>
                            <p class="text-sm mb-0 opacity-8">There was a problem with the system updates module</p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-3 pb-2">
                    <div class="alert alert-danger mx-4 mb-4">
                        <h5 class="alert-heading">Update System Error</h5>
                        <p>{{ $errorMessage }}</p>
                        
                        @if(config('app.debug') && isset($detailedError))
                            <hr>
                            <h6>Detailed Error (Debug Mode):</h6>
                            <p class="mb-0"><code>{{ $detailedError }}</code></p>
                        @endif
                    </div>
                    
                    <div class="row mx-3 mb-4">
                        <div class="col-md-6">
                            <div class="card card-body border shadow-sm h-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-white rounded-circle">
                                        <i class="fas fa-code-branch"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">Current Version</h5>
                                        <p class="text-sm mb-0">You are running <span class="font-weight-bold">v{{ $currentVersion ?? 'Unknown' }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mx-4">
                        <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i> Return to Dashboard
                            </a>
                            @if(auth()->user() && auth()->user()->role === 'admin')
                                <button onclick="tryFixTables()" class="btn btn-warning">
                                    <i class="fas fa-tools me-2"></i> Try to Fix Database Tables
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user() && auth()->user()->role === 'admin')
<script>
    function tryFixTables() {
        if (confirm('This will attempt to fix the system update database tables. Continue?')) {
            fetch('{{ route('updates.fix-tables') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tables fixed successfully! Reloading page...');
                    window.location.reload();
                } else {
                    alert('Error fixing tables: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    }
</script>
@endif
@endsection 