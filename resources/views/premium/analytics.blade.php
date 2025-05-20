@extends('layouts.app')

@section('title', 'Advanced Analytics')
@section('page_name', 'Advanced Analytics')

@push('css')
<style>
    .stats-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stats-icon {
        font-size: 1.8rem;
        margin-right: 1rem;
        opacity: 0.7;
    }
    
    .stats-info h4 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.2rem;
    }
    
    .stats-info p {
        font-size: 0.85rem;
        margin-bottom: 0;
        opacity: 0.7;
    }
    
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
    }
    
    .premium-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(45deg, #f5a623, #f8e71c);
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    /* Subscription status indicator */
    .subscription-indicator {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 20px;
        background-color: #4CAF50;
        color: white;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .subscription-indicator i {
        margin-right: 6px;
    }
    
    #subscription-status-alert {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        max-width: 350px;
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Subscription Status Alert - Hidden by default, shown by JavaScript when needed -->
    <div id="subscription-status-alert" class="alert alert-warning shadow-lg" role="alert">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
            </div>
            <div class="ms-3">
                <h5 class="alert-heading">Subscription Status Change</h5>
                <p>Your subscription status has changed. You will be redirected to the dashboard.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Advanced Analytics</h6>
                            <p class="text-sm mb-0">Comprehensive insights about your clinic's performance</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="subscription-indicator me-3">
                                <i class="fas fa-star"></i> Premium Active
                            </div>
                            <a href="{{ route('premium.reports') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Reports
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <strong>Premium Feature:</strong> These analytics are available exclusively for clinics with active subscriptions.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-gradient-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ $data['total_patients'] }}</h4>
                            <p>Total Patients</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-gradient-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ $data['monthly_visits'] }}</h4>
                            <p>Monthly Visits</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-gradient-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stats-info">
                            <h4>${{ number_format($data['revenue']) }}</h4>
                            <p>Monthly Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card bg-gradient-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-smile"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ $data['satisfaction_score'] }}%</h4>
                            <p>Satisfaction Score</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <span class="premium-badge">Premium</span>
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Common Treatments</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container">
                        <canvas id="treatmentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <span class="premium-badge">Premium</span>
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Patient Growth</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Common Treatments Chart
        const treatmentCtx = document.getElementById('treatmentsChart').getContext('2d');
        const treatmentChart = new Chart(treatmentCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($data['common_treatments'] as $treatment => $count)
                        '{{ $treatment }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($data['common_treatments'] as $treatment => $count)
                            {{ $count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(94, 114, 228, 0.8)',
                        'rgba(45, 206, 137, 0.8)',
                        'rgba(255, 171, 67, 0.8)',
                        'rgba(17, 205, 239, 0.8)',
                        'rgba(245, 54, 92, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
        
        // Patient Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        const growthChart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($data['patient_growth'] as $month => $growth)
                        '{{ $month }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'New Patients',
                    data: [
                        @foreach($data['patient_growth'] as $month => $growth)
                            {{ $growth }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(45, 206, 137, 0.2)',
                    borderColor: 'rgba(45, 206, 137, 1)',
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
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush 