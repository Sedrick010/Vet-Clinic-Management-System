@extends('layouts.app')

@section('title', 'Subscription Details')

@section('page-name', 'Subscription Details')

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

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-1"></i>
                        Subscription Details
                    </div>
                    <a href="{{ route('subscription.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Subscription Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0" width="140">Subscription ID:</th>
                                    <td><span class="badge bg-secondary">{{ $subscription->id }}</span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Status:</th>
                                    <td>
                                        @if($subscription->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($subscription->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($subscription->status == 'expired')
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($subscription->status == 'cancelled')
                                            <span class="badge bg-secondary">Cancelled</span>
                                        @elseif($subscription->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Plan:</th>
                                    <td>
                                        @if($subscription->plan == 'free')
                                            <span class="fw-bold">Free Plan</span>
                                        @elseif($subscription->plan == 'basic')
                                            <span class="fw-bold">Basic Plan</span>
                                        @elseif($subscription->plan == 'standard')
                                            <span class="fw-bold">Standard Plan</span>
                                        @elseif($subscription->plan == 'business' || $subscription->plan == 'premium')
                                            <span class="fw-bold">Business Plan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Duration:</th>
                                    <td>{{ $subscription->duration }} month(s)</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Amount Paid:</th>
                                    <td>₱{{ number_format($subscription->amount_paid, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Timeline</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0" width="140">Requested on:</th>
                                    <td>{{ $subscription->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if($subscription->approved_at)
                                <tr>
                                    <th class="ps-0">Approved on:</th>
                                    <td>{{ \Carbon\Carbon::parse($subscription->approved_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endif
                                @if($subscription->rejected_at)
                                <tr>
                                    <th class="ps-0">Rejected on:</th>
                                    <td>{{ \Carbon\Carbon::parse($subscription->rejected_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endif
                                @if($subscription->expired_at)
                                <tr>
                                    <th class="ps-0">Expires on:</th>
                                    <td>
                                        {{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}
                                        @if($subscription->status == 'active')
                                            <span class="text-muted">({{ \Carbon\Carbon::parse($subscription->expired_at)->diffForHumans() }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($subscription->cancelled_at)
                                <tr>
                                    <th class="ps-0">Cancelled on:</th>
                                    <td>{{ \Carbon\Carbon::parse($subscription->cancelled_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($subscription->notes)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Additional Notes</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $subscription->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(auth()->check() && auth()->user()->hasRole('admin') && $subscription->rejection_reason)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Rejection Reason</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $subscription->rejection_reason }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Payment Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th class="ps-0" width="140">Payment Method:</th>
                                    <td>
                                        @if($subscription->payment_method == 'bank_transfer')
                                            Bank Transfer
                                        @elseif($subscription->payment_method == 'gcash')
                                            GCash
                                        @elseif($subscription->payment_method == 'credit_card')
                                            Credit Card
                                        @else
                                            {{ ucfirst($subscription->payment_method) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            
                            <h6 class="mt-3">Payment Proof</h6>
                            <div class="payment-proof-container text-center p-3 border rounded">
                                <img src="{{ asset('storage/' . $subscription->payment_proof) }}" class="img-fluid payment-proof" style="max-height: 400px;" alt="Payment Proof">
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $subscription->payment_proof) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i> View Full Size
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-1"></i>
                    Actions
                </div>
                <div class="card-body">
                    @if(auth()->check())
                        @if(auth()->user()->hasRole('admin'))
                            @if($subscription->status == 'pending')
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                        <i class="fas fa-check me-1"></i> Approve Subscription
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="fas fa-times me-1"></i> Reject Subscription
                                    </button>
                                    <a href="{{ route('subscription.edit', $subscription->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-1"></i> Edit Subscription
                                    </a>
                                </div>
                            @elseif($subscription->status == 'active')
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#extendModal">
                                        <i class="fas fa-calendar-plus me-1"></i> Extend Subscription
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-ban me-1"></i> Cancel Subscription
                                    </button>
                                </div>
                            @endif
                        @else
                            @if($subscription->status == 'pending')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i> Your subscription request is currently being reviewed.
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                                        <i class="fas fa-times me-1"></i> Cancel Request
                                    </button>
                                    <a href="{{ route('subscription.edit', $subscription->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-1"></i> Edit Request
                                    </a>
                                </div>
                            @elseif($subscription->status == 'active')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-1"></i> Your subscription is active and will expire on {{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}.
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">
                                        <i class="fas fa-sync me-1"></i> Renew Subscription
                                    </a>
                                </div>
                            @elseif($subscription->status == 'expired')
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-circle me-1"></i> Your subscription has expired.
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">
                                        <i class="fas fa-sync me-1"></i> Renew Subscription
                                    </a>
                                </div>
                            @elseif($subscription->status == 'cancelled')
                                <div class="alert alert-secondary">
                                    <i class="fas fa-ban me-1"></i> This subscription was cancelled.
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> New Subscription
                                    </a>
                                </div>
                            @elseif($subscription->status == 'rejected')
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-1"></i> Your subscription request was rejected.
                                    @if($subscription->rejection_reason)
                                    <hr>
                                    <strong>Reason:</strong> {{ $subscription->rejection_reason }}
                                    @endif
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> New Request
                                    </a>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i> Please log in to manage subscriptions.
                        </div>
                    @endif

                    @if($subscription->status == 'active')
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Features Available</h6>
                        <ul class="list-group">
                            @if($subscription->plan == 'basic')
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Patient Records
                                    <span class="badge bg-primary rounded-pill">100</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Staff Accounts
                                    <span class="badge bg-primary rounded-pill">2</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Vet Accounts
                                    <span class="badge bg-primary rounded-pill">1</span>
                                </li>
                            @elseif($subscription->plan == 'standard')
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Patient Records
                                    <span class="badge bg-primary rounded-pill">500</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Staff Accounts
                                    <span class="badge bg-primary rounded-pill">5</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Vet Accounts
                                    <span class="badge bg-primary rounded-pill">3</span>
                                </li>
                            @elseif($subscription->plan == 'business' || $subscription->plan == 'premium')
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Patient Records
                                    <span class="badge bg-primary rounded-pill">Unlimited</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Staff Accounts
                                    <span class="badge bg-primary rounded-pill">10</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Vet Accounts
                                    <span class="badge bg-primary rounded-pill">5</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    User Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th class="ps-0">User:</th>
                            <td>
                                @if($subscription->user)
                                    {{ $subscription->user->name }}
                                @else
                                    {{ $subscription->guest_clinic_name ?? 'Guest' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Email:</th>
                            <td>
                                @if($subscription->user)
                                    {{ $subscription->user->email }}
                                @else
                                    {{ $subscription->guest_email ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Clinic:</th>
                            <td>
                                @if($subscription->user && $subscription->user->clinic)
                                    {{ $subscription->user->clinic->name }}
                                @else
                                    {{ $subscription->guest_clinic_name ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list-check me-1"></i>
                    Subscription Features
                </div>
                <div class="card-body">
                    // ... existing code ...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Modals -->
@if(auth()->check() && auth()->user()->hasRole('admin'))
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('subscription.approve', $subscription->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve this subscription request?</p>
                        <div class="mb-3">
                            <label for="expired_at" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expired_at" name="expired_at" required>
                            <small class="form-text text-muted">Set the expiry date for this subscription</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('subscription.reject', $subscription->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                            <small class="form-text text-muted">Please provide a reason for rejecting this subscription request</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('subscription.cancel', $subscription->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Cancel Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this active subscription?</p>
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Extend Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1" aria-labelledby="extendModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('subscription.extend', $subscription->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="extendModalLabel">Extend Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="extension_months" class="form-label">Extension Period (months)</label>
                            <input type="number" class="form-control" id="extension_months" name="extension_months" min="1" max="12" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="extension_reason" class="form-label">Reason for Extension</label>
                            <textarea class="form-control" id="extension_reason" name="extension_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning">Extend Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- User Modals -->
@if(auth()->check() && !auth()->user()->hasRole('admin'))
    <!-- Cancel Request Modal -->
    <div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('subscription.cancel-request', $subscription->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelRequestModalLabel">Cancel Subscription Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this subscription request?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
    // Default to 30 days from today for expiry date
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('expired_at')) {
            const today = new Date();
            // Get duration from form if available
            const durationInput = document.getElementById('duration');
            let months = 1; // Default to 1 month
            
            if(durationInput) {
                months = parseInt(durationInput.value) || 1;
            }
            
            // Add months to current date
            today.setMonth(today.getMonth() + months);
            
            // Format date as YYYY-MM-DD
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;
            
            document.getElementById('expired_at').value = formattedDate;
        }
        
        // Update expiry date when duration changes
        const durationInput = document.getElementById('duration');
        if(durationInput) {
            durationInput.addEventListener('change', function() {
                const today = new Date();
                const months = parseInt(this.value) || 1;
                
                // Add months to current date
                today.setMonth(today.getMonth() + months);
                
                // Format date as YYYY-MM-DD
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                const formattedDate = `${yyyy}-${mm}-${dd}`;
                
                document.getElementById('expiry_date').value = formattedDate;
            });
        }
    });
</script>
@endsection 