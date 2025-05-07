@extends('layouts.app')

@section('title', 'Appointment Analytics Report')
@section('page_name', 'Appointment Analytics Report')

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
    
    .status-badge {
        width: 10px;
        height: 10px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .progress-radial {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    
    .progress-radial .overlay {
        position: absolute;
        width: 80px;
        height: 80px;
        background-color: #fff;
        border-radius: 50%;
        top: 10px;
        left: 10px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress-radial .overlay span {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .progress-card {
        padding: 10px;
        border-radius: 10px;
        background-color: #f8f9fa;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .progress-card .progress-label {
        flex: 1;
        font-weight: 500;
    }
    
    .progress-card .progress-value {
        font-weight: 700;
        width: 50px;
        text-align: right;
    }
    
    .progress-card .progress-bar {
        height: 8px;
        border-radius: 4px;
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
                            <h6 class="mb-0">Appointment Analytics Report</h6>
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
                                    <li><a class="dropdown-item" href="{{ route('reports.export.pdf', ['type' => 'appointment', 'start_date' => $startDate, 'end_date' => $endDate]) }}"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.export.csv', ['type' => 'appointment', 'start_date' => $startDate, 'end_date' => $endDate]) }}"><i class="fas fa-file-csv text-success me-2"></i>CSV</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.appointment_analytics') }}" class="date-picker-form">
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
    
    <!-- Appointment Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Appointments</p>
                                <h5 class="font-weight-bolder">
                                    {{ number_format($data['total_appointments']) }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-primary text-sm font-weight-bolder">{{ number_format($data['period_appointments']) }}</span>
                                    <span class="text-secondary text-xs"> in selected period</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fas fa-calendar-check text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-9 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-4 border-right border-light">
                            <div class="text-center">
                                <h6 class="text-sm mb-0 text-muted">Status Distribution</h6>
                                <hr class="horizontal dark my-2">
                                <div class="d-flex justify-content-center">
                                    <div class="progress-radial" id="statusChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-sm mb-2 text-muted">Status Breakdown</h6>
                            <div class="row">
                                @php
                                    $statuses = $data['status_distribution'] ?? [];
                                    $totalInPeriod = $data['period_appointments'] ?: 1;
                                    $statusColors = [
                                        'completed' => 'success',
                                        'scheduled' => 'info',
                                        'cancelled' => 'danger',
                                        'no-show' => 'warning',
                                        'rescheduled' => 'primary',
                                    ];
                                @endphp
                                
                                @foreach($statuses as $status => $count)
                                    <div class="col-md-6">
                                        <div class="progress-card">
                                            <div class="status-badge bg-{{ $statusColors[$status] ?? 'secondary' }}"></div>
                                            <span class="progress-label text-capitalize">{{ $status }}</span>
                                            <span class="progress-value">{{ number_format($count) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Appointment Charts -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Appointment Type Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Day of Week Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="dayDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hour Distribution Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Hour of Day Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="hourDistributionChart"></canvas>
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
        // Type Distribution Chart
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        const typeLabels = @json(array_keys($data['type_distribution'] ?? []));
        const typeData = @json(array_values($data['type_distribution'] ?? []));
        
        const typeChart = new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: typeLabels.length > 0 ? typeLabels : ['No Data'],
                datasets: [{
                    data: typeData.length > 0 ? typeData : [1],
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
        
        // Day Distribution Chart
        const dayCtx = document.getElementById('dayDistributionChart').getContext('2d');
        const dayLabels = @json(array_keys($data['day_distribution'] ?? []));
        const dayData = @json(array_values($data['day_distribution'] ?? []));
        
        // Sort the days of week in correct order
        const daysOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const sortedDays = [];
        const sortedData = [];
        
        daysOrder.forEach(day => {
            const index = dayLabels.findIndex(label => label === day);
            if (index !== -1) {
                sortedDays.push(day);
                sortedData.push(dayData[index]);
            } else {
                sortedDays.push(day);
                sortedData.push(0);
            }
        });
        
        const dayChart = new Chart(dayCtx, {
            type: 'bar',
            data: {
                labels: sortedDays.length > 0 ? sortedDays : ['No Data'],
                datasets: [{
                    label: 'Appointments',
                    data: sortedData.length > 0 ? sortedData : [0],
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
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Hour Distribution Chart
        const hourCtx = document.getElementById('hourDistributionChart').getContext('2d');
        const hourLabels = @json(array_keys($data['hour_distribution'] ?? []));
        const hourData = @json(array_values($data['hour_distribution'] ?? []));
        
        const hourChart = new Chart(hourCtx, {
            type: 'line',
            data: {
                labels: hourLabels.length > 0 ? hourLabels : Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Appointments',
                    data: hourData.length > 0 ? hourData : Array(24).fill(0),
                    borderColor: '#11cdef',
                    backgroundColor: 'rgba(17, 205, 239, 0.1)',
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
        
        // Create status distribution chart with Chart.js doughnut
        const statusLabels = @json(array_keys($data['status_distribution'] ?? []));
        const statusData = @json(array_values($data['status_distribution'] ?? []));
        const statusColors = {
            'completed': '#2dce89',
            'scheduled': '#11cdef',
            'cancelled': '#f5365c',
            'no-show': '#fb6340',
            'rescheduled': '#5e72e4'
        };
        
        const statusColorArray = statusLabels.map(label => statusColors[label] || '#888');
        
        const statusChart = new Chart(document.createElement('canvas'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColorArray,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Create circular progress widget
        const statusChartContainer = document.getElementById('statusChart');
        const statusCanvas = document.createElement('canvas');
        statusCanvas.width = 100;
        statusCanvas.height = 100;
        statusChartContainer.appendChild(statusCanvas);
        
        // Display percentage of completed appointments
        const completedCount = @json($data['status_distribution']['completed'] ?? 0);
        const totalCount = statusData.reduce((a, b) => a + b, 0) || 1;
        const completedPercentage = Math.round((completedCount / totalCount) * 100);
        
        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        const percentSpan = document.createElement('span');
        percentSpan.innerText = completedPercentage + '%';
        overlay.appendChild(percentSpan);
        statusChartContainer.appendChild(overlay);
        
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColorArray,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
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