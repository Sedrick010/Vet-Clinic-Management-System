@extends('layouts.app')

@section('title', 'Subscriptions')

@section('page-name', 'Subscriptions')

@php
    $isSidebar = true;
@endphp

@section('styles')
<style>
    /* Statistics card styles */
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
        transition: all 0.3s ease;
        background-color: white;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .stats-card .card-body {
        padding: 25px 20px;
    }
    
    .stats-card .icon {
        height: 54px;
        width: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.5rem;
    }
    
    .stats-card h4 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1.2;
    }
    
    .stats-card .text-label {
        color: #6c757d;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }
    
    /* Chart card styles */
    .chart-card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        background: #fff;
        height: 100%;
    }
    
    .chart-card .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
        position: relative;
    }
    
    .chart-card .card-header h5 {
        font-size: 16px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }
    
    .chart-card .card-header i {
        margin-right: 8px;
        opacity: 0.7;
    }
    
    .chart-card .card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 270px;
        width: 100%;
        flex-grow: 1;
    }
    
    /* Dashboard container */
    .dashboard-container {
        background-color: #f8f9fc;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    /* Graph legend styling */
    .chart-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
    }
    
    .chart-legend-item {
        display: flex;
        align-items: center;
        font-size: 12px;
    }
    
    .chart-legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    /* Empty chart message */
    .empty-chart-message {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #6c757d;
    }
    
    /* Animation */
    .animate-card {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.5s ease forwards;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-delay-1 {
        animation-delay: 0.1s;
    }
    
    .animate-delay-2 {
        animation-delay: 0.2s;
    }
    
    .animate-delay-3 {
        animation-delay: 0.3s;
    }
    
    .animate-delay-4 {
        animation-delay: 0.4s;
    }
    
    /* Icon background colors */
    .bg-primary-light {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
    }
</style>
@endsection

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
            
            @if (session('upgrade_required'))
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading mb-1">PDF Export Feature Requires an Upgrade</h5>
                            <p class="mb-0">PDF export functionality is available exclusively on <strong>paid subscription plans</strong> (Basic, Standard, and Business). Please upgrade your subscription to access this premium feature.</p>
                        </div>
                        <div class="ms-auto">
                            <a href="#pricing-plans" class="btn btn-warning">
                                <i class="fas fa-arrow-circle-up me-1"></i> View Upgrade Options
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert bg-gradient-info text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-crown fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1"><strong>Welcome to the Subscription Management page!</strong></h6>
                        <p class="mb-0">All new clinic registrations start with the Free Plan by default. You can upgrade your plan at any time to access additional features.</p>
                    </div>
                </div>
            </div>
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

    @if(isset($activeSubscription) && $activeSubscription)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-success text-white">
                <div class="card-body d-flex align-items-center p-3">
                    <i class="fas fa-crown fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0">Your Current Plan: <strong>{{ ucfirst($activeSubscription->plan === 'premium' ? 'Business' : $activeSubscription->plan) }} Plan</strong></h5>
                        <p class="mb-0">Expires: {{ \Carbon\Carbon::parse($activeSubscription->expired_at)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($activeSubscription->expired_at)->diffForHumans() }})</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('subscription.show', $activeSubscription->id) }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif(session()->has('current_clinic_id') || (auth()->check() && auth()->user()->clinic))
    @php
        if (session()->has('current_clinic_id')) {
            $clinicId = session('current_clinic_id');
            $clinic = \App\Models\Clinic::find($clinicId);
        } else {
            $clinic = auth()->user()->clinic;
        }
    @endphp
    @if($clinic)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-secondary text-white">
                <div class="card-body d-flex align-items-center p-3">
                    <i class="fas fa-ticket-alt fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0">Your Current Plan: <strong>{{ ucfirst($clinic->subscription_plan) }} Plan</strong></h5>
                        <p class="mb-0">{{ $clinic->is_subscription_active ? 'Active' : 'Inactive' }} - Basic features available</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('subscription.create') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-level-up-alt me-1"></i> Upgrade Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    @if(isset($pendingSubscription) && $pendingSubscription)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-warning">
                <div class="card-body d-flex align-items-center p-3">
                    <i class="fas fa-hourglass-half fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0">Pending Upgrade Request: <strong>{{ ucfirst($pendingSubscription->plan === 'premium' ? 'Business' : $pendingSubscription->plan) }} Plan</strong></h5>
                        <p class="mb-0">Submitted: {{ \Carbon\Carbon::parse($pendingSubscription->created_at)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($pendingSubscription->created_at)->diffForHumans() }})</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('subscription.show', $pendingSubscription->id) }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </a>
                        <a href="{{ route('subscription.cancelRequest', $pendingSubscription->id) }}" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Are you sure you want to cancel this subscription request?');">
                            <i class="fas fa-times me-1"></i> Cancel Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->check() && auth()->user()->hasRole('admin'))
    <div class="dashboard-container">
        <!-- Simple Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <h5><i class="fas fa-check-circle text-success me-2"></i>Active</h5>
                                <h3>{{ $stats['totalActive'] }}</h3>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5><i class="fas fa-clock text-warning me-2"></i>Pending</h5>
                                <h3>{{ $stats['totalPending'] }}</h3>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5><i class="fas fa-dollar-sign text-primary me-2"></i>Revenue</h5>
                                <h3>₱{{ number_format($stats['totalRevenue'], 2) }}</h3>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5><i class="fas fa-chart-line text-info me-2"></i>Avg. Value</h5>
                                <h3>₱{{ number_format($stats['averageRevenue'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clinics with Ongoing Subscriptions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Clinics with Ongoing Subscriptions</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $activeSubscriptions = \App\Models\SubscriptionRequest::where('status', 'approved')
                                ->where('expired_at', '>', now())
                                ->with(['clinic'])
                                ->get();
                        @endphp

                        @if($activeSubscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Clinic</th>
                                        <th>Plan</th>
                                        <th>Start Date</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            @if($subscription->clinic)
                                                {{ $subscription->clinic->name }}
                                            @elseif($subscription->guest_clinic_name)
                                                {{ $subscription->guest_clinic_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $subscription->plan == 'free' ? 'secondary' : ($subscription->plan == 'basic' ? 'info' : ($subscription->plan == 'standard' ? 'primary' : 'dark')) }}">
                                                {{ ucfirst($subscription->plan) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($subscription->approved_at)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($subscription->expired_at), false);
                                            @endphp
                                            
                                            @if($daysLeft > 30)
                                                <span class="badge bg-success">Active</span>
                                            @elseif($daysLeft > 0)
                                                <span class="badge bg-warning">Expiring Soon ({{ $daysLeft }} days)</span>
                                            @else
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#viewSubscriptionModal{{ $subscription->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#extendModal{{ $subscription->id }}">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                                @if(auth()->check() && auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#removeSubscriptionModal{{ $subscription->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                            
                                            <!-- Remove Subscription Modal -->
                                            @if(auth()->check() && auth()->user()->role === 'admin')
                                            <div class="modal fade" id="removeSubscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="removeSubscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="removeSubscriptionModalLabel{{ $subscription->id }}">Remove Subscription</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to remove this subscription?</p>
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                This action cannot be undone. If this is the clinic's current active subscription, it will be reverted to the free plan or another active subscription if available.
                                                            </div>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th>Clinic:</th>
                                                                    <td>
                                                                        @if($subscription->clinic)
                                                                            {{ $subscription->clinic->name }}
                                                                        @elseif($subscription->guest_clinic_name)
                                                                            {{ $subscription->guest_clinic_name }}
                                                                        @else
                                                                            <span class="text-muted">N/A</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Plan:</th>
                                                                    <td>{{ ucfirst($subscription->plan) }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Expiry:</th>
                                                                    <td>{{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('admin.subscriptions.remove', $subscription->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-danger">Remove Subscription</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <!-- Display nothing if there are no active subscriptions -->
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Subscription Requests -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Current Subscription Requests</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $pendingRequests = \App\Models\SubscriptionRequest::where('status', 'pending')
                                ->with(['clinic'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp

                        @if($pendingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Clinic</th>
                                        <th>Plan</th>
                                        <th>Duration</th>
                                        <th>Amount</th>
                                        <th>Request Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            @if($request->clinic_id && $request->clinic)
                                                {{ $request->clinic->name }}
                                            @elseif($request->guest_clinic_name)
                                                {{ $request->guest_clinic_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->plan == 'free' ? 'secondary' : ($request->plan == 'basic' ? 'info' : ($request->plan == 'standard' ? 'primary' : 'dark')) }}">
                                                {{ ucfirst($request->plan) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->duration }} month(s)</td>
                                        <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $request->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No pending subscription requests at this time.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->check() && !auth()->user()->hasRole('admin') || session()->has('tenant_user'))
    <div class="row mb-4" id="pricing-plans">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-th-large me-1"></i>
                    Available Subscription Plans
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card mb-4 h-100 {{ isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'free' ? 'border-2 border-success position-relative' : ((session()->has('current_clinic_id') || (auth()->check() && auth()->user()->clinic)) && !isset($activeSubscription) ? 'border-2 border-success position-relative' : '') }}">
                                @if((isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'free') || 
                                    ((session()->has('current_clinic_id') || (auth()->check() && auth()->user()->clinic)) && !isset($activeSubscription)))
                                <div class="position-absolute" style="top: -10px; right: -10px; z-index: 1;">
                                    <span class="badge bg-success shadow" style="font-size: 0.8rem; padding: 5px 10px;">
                                        <i class="fas fa-check-circle me-1"></i> CURRENT PLAN
                                    </span>
                                </div>
                                @endif
                                <div class="card-header text-center bg-light">
                                    <h5 class="my-0 fw-normal">Free Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱0 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        @if(isset($planFeatures) && isset($planFeatures['free']))
                                            @foreach($planFeatures['free'] as $feature)
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i> {{ $feature }}
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 20 Appointments/month</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic Clinic Setup (Name)</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 1 admin account</li>
                                            <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> No Premium Reports</li>
                                        @endif
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if((auth()->check() || session()->has('tenant_user')) && (!isset($activeSubscription) || !$activeSubscription || $activeSubscription->plan !== 'free'))
                                        <a href="{{ route('subscription.create', ['plan' => 'free']) }}" class="btn btn-outline-primary w-100">
                                            Select Free Plan
                                        </a>
                                        @elseif(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'free')
                                        <button class="btn btn-success w-100" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Current Plan
                                        </button>
                                        @elseif((session()->has('current_clinic_id') || (auth()->check() && auth()->user()->clinic)) && !isset($activeSubscription))
                                        <button class="btn btn-success w-100" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Current Plan
                                        </button>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                            Login to Subscribe
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card mb-4 h-100 {{ isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'basic' ? 'border-2 border-success position-relative' : '' }}">
                                @if(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'basic')
                                <div class="position-absolute" style="top: -10px; right: -10px; z-index: 1;">
                                    <span class="badge bg-success shadow" style="font-size: 0.8rem; padding: 5px 10px;">
                                        <i class="fas fa-check-circle me-1"></i> CURRENT PLAN
                                    </span>
                                </div>
                                @endif
                                <div class="card-header text-center bg-light">
                                    <h5 class="my-0 fw-normal">Basic Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱599 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        @if(isset($planFeatures) && isset($planFeatures['basic']))
                                            @foreach($planFeatures['basic'] as $feature)
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i> {{ $feature }}
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 100 appointments/month</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 200 products</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic Customization (Logo, 2 theme colors)</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 2 Admin/Staff Accounts</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Standard Reports (Appointment & Inventory Summary)</li>
                                        @endif
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if((auth()->check() || session()->has('tenant_user')) && (!isset($activeSubscription) || !$activeSubscription || $activeSubscription->plan !== 'basic'))
                                        <a href="{{ route('subscription.create', ['plan' => 'basic']) }}" class="btn btn-outline-primary w-100">
                                            Select Basic Plan
                                        </a>
                                        @elseif(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'basic')
                                        <button class="btn btn-success w-100" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Current Plan
                                        </button>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                            Login to Subscribe
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card mb-4 h-100 {{ isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'standard' ? 'border-2 border-success position-relative' : 'border-primary' }}">
                                @if(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'standard')
                                <div class="position-absolute" style="top: -10px; right: -10px; z-index: 1;">
                                    <span class="badge bg-success shadow" style="font-size: 0.8rem; padding: 5px 10px;">
                                        <i class="fas fa-check-circle me-1"></i> CURRENT PLAN
                                    </span>
                                </div>
                                @endif
                                <div class="card-header text-center text-white bg-primary">
                                    <h5 class="my-0 fw-normal">Standard Plan</h5>
                                    <span class="badge bg-warning text-dark">Most Popular</span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱1,599 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        @if(isset($planFeatures) && isset($planFeatures['premium']))
                                            @foreach($planFeatures['premium'] as $feature)
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i> {{ $feature }}
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 500 appointments/month</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 500 products</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full clinic customization (logo, banners, multiple theme colors)</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 5 staff accounts</li>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full reports and basic analytics</li>
                                        @endif
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if((auth()->check() || session()->has('tenant_user')) && (!isset($activeSubscription) || !$activeSubscription || $activeSubscription->plan !== 'standard'))
                                        <a href="{{ route('subscription.create', ['plan' => 'standard']) }}" class="btn btn-primary w-100">
                                            Select Standard Plan
                                        </a>
                                        @elseif(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan === 'standard')
                                        <button class="btn btn-success w-100" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Current Plan
                                        </button>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                            Login to Subscribe
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card mb-4 h-100 {{ isset($activeSubscription) && $activeSubscription && ($activeSubscription->plan === 'business' || $activeSubscription->plan === 'premium') ? 'border-2 border-success position-relative' : '' }}">
                                @if(isset($activeSubscription) && $activeSubscription && ($activeSubscription->plan === 'business' || $activeSubscription->plan === 'premium'))
                                <div class="position-absolute" style="top: -10px; right: -10px; z-index: 1;">
                                    <span class="badge bg-success shadow" style="font-size: 0.8rem; padding: 5px 10px;">
                                        <i class="fas fa-check-circle me-1"></i> CURRENT PLAN
                                    </span>
                                </div>
                                @endif
                                <div class="card-header text-center bg-dark text-white">
                                    <h5 class="my-0 fw-normal">Business Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱3,599 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Appointments</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited inventory items</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Advanced customization</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Staff Account</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Complete analytics and custom reporting</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Patient Portal (Client can view their pet's info, appointment history)</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if((auth()->check() || session()->has('tenant_user')) && (!isset($activeSubscription) || !$activeSubscription || ($activeSubscription->plan !== 'business' && $activeSubscription->plan !== 'premium')))
                                        <a href="{{ route('subscription.create', ['plan' => 'business']) }}" class="btn btn-outline-dark w-100">
                                            Select Business Plan
                                        </a>
                                        @elseif(isset($activeSubscription) && $activeSubscription && ($activeSubscription->plan === 'business' || $activeSubscription->plan === 'premium'))
                                        <button class="btn btn-success w-100" disabled>
                                            <i class="fas fa-check-circle me-1"></i> Current Plan
                                        </button>
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
<script>
    $(document).ready(function () {
        $('.datatable').DataTable({
            responsive: true,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10,
        });
    });
</script>
@endsection

<!-- Approval and Rejection Modals for Subscription Requests -->
@if(auth()->check() && auth()->user()->hasRole('admin'))
    @php
        $pendingRequests = \App\Models\SubscriptionRequest::where('status', 'pending')->get();
    @endphp
    
    @foreach($pendingRequests as $request)
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">Approve Subscription Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('subscription.approve', $request->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>You are approving the subscription request for 
                                <strong>
                                    @if($request->clinic_id && $request->clinic)
                                        {{ $request->clinic->name }}
                                    @elseif($request->guest_clinic_name)
                                        {{ $request->guest_clinic_name }}
                                    @else
                                        Unknown Clinic
                                    @endif
                                </strong>
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Plan:</strong> {{ ucfirst($request->plan) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Duration:</strong> {{ $request->duration }} month(s)
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Amount:</strong> ₱{{ number_format($request->amount_paid, 2) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expiration_date{{ $request->id }}" class="form-label">Expiration Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="expiration_date{{ $request->id }}" name="expiration_date" 
                                    value="{{ \Carbon\Carbon::now()->addMonths($request->duration)->format('Y-m-d') }}" required>
                                <small class="text-muted">Default is {{ $request->duration }} month(s) from today</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_notes{{ $request->id }}" class="form-label">Admin Notes (Optional)</label>
                                <textarea class="form-control" id="admin_notes{{ $request->id }}" name="admin_notes" rows="3" placeholder="Optional notes about this approval"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Confirm Approval</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="rejectModalLabel{{ $request->id }}">Reject Subscription Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('subscription.reject', $request->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>You are rejecting the subscription request for 
                                <strong>
                                    @if($request->clinic_id && $request->clinic)
                                        {{ $request->clinic->name }}
                                    @elseif($request->guest_clinic_name)
                                        {{ $request->guest_clinic_name }}
                                    @else
                                        Unknown Clinic
                                    @endif
                                </strong>
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Plan:</strong> {{ ucfirst($request->plan) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Duration:</strong> {{ $request->duration }} month(s)
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Amount:</strong> ₱{{ number_format($request->amount_paid, 2) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="rejection_reason{{ $request->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="rejection_reason{{ $request->id }}" name="rejection_reason" rows="3" placeholder="Provide reason for rejection" required></textarea>
                                <small class="text-muted">This reason will be visible to the clinic.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_notes_reject{{ $request->id }}" class="form-label">Admin Notes (Optional)</label>
                                <textarea class="form-control" id="admin_notes_reject{{ $request->id }}" name="admin_notes" rows="2" placeholder="Optional internal notes (not visible to clinic)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- View Subscription Request Details Modal -->
        <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $request->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="viewModalLabel{{ $request->id }}">Subscription Request Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Clinic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Clinic Name:</th>
                                        <td>
                                            @if($request->clinic_id && $request->clinic)
                                                {{ $request->clinic->name }}
                                            @elseif($request->guest_clinic_name)
                                                {{ $request->guest_clinic_name }}
                                            @else
                                                Unknown Clinic
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contact Email:</th>
                                        <td>
                                            @if($request->clinic_id && $request->clinic)
                                                {{ $request->clinic->email }}
                                            @elseif($request->guest_email)
                                                {{ $request->guest_email }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contact Phone:</th>
                                        <td>
                                            @if($request->clinic_id && $request->clinic)
                                                {{ $request->clinic->phone }}
                                            @elseif($request->guest_phone)
                                                {{ $request->guest_phone }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Request Date:</th>
                                        <td>{{ $request->created_at->format('M d, Y g:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Subscription Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Plan:</th>
                                        <td>
                                            <span class="badge bg-{{ $request->plan == 'free' ? 'secondary' : ($request->plan == 'basic' ? 'info' : ($request->plan == 'standard' ? 'primary' : 'dark')) }}">
                                                {{ ucfirst($request->plan) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td>{{ $request->duration }} month(s)</td>
                                    </tr>
                                    <tr>
                                        <th>Amount:</th>
                                        <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Auto-Renew:</th>
                                        <td>{{ $request->auto_renew ? 'Yes' : 'No' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Payment Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Method:</th>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Reference:</th>
                                        <td>{{ $request->payment_reference ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Additional Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Notes:</th>
                                        <td>{{ $request->notes ?: 'No notes provided' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Details:</th>
                                        <td>{{ $request->payment_details ?: 'No details provided' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">Approve Request</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">Reject Request</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    
    <!-- Extension Modals for Active Subscriptions -->
    @php
        $activeSubscriptions = \App\Models\SubscriptionRequest::where('status', 'approved')
            ->where('expired_at', '>', now())
            ->get();
    @endphp
    
    @foreach($activeSubscriptions as $subscription)
        <!-- Extend Subscription Modal -->
        <div class="modal fade" id="extendModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="extendModalLabel{{ $subscription->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="extendModalLabel{{ $subscription->id }}">Extend Subscription</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('subscription.extend', $subscription->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>You are extending the subscription for 
                                <strong>
                                    @if($subscription->clinic_id && $subscription->clinic)
                                        {{ $subscription->clinic->name }}
                                    @elseif($subscription->guest_clinic_name)
                                        {{ $subscription->guest_clinic_name }}
                                    @else
                                        Unknown Clinic
                                    @endif
                                </strong>
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Current Plan:</strong> {{ ucfirst($subscription->plan) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Current Expiry:</strong> {{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="extension_months{{ $subscription->id }}" class="form-label">Extension Period <span class="text-danger">*</span></label>
                                <select class="form-select" id="extension_months{{ $subscription->id }}" name="extension_months" required>
                                    <option value="1">1 month</option>
                                    <option value="3">3 months</option>
                                    <option value="6">6 months</option>
                                    <option value="12">12 months</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="extension_reason{{ $subscription->id }}" class="form-label">Reason for Extension <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="extension_reason{{ $subscription->id }}" name="extension_reason" rows="3" placeholder="Provide reason for extension" required></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="notify_client{{ $subscription->id }}" name="notify_client" checked>
                                <label class="form-check-label" for="notify_client{{ $subscription->id }}">
                                    Notify clinic about extension
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Confirm Extension</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- View Subscription Details Modal -->
        <div class="modal fade" id="viewSubscriptionModal{{ $subscription->id }}" tabindex="-1" aria-labelledby="viewSubscriptionModalLabel{{ $subscription->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="viewSubscriptionModalLabel{{ $subscription->id }}">Active Subscription Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Clinic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Clinic Name:</th>
                                        <td>
                                            @if($subscription->clinic_id && $subscription->clinic)
                                                {{ $subscription->clinic->name }}
                                            @elseif($subscription->guest_clinic_name)
                                                {{ $subscription->guest_clinic_name }}
                                            @else
                                                Unknown Clinic
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contact Email:</th>
                                        <td>
                                            @if($subscription->clinic_id && $subscription->clinic)
                                                {{ $subscription->clinic->email }}
                                            @elseif($subscription->guest_email)
                                                {{ $subscription->guest_email }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contact Phone:</th>
                                        <td>
                                            @if($subscription->clinic_id && $subscription->clinic)
                                                {{ $subscription->clinic->phone }}
                                            @elseif($subscription->guest_phone)
                                                {{ $subscription->guest_phone }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Subscription Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Plan:</th>
                                        <td>
                                            <span class="badge bg-{{ $subscription->plan == 'free' ? 'secondary' : ($subscription->plan == 'basic' ? 'info' : ($subscription->plan == 'standard' ? 'primary' : 'dark')) }}">
                                                {{ ucfirst($subscription->plan) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Approved Date:</th>
                                        <td>{{ $subscription->approved_at ? \Carbon\Carbon::parse($subscription->approved_at)->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expiry Date:</th>
                                        <td>{{ \Carbon\Carbon::parse($subscription->expired_at)->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @php
                                                $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($subscription->expired_at), false);
                                            @endphp
                                            
                                            @if($daysLeft > 30)
                                                <span class="badge bg-success">Active ({{ $daysLeft }} days left)</span>
                                            @elseif($daysLeft > 0)
                                                <span class="badge bg-warning">Expiring Soon ({{ $daysLeft }} days left)</span>
                                            @else
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Auto-Renew:</th>
                                        <td>{{ $subscription->auto_renew ? 'Yes' : 'No' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Payment Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Method:</th>
                                        <td>{{ ucfirst(str_replace('_', ' ', $subscription->payment_method)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount Paid:</th>
                                        <td>₱{{ number_format($subscription->amount_paid, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Reference:</th>
                                        <td>{{ $subscription->payment_reference ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Admin Notes</h6>
                                <div class="p-3 bg-light rounded">
                                    {{ $subscription->admin_notes ?: 'No admin notes available' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="{{ route('admin.clinics.subscription.change.form', $subscription->clinic_id) }}" class="btn btn-info">
                            <i class="fas fa-exchange-alt me-1"></i> Change Subscription
                        </a>
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#extendModal{{ $subscription->id }}">Extend Subscription</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif 