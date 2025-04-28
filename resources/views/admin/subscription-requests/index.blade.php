@extends('layouts.app')

@section('title', 'Admin - Subscription Requests')

@section('page-name', 'Subscription Requests Management')

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
        border-radius: 15px;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
        margin-bottom: 24px;
        background: #fff;
        height: 100%;
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .chart-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }
    
    .chart-card .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
        position: relative;
    }
    
    .chart-card .card-header h5 {
        font-size: 15px;
        font-weight: 600;
        color: #343a40;
        margin: 0;
    }
    
    .chart-card .card-header i {
        margin-right: 8px;
        opacity: 0.8;
        font-size: 16px;
    }
    
    .chart-card .card-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 200px;
        width: 100%;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Animation for cards */
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
    
    /* Plan list styling */
    .plan-list {
        list-style-type: none;
        padding: 0;
        margin: 10px 0 0 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 10px;
    }
    
    .plan-item {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
        background-color: rgba(0,0,0,0.02);
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    
    .plan-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .plan-item:hover {
        transform: translateY(-3px);
        background-color: rgba(0,0,0,0.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .plan-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        position: relative;
    }
    
    .plan-color::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        border: 2px solid transparent;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .plan-item:hover .plan-color::after {
        opacity: 1;
    }
    
    .plan-name {
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .plan-count {
        margin-left: auto;
        height: 20px;
        min-width: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        padding: 0 6px;
        transition: all 0.3s ease;
        color: white;
    }
    
    /* Plan specific styles */
    .free-plan {
        background-color: rgba(108, 117, 125, 0.1);
    }
    
    .basic-plan {
        background-color: rgba(23, 162, 184, 0.1);
    }
    
    .standard-plan {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .business-plan {
        background-color: rgba(33, 37, 41, 0.1);
    }
    
    .free-plan::before {
        background-color: #6c757d;
    }
    
    .basic-plan::before {
        background-color: #17a2b8;
    }
    
    .standard-plan::before {
        background-color: #0d6efd;
    }
    
    .business-plan::before {
        background-color: #212529;
    }
    
    .free-plan .plan-color {
        background-color: #6c757d;
    }
    
    .basic-plan .plan-color {
        background-color: #17a2b8;
    }
    
    .standard-plan .plan-color {
        background-color: #0d6efd;
    }
    
    .business-plan .plan-color {
        background-color: #212529;
    }
    
    .free-plan .plan-color::after {
        border-color: #6c757d;
    }
    
    .basic-plan .plan-color::after {
        border-color: #17a2b8;
    }
    
    .standard-plan .plan-color::after {
        border-color: #0d6efd;
    }
    
    .business-plan .plan-color::after {
        border-color: #212529;
    }
    
    .free-plan .plan-name {
        color: #495057;
    }
    
    .basic-plan .plan-name {
        color: #495057;
    }
    
    .standard-plan .plan-name {
        color: #495057;
    }
    
    .business-plan .plan-name {
        color: #495057;
    }
    
    .free-plan .plan-count {
        background-color: #6c757d;
    }
    
    .basic-plan .plan-count {
        background-color: #17a2b8;
    }
    
    .standard-plan .plan-count {
        background-color: #0d6efd;
    }
    
    .business-plan .plan-count {
        background-color: #212529;
    }
    
    /* Animation for plan items */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .plan-item {
        animation: fadeIn 0.5s ease forwards;
    }
    
    .plan-item:nth-child(1) {
        animation-delay: 0.1s;
    }
    
    .plan-item:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .plan-item:nth-child(3) {
        animation-delay: 0.3s;
    }
    
    .plan-item:nth-child(4) {
        animation-delay: 0.4s;
    }
    
    .plan-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .plan-item:hover .plan-color {
        transform: scale(1.1);
    }
    
    .plan-item:hover .plan-count {
        transform: scale(1.1);
    }
    
    /* Legend styles */
    .chart-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed rgba(0,0,0,0.05);
    }
    
    .chart-legend-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 20px;
        background-color: rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }
    
    .chart-legend-item:hover {
        background-color: rgba(0,0,0,0.05);
        transform: translateY(-2px);
    }
    
    .chart-legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
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
    
    /* Placeholder doughnut */
    .placeholder-doughnut {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        border-radius: 50%;
        background-color: #f8f9fa;
        border: 8px solid #e9ecef;
    }
    
    .placeholder-doughnut::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 60%;
        height: 60%;
        background-color: white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }
    
    /* Dashboard container */
    .dashboard-container {
        background-color: #f8f9fc;
        border-radius: 20px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    
    /* Chart title */
    .chart-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 0;
        color: #495057;
    }
    
    .chart-title i {
        margin-right: 8px;
        color: #6c757d;
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
                <div class="text-muted">Manage clinic subscription plans and requests</div>
            </div>
            <div>
                <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Statistics Overview -->
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card animate-card animate-delay-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-label">ACTIVE SUBSCRIPTIONS</p>
                                <h4>5</h4>
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
                                <h4>0</h4>
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
                                <h4>₱29,873.10</h4>
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
                                <h4>₱5,974.62</h4>
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
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="chart-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-chart-pie text-primary"></i>
                        <h5>Subscription Plan Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="planDistributionChart"></canvas>
                        </div>
                        <div class="plan-list">
                            <div class="plan-item free-plan">
                                <div class="plan-color"></div>
                                <span class="plan-name">Free</span>
                                <span class="plan-count">2</span>
                            </div>
                            <div class="plan-item basic-plan">
                                <div class="plan-color"></div>
                                <span class="plan-name">Basic</span>
                                <span class="plan-count">1</span>
                            </div>
                            <div class="plan-item standard-plan">
                                <div class="plan-color"></div>
                                <span class="plan-name">Standard</span>
                                <span class="plan-count">1</span>
                            </div>
                            <div class="plan-item business-plan">
                                <div class="plan-color"></div>
                                <span class="plan-name">Business</span>
                                <span class="plan-count">1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    
            <div class="col-md-6">
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
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
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
            
            <div class="col-md-6">
                <div class="chart-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-user-check text-primary"></i>
                        <h5>Subscription Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="subscriptionStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-clock me-1"></i>
                    Pending Subscription Requests
                </div>
                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Clinic</th>
                                    <th>Plan</th>
                                    <th>Guest Info</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Request Date</th>
                                    <th width="220">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->id }}</td>
                                    <td>
                                        @if($request->user_id)
                                            {{ $request->user->name ?? 'N/A' }}
                                        @else
                                            <strong>{{ $request->guest_clinic_name }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->plan == 'free' ? 'secondary' : ($request->plan == 'basic' ? 'info' : ($request->plan == 'standard' ? 'primary' : 'dark')) }}">
                                            {{ $request->plan == 'premium' ? 'Business' : ucfirst($request->plan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(!$request->user_id)
                                            <small>
                                                <strong>Email:</strong> {{ $request->guest_email }}<br>
                                                <strong>Phone:</strong> {{ $request->guest_phone }}
                                            </small>
                                        @else
                                            <span class="text-muted">Registered User</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->duration }} month(s)</td>
                                    <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.subscription-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            
                                            <!-- Approve Button - Opens Modal -->
                                            <button type="button" class="btn btn-sm btn-success ms-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            
                                            <!-- Reject Button - Opens Modal -->
                                            <button type="button" class="btn btn-sm btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                        
                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">Approve Subscription Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.subscription-requests.approve', $request->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>You are approving the subscription request for <strong>{{ $request->user_id ? ($request->user->name ?? 'N/A') : $request->guest_clinic_name }}</strong>.</p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="expiration_date{{ $request->id }}" class="form-label">Expiration Date <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" id="expiration_date{{ $request->id }}" name="expiration_date" 
                                                                    value="{{ Carbon\Carbon::now()->addMonths($request->duration)->format('Y-m-d') }}" required>
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
                                                    <form action="{{ route('admin.subscription-requests.reject', $request->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>You are rejecting the subscription request for <strong>{{ $request->user_id ? ($request->user->name ?? 'N/A') : $request->guest_clinic_name }}</strong>.</p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="rejection_reason{{ $request->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="rejection_reason{{ $request->id }}" name="rejection_reason" rows="3" placeholder="Provide reason for rejection" required></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="admin_notes_reject{{ $request->id }}" class="form-label">Admin Notes (Optional)</label>
                                                                <textarea class="form-control" id="admin_notes_reject{{ $request->id }}" name="admin_notes" rows="3" placeholder="Optional internal notes"></textarea>
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
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> There are no pending subscription requests at this time.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Processed Subscription Requests
                </div>
                <div class="card-body">
                    @if($processedRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Clinic</th>
                                    <th>Plan</th>
                                    <th>Guest Info</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Processed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedRequests as $request)
                                <tr>
                                    <td>{{ $request->id }}</td>
                                    <td>
                                        @if($request->user_id)
                                            {{ $request->user->name ?? 'N/A' }}
                                        @else
                                            <strong>{{ $request->guest_clinic_name }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->plan == 'free' ? 'secondary' : ($request->plan == 'basic' ? 'info' : ($request->plan == 'standard' ? 'primary' : 'dark')) }}">
                                            {{ $request->plan == 'premium' ? 'Business' : ucfirst($request->plan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(!$request->user_id)
                                            <small>
                                                <strong>Email:</strong> {{ $request->guest_email }}<br>
                                                <strong>Phone:</strong> {{ $request->guest_phone }}
                                            </small>
                                        @else
                                            <span class="text-muted">Registered User</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->duration }} month(s)</td>
                                    <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    <td>
                                        @if($request->status == 'approved' || $request->status == 'active')
                                            <span class="badge bg-success">{{ ucfirst($request->status) }}</span>
                                        @elseif($request->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->status == 'approved' || $request->status == 'active')
                                            {{ Carbon\Carbon::parse($request->approved_at)->format('M d, Y') }}
                                        @elseif($request->status == 'rejected')
                                            {{ Carbon\Carbon::parse($request->rejected_at)->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscription-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            {{ $processedRequests->links() }}
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> There are no processed subscription requests to display.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add icon background colors
    document.querySelectorAll('.bg-primary-light').forEach(el => {
        el.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
    });
    document.querySelectorAll('.bg-success-light').forEach(el => {
        el.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
    });
    document.querySelectorAll('.bg-warning-light').forEach(el => {
        el.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
    });
    document.querySelectorAll('.bg-info-light').forEach(el => {
        el.style.backgroundColor = 'rgba(23, 162, 184, 0.1)';
    });
    
    // Common Chart Animation
    const transitionDuration = 600;
    
    // Set chart fonts and common styling
    Chart.defaults.font.family = "'Open Sans', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6c757d';
    Chart.defaults.plugins.tooltip.padding = 8;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.tooltip.titleFont.size = 12;
    Chart.defaults.plugins.tooltip.titleFont.weight = 'bold';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(0, 0, 0, 0.1)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 4;
    
    // Plan Distribution Chart
    var planDistributionCtx = document.getElementById('planDistributionChart').getContext('2d');
    
    var planDistributionChart = new Chart(planDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['Free', 'Basic', 'Standard', 'Business'],
            datasets: [{
                data: [
                    @foreach($planDistribution as $plan => $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#6c757d',  // Free plan
                    '#4e73df',  // Basic plan
                    '#1cc88a',  // Standard plan
                    '#f6c23e'   // Business plan
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Common Chart Labels - months
    const monthLabels = ['Nov 2024', 'Dec 2024', 'Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025'];
    
    // Monthly Subscription Requests Chart
    var monthlyRequestsCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
    
    // Create blue gradient for bar chart
    var blueGradient = monthlyRequestsCtx.createLinearGradient(0, 0, 0, 200);
    blueGradient.addColorStop(0, 'rgba(13, 110, 253, 0.8)');
    blueGradient.addColorStop(1, 'rgba(13, 110, 253, 0.15)');
    
    var monthlyRequestsChart = new Chart(monthlyRequestsCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Subscriptions',
                data: [0, 0, 0, 0, 0, 5],
                backgroundColor: blueGradient,
                borderColor: '#0d6efd',
                borderWidth: 0,
                borderRadius: 6,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    right: 10,
                    bottom: 0,
                    left: 0
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.03)',
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `${context.parsed.y} new subscriptions`;
                        }
                    }
                }
            },
            animation: {
                delay: function(context) {
                    return context.dataIndex * 50;
                },
                duration: transitionDuration,
                easing: 'easeOutQuart'
            }
        }
    });

    // Monthly Revenue Chart
    var monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    
    // Create green gradient for line chart
    var greenGradient = monthlyRevenueCtx.createLinearGradient(0, 0, 0, 200);
    greenGradient.addColorStop(0, 'rgba(40, 167, 69, 0.4)');
    greenGradient.addColorStop(1, 'rgba(40, 167, 69, 0)');
    
    var monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Revenue',
                data: [0, 0, 0, 0, 0, 30000],
                fill: true,
                backgroundColor: greenGradient,
                borderColor: '#28a745',
                tension: 0.4,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBorderWidth: 2,
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    right: 10,
                    bottom: 0,
                    left: 0
                }
            },
            scales: {
                y: {
                    suggestedMin: 0,
                    suggestedMax: 30000,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.03)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        },
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `Revenue: ₱${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            },
            animation: {
                duration: transitionDuration,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Add shadow effect to the charts when hovered
    document.querySelectorAll('.chart-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '0 6px 24px rgba(0, 0, 0, 0.12)';
        });
    });

    // Subscription Status Chart
    var subscriptionStatusCtx = document.getElementById('subscriptionStatusChart').getContext('2d');
    var subscriptionStatusChart = new Chart(subscriptionStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $stats['activeSubscriptions'] ?? $stats['totalActive'] ?? 0 }},
                    {{ $stats['pendingRequests'] ?? $stats['totalPending'] ?? 0 }},
                    {{ $stats['rejectedRequests'] ?? $stats['totalRejected'] ?? 0 }}
                ],
                backgroundColor: [
                    '#1cc88a',  // Active
                    '#f6c23e',  // Pending
                    '#e74a3b'   // Rejected
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush 