@extends('layouts.app')

@section('title', 'Client & Patient Report')
@section('page_name', 'Client & Patient Report')

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
    
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
    }
    
    .date-picker-form .input-group {
        width: auto;
    }
    
    .metric-title {
        font-size: 0.875rem;
        color: #8898aa;
        font-weight: 500;
    }
    
    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0;
    }
    
    .metric-change {
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .metric-change.positive {
        color: #2dce89;
    }
    
    .metric-change.negative {
        color: #f5365c;
    }
    
    .metric-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
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
                            <h6 class="mb-0">Client & Patient Report</h6>
                            <p class="text-sm mb-0">Data for period: {{ $data['period'] }}</p>
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
                                    <li><a class="dropdown-item" href="{{ route('reports.export.pdf', ['type' => 'client', 'start_date' => $startDate, 'end_date' => $endDate]) }}"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.export.csv', ['type' => 'client', 'start_date' => $startDate, 'end_date' => $endDate]) }}"><i class="fas fa-file-csv text-success me-2"></i>CSV</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.client_patient') }}" class="date-picker-form">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <label for="start_date" class="form-label">From</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                                        </div>
                                    </div>
                                    <div class="me-3">
                                        <label for="end_date" class="form-label">To</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Client Stats -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Client Statistics</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="row p-3">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="metric-icon bg-gradient-primary text-white me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="metric-title">Total Clients</p>
                                    <h4 class="metric-value">{{ number_format($data['total_clients']) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="metric-icon bg-gradient-success text-white me-3">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div>
                                    <p class="metric-title">New Clients</p>
                                    <h4 class="metric-value">{{ number_format($data['new_clients']) }}</h4>
                                    @if($data['total_clients'] > 0)
                                    <span class="metric-change positive">
                                        {{ round(($data['new_clients'] / $data['total_clients']) * 100, 1) }}% of total
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="horizontal dark">
                    
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-sm mb-3">Clients with Multiple Pets</h6>
                                <div class="progress-wrapper">
                                    @php 
                                        $multiplePercentage = $data['total_clients'] > 0 ? 
                                            round(($data['clients_with_multiple_pets'] / $data['total_clients']) * 100, 1) : 0;
                                    @endphp
                                    <div class="progress-info">
                                        <div class="progress-percentage">
                                            <span class="text-sm font-weight-bold">{{ number_format($data['clients_with_multiple_pets']) }} clients ({{ $multiplePercentage }}%)</span>
                                        </div>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-gradient-info" role="progressbar" 
                                             aria-valuenow="{{ $multiplePercentage }}" aria-valuemin="0" 
                                             aria-valuemax="100" style="width: {{ $multiplePercentage }}%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Pet Stats -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Patient Statistics</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="row p-3">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="metric-icon bg-gradient-info text-white me-3">
                                    <i class="fas fa-paw"></i>
                                </div>
                                <div>
                                    <p class="metric-title">Total Patients</p>
                                    <h4 class="metric-value">{{ number_format($data['total_pets']) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="metric-icon bg-gradient-warning text-white me-3">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>
                                    <p class="metric-title">New Patients</p>
                                    <h4 class="metric-value">{{ number_format($data['new_pets']) }}</h4>
                                    @if($data['total_pets'] > 0)
                                    <span class="metric-change positive">
                                        {{ round(($data['new_pets'] / $data['total_pets']) * 100, 1) }}% of total
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="horizontal dark">
                    
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-sm mb-3">Patients per Client Ratio</h6>
                                @php 
                                    $ratio = $data['total_clients'] > 0 ? 
                                        round($data['total_pets'] / $data['total_clients'], 2) : 0;
                                @endphp
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xl bg-gradient-success rounded-circle shadow me-3">
                                        <h3 class="text-white m-0">{{ $ratio }}</h3>
                                    </div>
                                    <div>
                                        <p class="mb-0">Average pets per client</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Species Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="speciesChart"></canvas>
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
        // Species Distribution Chart
        const speciesCtx = document.getElementById('speciesChart').getContext('2d');
        const speciesLabels = @json(array_keys($data['species_distribution'] ?? []));
        const speciesData = @json(array_values($data['species_distribution'] ?? []));
        
        const speciesChart = new Chart(speciesCtx, {
            type: 'bar',
            data: {
                labels: speciesLabels.length > 0 ? speciesLabels : ['No Data'],
                datasets: [{
                    label: 'Number of Pets',
                    data: speciesData.length > 0 ? speciesData : [0],
                    backgroundColor: [
                        '#5e72e4', '#2dce89', '#fb6340', '#11cdef', '#f5365c', 
                        '#8965e0', '#f3a4b5', '#ffd600', '#2bffc6', '#fd5d93'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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