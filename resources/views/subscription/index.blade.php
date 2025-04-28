@extends('layouts.app')

@section('title', 'Subscriptions')

@section('page-name', 'Subscriptions')

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

    @if(auth()->check() && auth()->user()->hasRole('admin'))
    <div class="dashboard-container">
        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card animate-card animate-delay-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-label">ACTIVE SUBSCRIPTIONS</p>
                                <h4>{{ $stats['totalActive'] }}</h4>
                            </div>
                            <div class="icon bg-primary-light">
                                <i class="fas fa-check-circle text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card animate-card animate-delay-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-label">PENDING REQUESTS</p>
                                <h4>{{ $stats['totalPending'] }}</h4>
                            </div>
                            <div class="icon bg-warning-light">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card animate-card animate-delay-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-label">TOTAL REVENUE</p>
                                <h4>₱{{ number_format($stats['totalRevenue'], 2) }}</h4>
                            </div>
                            <div class="icon bg-success-light">
                                <i class="fas fa-dollar-sign text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card animate-card animate-delay-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-label">AVG. SUBSCRIPTION VALUE</p>
                                <h4>₱{{ number_format($stats['averageRevenue'], 2) }}</h4>
                            </div>
                            <div class="icon bg-info-light">
                                <i class="fas fa-chart-line text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="chart-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-chart-pie text-primary"></i>
                        <h5>Subscription Plan Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="planDistributionChart"></canvas>
                            @if(array_sum([$planDistribution['free'], $planDistribution['basic'], $planDistribution['standard'], $planDistribution['premium']]) == 0)
                            <div class="empty-chart-message">
                                <p>No active subscription plans</p>
                            </div>
                            @endif
                        </div>
                        <div class="chart-legend">
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background-color: #6c757d;"></div>
                                <span>Free</span>
                            </div>
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background-color: #17a2b8;"></div>
                                <span>Basic</span>
                            </div>
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background-color: #0d6efd;"></div>
                                <span>Standard</span>
                            </div>
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background-color: #212529;"></div>
                                <span>Business</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    
            <div class="col-md-4">
                <div class="chart-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-chart-bar text-primary"></i>
                        <h5>Monthly Subscription Requests</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyRequestsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
                    
            <div class="col-md-4">
                <div class="chart-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-dollar-sign text-primary"></i>
                        <h5>Monthly Revenue</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                    </div>
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
                                        Your request is awaiting admin approval. Once approved, your clinic will gain access to all features of this plan.
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
                                    <td>
                                        @if($subscription->user)
                                            {{ $subscription->user->name }}
                                        @else
                                            <span class="text-muted">{{ $subscription->guest_clinic_name ?? 'Guest' }}</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <span class="badge bg-{{ $subscription->plan == 'free' ? 'secondary' : ($subscription->plan == 'basic' ? 'info' : ($subscription->plan == 'standard' ? 'primary' : 'dark')) }} text-white">
                                            {{ $subscription->plan == 'premium' ? 'Business' : ucfirst($subscription->plan) }}
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
                        <div class="col-md-3">
                            <div class="card mb-4 h-100">
                                <div class="card-header text-center bg-light">
                                    <h5 class="my-0 fw-normal">Free Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱0 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 20 Appointments/month</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic Clinic Setup (Name)</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 1 admin account</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> No Premium Reports</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        @if(auth()->check() || session()->has('tenant_user'))
                                        <a href="{{ route('subscription.create', ['plan' => 'free']) }}" class="btn btn-outline-primary w-100">
                                            Select Free Plan
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
                        
                        <div class="col-md-3">
                            <div class="card mb-4 h-100">
                                <div class="card-header text-center bg-light">
                                    <h5 class="my-0 fw-normal">Basic Plan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱599 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 100 appointments/month</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 200 products</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic Customization (Logo, 2 theme colors)</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 2 Admin/Staff Accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Standard Reports (Appointment & Inventory Summary)</li>
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
                        
                        <div class="col-md-3">
                            <div class="card mb-4 border-primary h-100">
                                <div class="card-header text-center text-white bg-primary">
                                    <h5 class="my-0 fw-normal">Standard Plan</h5>
                                    <span class="badge bg-warning text-dark">Most Popular</span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h1 class="text-center mb-3">₱1,599 <small class="text-muted fw-light">/month</small></h1>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 500 appointments/month</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory for up to 500 products</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full clinic customization (logo, banners, multiple theme colors)</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 5 staff accounts</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Full reports and basic analytics</li>
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
                        
                        <div class="col-md-3">
                            <div class="card mb-4 h-100">
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
                                        @if(auth()->check() || session()->has('tenant_user'))
                                        <a href="{{ route('subscription.create', ['plan' => 'business']) }}" class="btn btn-outline-dark w-100">
                                            Select Business Plan
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
<script>
    $(document).ready(function () {
        $('.datatable').DataTable({
            responsive: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 5,
        });
    });

    @if(auth()->check() && auth()->user()->hasRole('admin'))
    // Chart.js scripts for admin dashboard
    $(document).ready(function() {
        // Background colors with gradients
        const getGradient = (ctx, chartArea, startColor, endColor) => {
            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
            gradient.addColorStop(0, startColor);
            gradient.addColorStop(1, endColor);
            return gradient;
        };
        
        // Set background colors for statuses
        const backgroundColors = {
            primary: '#0d6efd',
            success: '#28a745',
            warning: '#ffc107',
            info: '#17a2b8',
            dark: '#212529',
            secondary: '#6c757d'
        };

        // Calculate total plan count for percentage calculation
        const totalPlans = {{ array_sum([$planDistribution['free'], $planDistribution['basic'], $planDistribution['standard'], $planDistribution['premium']]) }};
        
        // Hide empty chart message if there are plans
        if (totalPlans > 0) {
            $('.empty-chart-message').hide();
        }

        // Plan Distribution Chart
        const planCtx = document.getElementById('planDistributionChart').getContext('2d');
        const planDistributionChart = new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: ['Free', 'Basic', 'Standard', 'Premium'],
                datasets: [{
                    data: [
                        {{ $planDistribution['free'] }}, 
                        {{ $planDistribution['basic'] }}, 
                        {{ $planDistribution['standard'] }}, 
                        {{ $planDistribution['premium'] }}
                    ],
                    backgroundColor: [
                        backgroundColors.secondary,
                        backgroundColors.info,
                        backgroundColors.primary,
                        backgroundColors.dark
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = totalPlans ? Math.round((value / totalPlans) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Monthly Requests Chart
        const requestsCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
        const monthlyRequestsChart = new Chart(requestsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyRequests['labels']) !!},
                datasets: [{
                    label: 'Monthly Requests',
                    data: {!! json_encode($monthlyRequests['data']) !!},
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) {
                            return null;
                        }
                        return getGradient(
                            ctx,
                            chartArea,
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(13, 110, 253, 0.2)'
                        );
                    },
                    borderColor: backgroundColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyRevenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyRevenue['labels']) !!},
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: {!! json_encode($monthlyRevenue['data']) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: backgroundColors.success,
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
    @endif
</script>
@endsection 