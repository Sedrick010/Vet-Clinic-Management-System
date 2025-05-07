@extends('layouts.app')

@section('title', 'Request Subscription')

@section('page-name', 'Request Subscription')

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

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    New Subscription Request
                </div>
                <div class="card-body">
                    <div class="alert bg-gradient-primary border-0 text-white fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-white mb-1"><strong>Please Note:</strong></h6>
                                <p class="mb-0">All subscription requests require admin approval before they become active. Once approved, you will gain access to all features included in your selected plan. The approval process typically takes 1-2 business days.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('subscription.store') }}" method="POST" id="subscriptionForm">
                        @csrf
                        
                        @if(session()->has('tenant_user'))
                            @if(is_array(session('tenant_user')))
                                <input type="hidden" name="tenant_user_id" value="{{ session('tenant_user')['id'] }}">
                            @elseif(is_object(session('tenant_user')))
                                <input type="hidden" name="tenant_user_id" value="{{ session('tenant_user')->id }}">
                            @endif
                        @elseif(isset($userId) && $userId)
                            <input type="hidden" name="tenant_user_id" value="{{ $userId }}">
                        @endif
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Clinic Information</h4>
                                <p class="text-muted">Please provide your clinic details</p>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="clinic_name" class="form-label">Clinic Name <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" id="clinic_name" class="form-control @error('clinic_name') is-invalid @enderror" value="{{ old('clinic_name') }}" required>
                                @error('clinic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">Contact Email <span class="text-danger">*</span></label>
                                <input type="email" name="contact_email" id="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email') }}" required>
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" name="contact_phone" id="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone') }}" required>
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Select a Subscription Plan</h4>
                                <p class="text-muted">Choose the plan that best fits your clinic's needs</p>
                                
                                <div class="row">
                                    <!-- Free plan removed - automatically assigned on registration -->
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 plan-card {{ request('plan') == 'basic' ? 'border border-2 border-primary' : '' }}">
                                            <div class="card-header bg-light">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" 
                                                        id="planBasic" value="basic" {{ request('plan') == 'basic' ? 'checked' : '' }} 
                                                        {{ old('plan') == 'basic' ? 'checked' : '' }}
                                                        onclick="document.getElementById('totalAmount').textContent = '₱599.00';">
                                                    <label class="form-check-label fw-bold" for="planBasic">
                                                        Basic Plan - ₱599/month
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 100 appointments/month</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 200 products</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic Customization (Logo, 2 theme colors)</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 2 Admin/Staff Accounts</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Standard Reports (Appointment & Inventory Summary)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 plan-card {{ request('plan') == 'standard' || !request('plan') ? 'border border-2 border-primary' : '' }}">
                                            <div class="card-header bg-primary text-white">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" 
                                                        id="planStandard" value="standard" 
                                                        {{ (request('plan') == 'standard' || !request('plan') || old('plan') == '') && old('plan') != 'basic' && old('plan') != 'business' ? 'checked' : '' }}
                                                        {{ old('plan') == 'standard' ? 'checked' : '' }}
                                                        onclick="document.getElementById('totalAmount').textContent = '₱1,599.00';">
                                                    <label class="form-check-label fw-bold" for="planStandard">
                                                        Standard Plan - ₱1,599/month
                                                    </label>
                                                </div>
                                                <span class="badge bg-warning text-dark">Most Popular</span>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 500 appointments/month</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 500 products</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full clinic customization (logo, banners, multiple theme colors)</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 5 staff accounts</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full reports and basic analytics</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 plan-card {{ request('plan') == 'business' ? 'border border-2 border-primary' : '' }}">
                                            <div class="card-header bg-dark text-white">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" 
                                                        id="planBusiness" value="business" {{ request('plan') == 'business' ? 'checked' : '' }}
                                                        {{ old('plan') == 'business' ? 'checked' : '' }}
                                                        onclick="document.getElementById('totalAmount').textContent = '₱3,599.00';">
                                                    <label class="form-check-label fw-bold" for="planBusiness">
                                                        Business Plan - ₱3,599/month
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Appointments</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited inventory items</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Advanced customization</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Staff Account</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Complete analytics and custom reporting</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Patient Portal (Client can view their pet's info, appointment history)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @error('plan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Subscription Details</h4>
                                <p class="text-muted">Choose your subscription duration and options</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Subscription Duration <span class="text-danger">*</span></label>
                                <select name="duration" id="duration" class="form-select @error('duration') is-invalid @enderror" required>
                                    <option value="">Select Duration</option>
                                    <option value="1" {{ old('duration') == '1' ? 'selected' : '' }}>1 Month</option>
                                    <option value="3" {{ old('duration') == '3' ? 'selected' : '' }}>3 Months (5% discount)</option>
                                    <option value="6" {{ old('duration') == '6' ? 'selected' : '' }}>6 Months (10% discount)</option>
                                    <option value="12" {{ old('duration') == '12' ? 'selected' : '' }}>12 Months (15% discount)</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Additional Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" id="autoRenew" value="1" {{ old('auto_renew') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoRenew">
                                        Auto-renew subscription when it expires
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Payment Information</h4>
                                <p class="text-muted">Provide payment details for your subscription</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_reference" class="form-label">Payment Reference/Receipt Number <span class="text-danger">*</span></label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" value="{{ old('payment_reference') }}" required>
                                @error('payment_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="payment_details" class="form-label">Additional Payment Details</label>
                                <textarea name="payment_details" id="payment_details" class="form-control @error('payment_details') is-invalid @enderror" rows="3">{{ old('payment_details') }}</textarea>
                                <small class="text-muted">Please provide any additional information about your payment, such as the sender's name, date of payment, etc.</small>
                                @error('payment_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Additional Notes</h4>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert bg-gradient-primary border-0 text-white fade show" role="alert">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="fas fa-credit-card fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-2"><strong>Payment Instructions</strong></h6>
                                            <p class="mb-3">Please complete your payment before submitting this form. Your subscription will be reviewed and activated once the payment has been verified.</p>
                                            <p class="mb-2">For bank transfers, please use the following account details:</p>
                                            <ul class="list-unstyled mb-0 ps-0">
                                                <li class="mb-2"><i class="fas fa-university me-2"></i> Bank: Sample Bank of the Philippines</li>
                                                <li class="mb-2"><i class="fas fa-user me-2"></i> Account Name: VetClinic System Inc.</li>
                                                <li><i class="fas fa-hashtag me-2"></i> Account Number: 1234-5678-9012-3456</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Subscriptions
                                    </a>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <span class="fw-bold">Total Amount: </span>
                                            <span id="totalAmount" class="fs-5 text-primary">₱0.00</span>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-paper-plane me-1"></i> Submit Subscription Request
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Directly set the total amount to 699 fixed value
        document.getElementById('totalAmount').textContent = '₱699.00';

        // Add event listeners for plan selection
        const planRadios = document.querySelectorAll('input[name="plan"]');
        planRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Update card highlighting
                document.querySelectorAll('.plan-card').forEach(card => {
                    card.classList.remove('border', 'border-2', 'border-primary');
                });
                this.closest('.plan-card').classList.add('border', 'border-2', 'border-primary');
                
                // Always set total to 699 when any plan is selected
                document.getElementById('totalAmount').textContent = '₱699.00';
                console.log('Plan changed, set total to 699');
            });
        });
        
        // Form validation
        document.getElementById('subscriptionForm').addEventListener('submit', function(e) {
            const planSelected = document.querySelector('input[name="plan"]:checked');
            const durationSelected = document.getElementById('duration').value;
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (!planSelected) {
                e.preventDefault();
                alert('Please select a subscription plan');
                return false;
            }
            
            if (!durationSelected) {
                e.preventDefault();
                alert('Please select a subscription duration');
                return false;
            }
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection 