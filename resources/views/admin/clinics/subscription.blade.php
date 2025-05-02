@extends('layouts.app')

@section('title', 'Manage Clinic Subscription')
@section('page_name', 'Manage Clinic Subscription')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Manage Subscription: {{ $clinic->name }}</h6>
                            <p class="text-sm mb-0">Update clinic subscription plan and settings</p>
                        </div>
                        <a href="{{ route('admin.clinics.show', $clinic) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Clinic Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clinics.subscription.update', $clinic->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_plan" class="form-control-label">Subscription Plan</label>
                                    <select id="subscription_plan" name="subscription_plan" class="form-control">
                                        @foreach($subscriptionPlans as $value => $label)
                                            <option value="{{ $value }}" {{ $clinic->subscription_plan === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_ends_at" class="form-control-label">Subscription End Date</label>
                                    <input type="date" id="subscription_ends_at" name="subscription_ends_at" class="form-control" 
                                        value="{{ $clinic->subscription_ends_at ? $clinic->subscription_ends_at->format('Y-m-d') : '' }}">
                                    <small class="text-muted">Leave blank for perpetual subscription</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3 mt-3">
                            <input class="form-check-input" type="checkbox" name="is_subscription_active" id="is_subscription_active" 
                                {{ $clinic->is_subscription_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_subscription_active">Subscription Active</label>
                        </div>
                        
                        <div id="deactivation_reason_container" class="form-group mb-3" style="{{ $clinic->is_subscription_active ? 'display: none;' : '' }}">
                            <label for="deactivation_reason" class="form-control-label">Deactivation Reason</label>
                            <textarea id="deactivation_reason" name="deactivation_reason" class="form-control" rows="3">{{ $clinic->deactivation_reason }}</textarea>
                            <small class="text-muted">Provide a reason for the subscription deactivation</small>
                        </div>
                        
                        <div class="alert alert-info mb-4">
                            <h6 class="text-white"><i class="fas fa-info-circle me-2"></i>Subscription Information</h6>
                            <ul class="mb-0 ps-4">
                                <li>Free plan: Limited features and up to 100 patient records</li>
                                <li>Basic plan: More features and up to 500 patient records</li>
                                <li>Premium plan: Full feature access and unlimited records</li>
                                <li><strong>Update Subscription:</strong> Change plan or end date without creating a new subscription record</li>
                                <li><strong>Change & Extend:</strong> Change plan and add more time to the subscription, creating a new subscription record</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.clinics.subscription.change.form', $clinic) }}" class="btn btn-info">
                                <i class="fas fa-exchange-alt me-1"></i> Change & Extend Subscription
                            </a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-1"></i> Update Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Clinic Activation -->
            <div class="card">
                <div class="card-header pb-0 {{ $clinic->is_active ? 'bg-light' : 'bg-light-danger' }}">
                    <h6 class="mb-0 {{ $clinic->is_active ? '' : 'text-danger' }}">Clinic Activation Status</h6>
                    <p class="text-sm mb-0">Toggle the clinic's activation status</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clinics.subscription.toggle-activation', $clinic->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                {{ $clinic->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ $clinic->is_active ? 'Clinic is Active' : 'Clinic is Inactive' }}
                            </label>
                        </div>
                        
                        <div id="clinic_deactivation_reason_container" class="form-group mb-3" style="{{ $clinic->is_active ? 'display: none;' : '' }}">
                            <label for="clinic_deactivation_reason" class="form-control-label">Deactivation Reason</label>
                            <textarea id="clinic_deactivation_reason" name="deactivation_reason" class="form-control" rows="3">{{ $clinic->deactivation_reason }}</textarea>
                            <small class="text-muted">Provide a reason for the clinic deactivation</small>
                        </div>
                        
                        <div class="alert alert-warning mb-4">
                            <h6 class="text-white"><i class="fas fa-exclamation-triangle me-2"></i>Warning</h6>
                            <p class="mb-0">Deactivating a clinic will prevent all users from accessing it. The database will remain intact, but inaccessible.</p>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn {{ $clinic->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas {{ $clinic->is_active ? 'fa-ban' : 'fa-check-circle' }} me-1"></i>
                                {{ $clinic->is_active ? 'Deactivate Clinic' : 'Activate Clinic' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle subscription deactivation reason
        const isSubscriptionActiveCheckbox = document.getElementById('is_subscription_active');
        const deactivationReasonContainer = document.getElementById('deactivation_reason_container');
        
        isSubscriptionActiveCheckbox.addEventListener('change', function() {
            deactivationReasonContainer.style.display = this.checked ? 'none' : 'block';
        });
        
        // Toggle clinic deactivation reason
        const isActiveCheckbox = document.getElementById('is_active');
        const clinicDeactivationReasonContainer = document.getElementById('clinic_deactivation_reason_container');
        
        isActiveCheckbox.addEventListener('change', function() {
            clinicDeactivationReasonContainer.style.display = this.checked ? 'none' : 'block';
        });
    });
</script>
@endpush
@endsection 