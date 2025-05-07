@extends('layouts.app')

@section('title', 'Dashboard Summary Report')
@section('page_name', 'Dashboard Summary Report')

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
    
    .low-stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        font-size: 0.7rem;
        border-radius: 12px;
        background-color: #f5365c;
        color: white;
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Dashboard Summary Report</h6>
                            <p class="text-sm mb-0">Comprehensive overview for {{ $data['report_period'] }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Reports
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="printReport()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <div class="dropdown ms-2">
                                <button class="btn btn-sm btn-info dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="{{ route('reports.export.pdf', 'dashboard') }}"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.export.csv', 'dashboard') }}"><i class="fas fa-file-csv text-success me-2"></i>CSV</a></li>
                                </ul>
                            </div>
                        </div>
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
                            <h4>{{ number_format($data['total_clients']) }}</h4>
                            <p>Total Clients</p>
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
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ number_format($data['total_pets']) }}</h4>
                            <p>Total Patients (Pets)</p>
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
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ number_format($data['total_appointments']) }}</h4>
                            <p>Total Appointments</p>
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
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stats-info">
                            <h4>{{ $data['completion_rate'] }}%</h4>
                            <p>Appointment Completion Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly & Upcoming Stats -->
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Appointment Statistics</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="avatar avatar-xl bg-gradient-success rounded-circle shadow me-2">
                                    <i class="fas fa-calendar-alt text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h3 class="font-weight-bold mb-0">{{ number_format($data['monthly_appointments']) }}</h3>
                                    <p class="text-sm mb-0">Appointments this month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-md-0 mt-4">
                            <div class="d-flex">
                                <div class="avatar avatar-xl bg-gradient-info rounded-circle shadow me-2">
                                    <i class="fas fa-clock text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h3 class="font-weight-bold mb-0">{{ number_format($data['upcoming_appointments']) }}</h3>
                                    <p class="text-sm mb-0">Upcoming appointments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-sm mb-3">Appointment Types</h6>
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="appointmentTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Pet Species Distribution</h6>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container">
                        <canvas id="speciesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Weekly Trends & Inventory -->
    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Weekly Appointment Trends</h6>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container">
                        <canvas id="weeklyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0 p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Inventory Status</h6>
                    <div class="d-flex">
                        <span class="low-stock-badge me-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ count($data['low_stock_items']) }} Low Stock
                        </span>
                        @if(isset($data['out_of_stock_items']) && count($data['out_of_stock_items']) > 0)
                        <span class="badge bg-danger">
                            <i class="fas fa-times-circle me-1"></i>
                            {{ count($data['out_of_stock_items']) }} Out of Stock
                        </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    @if(count($data['low_stock_items']) > 0)
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['low_stock_items'] as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <i class="fas fa-box text-warning me-2"></i>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $item->quantity <= 3 ? 'danger' : 'warning' }}">{{ $item->quantity <= 3 ? 'Critical' : 'Low' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ $item->quantity }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    @if(isset($data['out_of_stock_items']))
                                        @foreach($data['out_of_stock_items'] as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <i class="fas fa-box text-danger me-2"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            </td>
                                            <td class="text-end">
                                                <strong>0</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @if(isset($data['expiring_soon_items']) && count($data['expiring_soon_items']) > 0)
                        <div class="alert alert-info mt-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>{{ count($data['expiring_soon_items']) }}</strong> items will expire within 30 days.
                        </div>
                        @endif
                        <div class="mt-3 text-end">
                            <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-primary">View Full Inventory Report</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 3rem;"></i>
                            <p class="mb-0">All inventory items are at adequate stock levels.</p>
                            @if(isset($data['expiring_soon_items']) && count($data['expiring_soon_items']) > 0)
                            <div class="alert alert-info mt-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>{{ count($data['expiring_soon_items']) }}</strong> items will expire within 30 days.
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Species Distribution Chart
        const speciesCtx = document.getElementById('speciesChart').getContext('2d');
        const speciesLabels = @json(array_keys($data['pet_species_distribution'] ?? []));
        const speciesData = @json(array_values($data['pet_species_distribution'] ?? []));
        
        const speciesChart = new Chart(speciesCtx, {
            type: 'doughnut',
            data: {
                labels: speciesLabels.length > 0 ? speciesLabels : ['No Data'],
                datasets: [{
                    data: speciesData.length > 0 ? speciesData : [1],
                    backgroundColor: [
                        '#5e72e4', '#2dce89', '#fb6340', '#11cdef', '#f5365c', 
                        '#8965e0', '#f3a4b5', '#ffd600', '#2bffc6', '#fd5d93'
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
        
        // Appointment Types Chart
        const appointmentTypeCtx = document.getElementById('appointmentTypeChart').getContext('2d');
        const appointmentTypeLabels = @json(array_keys($data['appointment_types'] ?? []));
        const appointmentTypeData = @json(array_values($data['appointment_types'] ?? []));
        
        const appointmentTypeChart = new Chart(appointmentTypeCtx, {
            type: 'bar',
            data: {
                labels: appointmentTypeLabels.length > 0 ? appointmentTypeLabels : ['No Data'],
                datasets: [{
                    label: 'Appointments',
                    data: appointmentTypeData.length > 0 ? appointmentTypeData : [0],
                    backgroundColor: '#5e72e4',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            borderDash: [2],
                            borderDashOffset: [2]
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Weekly Trends Chart
        const trendsCtx = document.getElementById('weeklyTrendsChart').getContext('2d');
        const trendsLabels = @json(array_keys($data['weekly_appointment_trends'] ?? []));
        const trendsData = @json(array_values($data['weekly_appointment_trends'] ?? []));
        
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsLabels.length > 0 ? trendsLabels : ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Appointments',
                    data: trendsData.length > 0 ? trendsData : [0, 0, 0, 0],
                    borderColor: '#11cdef',
                    backgroundColor: 'rgba(17, 205, 239, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2],
                            borderDashOffset: [2]
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
    
    function printReport() {
        window.print();
    }
</script>
@endpush 