@extends('layouts.app')

@section('title', 'Change Clinic Subscription')
@section('page_name', 'Change Clinic Subscription')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Change Subscription: {{ $clinic->name }}</h6>
                            <p class="text-sm mb-0">Update clinic subscription plan and extend subscription period</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-info me-2">
                                <i class="fas fa-list me-1"></i> Back to Subscriptions
                            </a>
                            <a href="{{ route('admin.clinics.show', $clinic) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Clinic Details
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-none border">
                                <div class="card-body p-3">
                                    <h6 class="mb-2">Current Subscription</h6>
                                    <p class="mb-1"><strong>Plan:</strong> 
                                        <span class="badge bg-primary">{{ ucfirst($clinic->subscription_plan ?? 'None') }}</span>
                                    </p>
                                    <p class="mb-1"><strong>Status:</strong>
                                        @if($clinic->is_subscription_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </p>
                                    <p class="mb-0"><strong>Expires:</strong>
                                        @if($clinic->subscription_ends_at)
                                            {{ $clinic->subscription_ends_at->format('M d, Y') }}
                                            <small class="text-muted">({{ $clinic->subscription_ends_at->diffForHumans() }})</small>
                                        @else
                                            <span class="text-muted">No expiration date</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.clinics.subscription.change', $clinic->id) }}">
                        @csrf
                        @method('POST')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_plan" class="form-control-label">New Subscription Plan</label>
                                    <select id="subscription_plan" name="subscription_plan" class="form-control" required>
                                        <option value="">Select a plan</option>
                                        <option value="free" {{ old('subscription_plan') == 'free' ? 'selected' : '' }}>Free Plan</option>
                                        <option value="basic" {{ old('subscription_plan') == 'basic' ? 'selected' : '' }}>Basic Plan</option>
                                        <option value="standard" {{ old('subscription_plan') == 'standard' ? 'selected' : '' }}>Standard Plan</option>
                                        <option value="business" {{ old('subscription_plan') == 'business' ? 'selected' : '' }}>Business Plan</option>
                                        <option value="premium" {{ old('subscription_plan') == 'premium' ? 'selected' : '' }}>Premium Plan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_months" class="form-control-label">Extension Duration (months)</label>
                                    <select id="duration_months" name="duration_months" class="form-control" required>
                                        <option value="">Select duration</option>
                                        <option value="1" {{ old('duration_months') == 1 ? 'selected' : '' }}>1 month</option>
                                        <option value="3" {{ old('duration_months') == 3 ? 'selected' : '' }}>3 months</option>
                                        <option value="6" {{ old('duration_months') == 6 ? 'selected' : '' }}>6 months</option>
                                        <option value="12" {{ old('duration_months') == 12 ? 'selected' : '' }}>12 months</option>
                                        <option value="24" {{ old('duration_months') == 24 ? 'selected' : '' }}>24 months</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="admin_notes" class="form-control-label">Admin Notes</label>
                            <textarea id="admin_notes" name="admin_notes" class="form-control" rows="3">{{ old('admin_notes') }}</textarea>
                            <small class="text-muted">Reason for changing the subscription (optional)</small>
                        </div>
                        
                        <div class="alert alert-info mb-4">
                            <h6 class="text-white"><i class="fas fa-info-circle me-2"></i>Subscription Change Information</h6>
                            <ul class="mb-0 ps-4">
                                <li>The current subscription will be updated to the selected plan immediately</li>
                                <li>If the current subscription is active, the new duration will be added to the remaining time</li>
                                <li>If the current subscription is expired, the new duration will start from today</li>
                                <li>This change will be recorded in the subscription history</li>
                                <li>The clinic will be notified via email about this change</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.clinics.show', $clinic) }}" class="btn btn-outline-secondary me-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-1"></i> Change Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 