@extends('layouts.app')

@section('title', 'Edit Subscription')

@section('page-name', 'Edit Subscription')

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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-edit me-1"></i>
                        Edit Subscription Request
                    </div>
                    <a href="{{ route('subscription.show', $subscription->id) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Details
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('subscription.update', $subscription->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Plan Selection</h4>
                                <p class="text-muted">Select your subscription plan</p>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 {{ $subscription->plan == 'basic' ? 'border-primary' : '' }}">
                                            <div class="card-header bg-light">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" id="basicPlan" value="basic" {{ $subscription->plan == 'basic' ? 'checked' : '' }} required>
                                                    <label class="form-check-label fw-bold" for="basicPlan">
                                                        Basic Plan
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">₱4,999/month</h5>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i> Up to 500 patient records</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 5 staff accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 2 vet accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Basic reporting</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 {{ $subscription->plan == 'standard' ? 'border-primary' : '' }}">
                                            <div class="card-header bg-light">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" id="standardPlan" value="standard" {{ $subscription->plan == 'standard' ? 'checked' : '' }} required>
                                                    <label class="form-check-label fw-bold" for="standardPlan">
                                                        Standard Plan
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">₱4,999/month</h5>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i> Up to 2,000 patient records</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 10 staff accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 5 vet accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Advanced reporting</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Inventory management</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 {{ $subscription->plan == 'premium' ? 'border-primary' : '' }}">
                                            <div class="card-header bg-light">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="plan" id="premiumPlan" value="premium" {{ $subscription->plan == 'premium' ? 'checked' : '' }} required>
                                                    <label class="form-check-label fw-bold" for="premiumPlan">
                                                        Premium Plan
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">₱4,999/month</h5>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i> Unlimited patient records</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 20 staff accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> 10 vet accounts</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Premium reporting</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Full inventory management</li>
                                                    <li><i class="fas fa-check text-success me-2"></i> Priority support</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    <option value="1" {{ $subscription->duration == '1' ? 'selected' : '' }}>1 Month</option>
                                    <option value="3" {{ $subscription->duration == '3' ? 'selected' : '' }}>3 Months (5% discount)</option>
                                    <option value="6" {{ $subscription->duration == '6' ? 'selected' : '' }}>6 Months (10% discount)</option>
                                    <option value="12" {{ $subscription->duration == '12' ? 'selected' : '' }}>12 Months (15% discount)</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Additional Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" id="autoRenew" value="1" {{ $subscription->auto_renew ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoRenew">
                                        Auto-renew subscription when it expires
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Payment Information</h4>
                                <p class="text-muted">Update payment details for your subscription</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="bank_transfer" {{ $subscription->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ $subscription->payment_method == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="gcash" {{ $subscription->payment_method == 'gcash' ? 'selected' : '' }}>GCash</option>
                                    <option value="maya" {{ $subscription->payment_method == 'maya' ? 'selected' : '' }}>Maya</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_reference" class="form-label">Payment Reference/Transaction ID</label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" value="{{ $subscription->payment_reference }}">
                                <small class="text-muted">Reference number or transaction ID from your payment</small>
                                @error('payment_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="payment_details" class="form-label">Additional Payment Details</label>
                                <textarea name="payment_details" id="payment_details" class="form-control @error('payment_details') is-invalid @enderror" rows="3">{{ $subscription->payment_details }}</textarea>
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
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ $subscription->notes }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                            <span id="totalAmount" class="fs-5 text-primary">₱{{ number_format($subscription->amount, 2) }}</span>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save me-1"></i> Update Subscription
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
        updateTotalAmount();
        
        // Update total amount when plan or duration changes
        document.querySelectorAll('input[name="plan"]').forEach(function(radio) {
            radio.addEventListener('change', updateTotalAmount);
        });
        
        document.getElementById('duration').addEventListener('change', updateTotalAmount);
        
        function updateTotalAmount() {
            const selectedPlan = document.querySelector('input[name="plan"]:checked').value;
            const duration = parseInt(document.getElementById('duration').value) || 0;
            
            let basePrice = 0;
            switch(selectedPlan) {
                case 'basic':
                    basePrice = 4999;
                    break;
                case 'standard':
                    basePrice = 4999;
                    break;
                case 'premium':
                    basePrice = 4999;
                    break;
            }
            
            let discount = 0;
            if (duration === 3) {
                discount = 0.05; // 5% discount
            } else if (duration === 6) {
                discount = 0.10; // 10% discount
            } else if (duration === 12) {
                discount = 0.15; // 15% discount
            }
            
            const totalAmount = basePrice * duration * (1 - discount);
            document.getElementById('totalAmount').textContent = '₱' + totalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
</script>
@endsection 