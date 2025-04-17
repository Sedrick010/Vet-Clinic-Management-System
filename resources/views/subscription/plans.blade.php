@extends('layouts.app')

@section('title', 'Subscription Plans')
@section('page_name', 'Subscription Plans')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Choose Your Plan</h6>
                    <p class="text-sm mb-0">Select the plan that best suits your clinic's needs</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="row">
                        <!-- Basic Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h5 class="text-center">Basic Plan</h5>
                                    <div class="text-center">
                                        <h2 class="mb-0">$29</h2>
                                        <span class="text-sm text-secondary">/month</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Up to 100 patients</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic appointment scheduling</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Patient records management</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Email support</li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <button class="btn btn-primary subscribe-btn" data-plan="BASIC PLAN" data-price="29">
                                        Subscribe Now
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Standard Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h5 class="text-center">Standard Plan</h5>
                                    <div class="text-center">
                                        <h2 class="mb-0">$49</h2>
                                        <span class="text-sm text-secondary">/month</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Up to 500 patients</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced appointment scheduling</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Patient records management</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority email support</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic reporting</li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <button class="btn btn-primary subscribe-btn" data-plan="STANDARD PLAN" data-price="49">
                                        Subscribe Now
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Premium Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h5 class="text-center">Premium Plan</h5>
                                    <div class="text-center">
                                        <h2 class="mb-0">$99</h2>
                                        <span class="text-sm text-secondary">/month</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited patients</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced appointment scheduling</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Patient records management</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>24/7 priority support</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced reporting</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Custom branding</li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <button class="btn btn-primary subscribe-btn" data-plan="PREMIUM PLAN" data-price="99">
                                        Subscribe Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <strong>Test Mode:</strong> This is a test payment form.
                    <ul class="mb-0">
                        <li>Use any 16-digit card number starting with "1111" for successful payment</li>
                        <li>Any other card number will simulate a failed payment</li>
                        <li>Use any future date for expiry (MM/YY)</li>
                        <li>Use any 3 digits for CVV</li>
                    </ul>
                </div>
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="plan_name" id="plan_name">
                    <input type="hidden" name="amount" id="amount">
                    
                    <div class="mb-3">
                        <label for="card_name" class="form-label">Name on Card</label>
                        <input type="text" class="form-control" id="card_name" name="card_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="card_number" class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1111 XXXX XXXX XXXX" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="processPayment">Process Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing subscription handlers...');
    
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded!');
        return;
    }

    // Initialize payment modal
    const paymentModal = document.getElementById('paymentModal');
    if (!paymentModal) {
        console.error('Payment modal element not found!');
        return;
    }

    const modal = new bootstrap.Modal(paymentModal);
    console.log('Modal initialized');

    // Add event listeners to all subscribe buttons
    const subscribeButtons = document.querySelectorAll('.subscribe-btn');
    console.log('Found subscribe buttons:', subscribeButtons.length);

    subscribeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const planName = this.dataset.plan;
            const amount = this.dataset.price;
            
            console.log('Selected plan:', planName, 'Amount:', amount);
            
            // Reset form before showing
            document.getElementById('paymentForm').reset();
            document.getElementById('plan_name').value = planName;
            document.getElementById('amount').value = amount;
            
            modal.show();
        });
    });

    // Process payment
    const processPaymentBtn = document.getElementById('processPayment');
    if (processPaymentBtn) {
        processPaymentBtn.addEventListener('click', async function() {
            // Show loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Get form data
            const formData = {
                plan_name: document.getElementById('plan_name').value,
                amount: document.getElementById('amount').value,
                card_name: document.getElementById('card_name').value,
                card_number: document.getElementById('card_number').value.replace(/\s/g, ''),
                expiry_date: document.getElementById('expiry_date').value,
                cvv: document.getElementById('cvv').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            // Send request
            const response = await fetch('{{ route("subscription.subscribe") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData._token
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            // Hide modal and show success message
            modal.hide();
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        });
    }

    // Format card number input
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            // Remove any non-digits
            let value = e.target.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            if (value.length > 16) {
                value = value.slice(0, 16);
            }
            
            // Add spaces for readability, but store without spaces
            const parts = [];
            for (let i = 0; i < value.length; i += 4) {
                parts.push(value.slice(i, i + 4));
            }
            e.target.value = parts.join(' ');
            
            // Store the raw number (without spaces) as a data attribute
            e.target.dataset.number = value;
        });
    }

    // Format expiry date input
    const expiryDateInput = document.getElementById('expiry_date');
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) value = value.slice(0, 4);
            if (value.length >= 2) {
                value = value.slice(0,2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });
    }

    // Format CVV input
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) value = value.slice(0, 3);
            e.target.value = value;
        });
    }
});
</script>
@endpush 