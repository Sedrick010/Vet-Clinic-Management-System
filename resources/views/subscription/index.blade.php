@extends('layouts.app')

@section('title', 'Subscriptions')

@section('page-name', 'Subscriptions')

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
            <div>
                <h4 class="mb-0">Subscription Management</h4>
                <div class="text-muted">View and manage your subscription plans</div>
            </div>
            <div>
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-info me-2">
                        <i class="fas fa-chart-bar me-1"></i> Subscription Requests
                    </a>
                @elseif(auth()->check() || session()->has('tenant_user'))
                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Request New Subscription
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-1"></i> Login to Subscribe
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(auth()->check() && !auth()->user()->hasRole('admin') || session()->has('tenant_user'))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Subscription Status
                </div>
                <div class="card-body">
                    @if($activeSubscription)
                        <div class="alert alert-success mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Active Subscription</h5>
                                    <p class="mb-0">
                                        You have an active <strong>{{ ucfirst($activeSubscription->plan) }} Plan</strong> subscription 
                                        which will expire on {{ \Carbon\Carbon::parse($activeSubscription->expired_at)->format('M d, Y') }}
                                        <span class="text-muted">({{ \Carbon\Carbon::parse($activeSubscription->expired_at)->diffForHumans() }})</span>.
                                    </p>
                                </div>
                                <a href="{{ route('subscription.show', $activeSubscription->id) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    @elseif($pendingSubscription)
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="alert-heading"><i class="fas fa-clock me-2"></i>Pending Request</h5>
                                    <p class="mb-0">
                                        You have a pending subscription request for the <strong>{{ ucfirst($pendingSubscription->plan) }} Plan</strong>. 
                                        We'll notify you once it's approved.
                                    </p>
                                </div>
                                <a href="{{ route('subscription.show', $pendingSubscription->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-eye me-1"></i> View Request
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>No Active Subscription</h5>
                                    <p class="mb-0">
                                        You don't have an active subscription. Request a new subscription to access all features.
                                    </p>
                                </div>
                                @if(auth()->check() || session()->has('tenant_user'))
                                <a href="{{ route('subscription.create') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Request Now
                                </a>
                                @else
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login to Subscribe
                                </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    @if(auth()->check() && auth()->user()->hasRole('admin'))
                        All Subscription Requests
                    @else
                        My Subscription History
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="subscriptionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    @if(auth()->check() && auth()->user()->hasRole('admin'))
                                    <th>User</th>
                                    @endif
                                    <th>Plan</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    @if(auth()->check() && auth()->user()->hasRole('admin'))
                                    <th>Expiry Date</th>
                                    @endif
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->id }}</td>
                                    @if(auth()->check() && auth()->user()->hasRole('admin'))
                                    <td>{{ $subscription->user->name }}</td>
                                    @endif
                                    <td>
                                        <span class="badge bg-{{ $subscription->plan == 'basic' ? 'secondary' : ($subscription->plan == 'standard' ? 'info' : 'primary') }} text-white">
                                            {{ ucfirst($subscription->plan) }}
                                        </span>
                                    </td>
                                    <td>{{ $subscription->duration }} month(s)</td>
                                    <td>₱{{ number_format($subscription->amount_paid, 2) }}</td>
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
                                    <td>{{ $subscription->created_at->format('M d, Y') }}</td>
                                    @if(auth()->check() && auth()->user()->hasRole('admin'))
                                    <td>
                                        @if($subscription->expired_at)
                                            {{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <a href="{{ route('subscription.show', $subscription->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if(auth()->check() && auth()->user()->hasRole('admin') && $subscription->status == 'pending')
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="window.location.href='{{ route('subscription.show', $subscription->id) }}'">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="window.location.href='{{ route('subscription.show', $subscription->id) }}'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ auth()->check() && auth()->user()->hasRole('admin') ? '9' : '7' }}" class="text-center">
                                        No subscription records found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->check() && auth()->user()->hasRole('admin'))
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Subscription Plan Distribution
                </div>
                <div class="card-body">
                    <canvas id="planDistributionChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Monthly Subscription Requests
                </div>
                <div class="card-body">
                    <canvas id="monthlyRequestsChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->check() && !auth()->user()->hasRole('admin') || session()->has('tenant_user'))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-th-large me-1"></i>
                    Available Subscription Plans
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4 h-100">
                                <div class="card-header text-center bg-light">
                                    <h5 class="my-0 fw-normal">Basic Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱4,999 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 100 patient records</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 2 staff accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 1 veterinarian account</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic email support</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> No priority support</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if(auth()->check() || session()->has('tenant_user'))
                                        <a href="{{ route('subscription.create', ['plan' => 'basic']) }}" class="btn btn-outline-primary w-100">
                                            Select Basic Plan
                                        </a>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                            Login to Subscribe
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4 border-primary h-100">
                                <div class="card-header text-center text-white bg-primary">
                                    <h5 class="my-0 fw-normal">Standard Plan</h5>
                                    <span class="badge bg-warning text-dark">Most Popular</span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱4,999 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 500 patient records</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 5 staff accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 3 veterinarian accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Priority email support</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Monthly usage reports</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if(auth()->check() || session()->has('tenant_user'))
                                        <a href="{{ route('subscription.create', ['plan' => 'standard']) }}" class="btn btn-primary w-100">
                                            Select Standard Plan
                                        </a>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                            Login to Subscribe
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4 h-100">
                                <div class="card-header text-center bg-dark text-white">
                                    <h5 class="my-0 fw-normal">Premium Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱4,999 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited patient records</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 10 staff accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 5 veterinarian accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 24/7 phone support</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Advanced analytics & reports</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if(auth()->check() || session()->has('tenant_user'))
                                        <a href="{{ route('subscription.create', ['plan' => 'premium']) }}" class="btn btn-outline-dark w-100">
                                            Select Premium Plan
                                        </a>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-dark w-100">
                                            Login to Subscribe
                                        </a>
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
    @endif
</div>
@endsection

@section('scripts')
@if(auth()->check() && auth()->user()->hasRole('admin'))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#subscriptionsTable').DataTable({
        order: [[0, 'desc']]
    });

    // Plan Distribution Chart
    const planCtx = document.getElementById('planDistributionChart');
    const planData = @json($planDistribution);
    
    new Chart(planCtx, {
        type: 'pie',
        data: {
            labels: ['Basic', 'Standard', 'Premium'],
            datasets: [{
                data: [
                    planData.basic || 0, 
                    planData.standard || 0, 
                    planData.premium || 0
                ],
                backgroundColor: [
                    '#6c757d',
                    '#0d6efd',
                    '#212529'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });

    // Monthly Subscription Requests Chart
    const monthlyCtx = document.getElementById('monthlyRequestsChart');
    const monthlyData = @json($monthlyRequests);
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: 'New Subscriptions',
                data: monthlyData.data,
                backgroundColor: '#0d6efd',
                borderColor: '#0d6efd',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@else
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#subscriptionsTable').DataTable({
        order: [[0, 'desc']]
    });
});
</script>
@endif
@endsection 