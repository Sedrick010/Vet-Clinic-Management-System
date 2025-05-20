@extends('layouts.app')

@section('title', 'Admin - Subscription Request Details')

@section('page-name', 'Subscription Request Details')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i> Subscription Request #{{ $subscriptionRequest->id }}
            </h4>
            <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to All Requests
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-1"></i>
                        Request Details
                    </div>
                    <span class="badge bg-{{ $subscriptionRequest->status == 'pending' ? 'warning text-dark' : ($subscriptionRequest->status == 'approved' || $subscriptionRequest->status == 'active' ? 'success' : 'danger') }}">
                        {{ ucfirst($subscriptionRequest->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">Requested By</h6>
                            @if($subscriptionRequest->user_id)
                                <p class="mb-0">{{ $subscriptionRequest->user->name ?? 'N/A' }}</p>
                                <p class="text-muted mb-0">{{ $subscriptionRequest->user->email ?? 'N/A' }}</p>
                            @else
                                <p class="mb-0"><strong>{{ $subscriptionRequest->guest_clinic_name }}</strong></p>
                                <p class="text-muted mb-0">{{ $subscriptionRequest->guest_email }}</p>
                                <p class="text-muted mb-0">{{ $subscriptionRequest->guest_phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">Plan Details</h6>
                            <p class="mb-0">
                                <span class="badge bg-{{ $subscriptionRequest->plan == 'free' ? 'secondary' : ($subscriptionRequest->plan == 'basic' ? 'info' : ($subscriptionRequest->plan == 'standard' ? 'primary' : 'dark')) }}">
                                    {{ ucfirst($subscriptionRequest->plan) }} Plan
                                </span>
                                for {{ $subscriptionRequest->duration }} month(s)
                            </p>
                            <p class="fw-bold text-success mb-0">₱{{ number_format($subscriptionRequest->amount_paid, 2) }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">Payment Method</h6>
                            <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $subscriptionRequest->payment_method)) }}</p>
                            @if($subscriptionRequest->payment_reference)
                            <p class="text-muted mb-0">Reference: {{ $subscriptionRequest->payment_reference }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">Request Dates</h6>
                            <p class="mb-0"><strong>Submitted:</strong> {{ $subscriptionRequest->created_at->format('M d, Y g:i A') }}</p>
                            @if($subscriptionRequest->status == 'approved' || $subscriptionRequest->status == 'active')
                            <p class="mb-0"><strong>Approved:</strong> {{ Carbon\Carbon::parse($subscriptionRequest->approved_at)->format('M d, Y g:i A') }}</p>
                            <p class="mb-0"><strong>Expires:</strong> {{ Carbon\Carbon::parse($subscriptionRequest->expired_at)->format('M d, Y g:i A') }}</p>
                            @elseif($subscriptionRequest->status == 'rejected')
                            <p class="mb-0"><strong>Rejected:</strong> {{ Carbon\Carbon::parse($subscriptionRequest->rejected_at)->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($subscriptionRequest->payment_details)
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <h6 class="fw-bold text-muted">Payment Details</h6>
                            <p class="mb-0">{{ $subscriptionRequest->payment_details }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($subscriptionRequest->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="fw-bold text-muted">Additional Notes</h6>
                            <p class="mb-0">{{ $subscriptionRequest->notes }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($subscriptionRequest->status != 'pending' && $subscriptionRequest->admin_notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="fw-bold text-muted">Admin Notes</h6>
                            <p class="mb-0">{{ $subscriptionRequest->admin_notes }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($subscriptionRequest->status == 'rejected' && $subscriptionRequest->rejection_reason)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="fw-bold text-muted">Rejection Reason</h6>
                            <p class="mb-0">{{ $subscriptionRequest->rejection_reason }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($subscriptionRequest->status == 'pending')
                    <hr>
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i> Approve Subscription
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.subscription-requests.approve', $subscriptionRequest->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="expiration_date" class="form-label">Expiration Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="expiration_date" name="expiration_date" 
                                                value="{{ Carbon\Carbon::now()->addMonths($subscriptionRequest->duration)->format('Y-m-d') }}" required>
                                            <small class="text-muted">Default is {{ $subscriptionRequest->duration }} month(s) from today</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_notes" class="form-label">Admin Notes</label>
                                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Optional notes about this approval"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check me-1"></i> Approve Request
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <i class="fas fa-times-circle me-1"></i> Reject Subscription
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.subscription-requests.reject', $subscriptionRequest->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Provide reason for rejection" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_notes_reject" class="form-label">Admin Notes</label>
                                            <textarea class="form-control" id="admin_notes_reject" name="admin_notes" rows="3" placeholder="Optional internal notes"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="fas fa-times me-1"></i> Reject Request
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to All Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 