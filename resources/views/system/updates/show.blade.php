@extends('layouts.app')

@section('title', 'Update Details')
@section('page_name', 'Update Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Update Details</h5>
                        <p class="text-sm mb-0">Version {{ $update->version }} - {{ $update->name }}</p>
                    </div>
                    <a href="{{ route('system.updates.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Updates
                    </a>
                </div>
                <div class="card-body">
                    <!-- Update status alert -->
                    @if($clinicUpdate && $clinicUpdate->is_applied)
                        <div class="alert alert-success mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="text-white">Update Applied</h5>
                                    <p class="mb-0">This update was applied on {{ $clinicUpdate->applied_at->format('F j, Y \a\t g:i a') }}.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($clinicUpdate && $clinicUpdate->is_dismissed)
                        <div class="alert alert-secondary mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="text-white">Update Dismissed</h5>
                                    <p class="mb-0">This update was dismissed on {{ $clinicUpdate->dismissed_at->format('F j, Y \a\t g:i a') }}.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="text-white">Update Available</h5>
                                    <p class="mb-0">This update is available to be applied to your system.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Update details -->
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Update Information</h6>
                            
                            <div class="mb-4">
                                <h5>Description</h5>
                                <p>{{ $update->description }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Changes</h5>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($update->changes)) !!}
                                </div>
                            </div>
                            
                            @if($update->features)
                                <div class="mb-4">
                                    <h5>New Features</h5>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($update->features)) !!}
                                    </div>
                                </div>
                            @endif
                            
                            @if($update->bug_fixes)
                                <div class="mb-4">
                                    <h5>Bug Fixes</h5>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($update->bug_fixes)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Details</h6>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm text-muted">Version:</span>
                                        <span class="text-sm font-weight-bold">{{ $update->version }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm text-muted">Released:</span>
                                        <span class="text-sm font-weight-bold">{{ $update->available_from ? $update->available_from->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm text-muted">Type:</span>
                                        <span class="badge badge-sm bg-gradient-{{ $update->is_critical ? 'danger' : ($update->is_security ? 'warning' : 'info') }}">
                                            {{ $update->is_critical ? 'Critical' : ($update->is_security ? 'Security' : 'Feature') }}
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm text-muted">Required:</span>
                                        <span class="badge badge-sm bg-gradient-{{ $update->is_mandatory ? 'primary' : 'secondary' }}">
                                            {{ $update->is_mandatory ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm text-muted">Status:</span>
                                        @php
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
                                    </div>
                                    
                                    @if((!$clinicUpdate || (!$clinicUpdate->is_applied && !$clinicUpdate->is_dismissed)))
                                        <div class="mt-4">
                                            <form id="apply-form" action="{{ route('system.updates.apply', $update->id) }}" method="POST" style="display: inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" id="apply-btn">
                                                    <i class="fas fa-download me-1"></i> Apply Update
                                                </button>
                                            </form>
                                            
                                            @if(!$update->is_mandatory)
                                                <form id="dismiss-form" action="{{ route('system.updates.dismiss', $update->id) }}" method="POST" style="display: inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary ms-2" id="dismiss-btn">
                                                        <i class="fas fa-times me-1"></i> Dismiss
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif($clinicUpdate && $clinicUpdate->is_dismissed && !$update->is_mandatory)
                                        <div class="mt-4">
                                            <form id="apply-form" action="{{ route('system.updates.apply', $update->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" id="apply-btn">
                                                    <i class="fas fa-download me-1"></i> Apply Update
                                                </button>
                                            </form>
                                        </div>
                                    @endif
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle apply update form
        const applyForm = document.getElementById('apply-form');
        const applyBtn = document.getElementById('apply-btn');
        
        if (applyForm && applyBtn) {
            applyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to apply this update? This action cannot be undone.')) {
                    applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Applying...';
                    applyBtn.disabled = true;
                    this.submit();
                }
            });
        }
        
        // Handle dismiss update form
        const dismissForm = document.getElementById('dismiss-form');
        const dismissBtn = document.getElementById('dismiss-btn');
        
        if (dismissForm && dismissBtn) {
            dismissForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to dismiss this update? You can apply it later if needed.')) {
                    dismissBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Dismissing...';
                    dismissBtn.disabled = true;
                    this.submit();
                }
            });
        }
    });
</script>
@endsection 