@extends('layouts.app')

@section('title', 'Inventory Report')
@section('page_name', 'Inventory Report')

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
    
    .inventory-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 1rem;
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
    
    .warning-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        font-size: 0.7rem;
        border-radius: 12px;
        background-color: #fb6340;
        color: white;
    }
    
    .danger-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        font-size: 0.7rem;
        border-radius: 12px;
        background-color: #f5365c;
        color: white;
    }
    
    .expiry-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        font-size: 0.7rem;
        border-radius: 12px;
        background-color: #11cdef;
        color: white;
    }
    
    .item-card {
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .item-card:hover {
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
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
                            <h6 class="mb-0">Inventory Report</h6>
                            <p class="text-sm mb-0">Comprehensive inventory status and analysis</p>
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
                                    <li><a class="dropdown-item" href="{{ route('reports.export.pdf', 'inventory') }}"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.export.csv', 'inventory') }}"><i class="fas fa-file-csv text-success me-2"></i>CSV</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Items</p>
                                <h5 class="font-weight-bolder">
                                    {{ number_format($data['total_items']) }}
                                </h5>
                                @if(isset($data['active_items']) && isset($data['inactive_items']))
                                <p class="mb-0 text-xs">
                                    <span class="text-success">{{ number_format($data['active_items'] ?? 0) }}</span> active, 
                                    <span class="text-secondary">{{ number_format($data['inactive_items'] ?? 0) }}</span> inactive
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fas fa-cubes text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Low Stock</p>
                                <h5 class="font-weight-bolder">
                                    {{ count($data['low_stock_items']) }}
                                </h5>
                                <p class="mb-0 text-xs">
                                    <span class="text-danger">{{ count(array_filter($data['low_stock_items'] ?? [], function($item) { return $item->quantity <= 3; })) }}</span> critical level
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="fas fa-exclamation-triangle text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Out of Stock</p>
                                <h5 class="font-weight-bolder">
                                    {{ count($data['out_of_stock_items']) }}
                                </h5>
                                <p class="mb-0 text-xs">
                                    <span class="text-{{ count($data['out_of_stock_items']) > 0 ? 'danger' : 'success' }}">
                                        {{ count($data['out_of_stock_items']) > 0 ? 'Needs restocking' : 'All items in stock' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="fas fa-times-circle text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Expiring Soon</p>
                                <h5 class="font-weight-bolder">
                                    {{ count($data['expiring_soon_items']) }}
                                </h5>
                                <p class="mb-0 text-xs">
                                    Within 30 days
                                    @if(isset($data['expiring_next_week']))
                                    (<span class="text-danger">{{ $data['expiring_next_week'] ?? 0 }}</span> within 7 days)
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                <i class="fas fa-clock text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Value Summary -->
    @if(isset($data['total_inventory_value']) || isset($data['avg_item_value']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Inventory Value Summary</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        @if(isset($data['total_inventory_value']))
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow me-3" style="height: 48px; width: 48px;">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-muted mb-0">Total Inventory Value</p>
                                    <h4 class="font-weight-bold mb-0">${{ number_format($data['total_inventory_value'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($data['avg_item_value']))
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow me-3" style="height: 48px; width: 48px;">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-muted mb-0">Average Item Value</p>
                                    <h4 class="font-weight-bold mb-0">${{ number_format($data['avg_item_value'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($data['low_stock_value']))
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow me-3" style="height: 48px; width: 48px;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-muted mb-0">Low Stock Value</p>
                                    <h4 class="font-weight-bold mb-0">${{ number_format($data['low_stock_value'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Details -->
    <div class="row">
        <!-- Category Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Category Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Items -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Critical Inventory Items</h6>
                    <div class="d-flex">
                        <span class="warning-badge me-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Low Stock
                        </span>
                        <span class="danger-badge">
                            <i class="fas fa-times-circle me-1"></i>
                            Out of Stock
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($data['low_stock_items']) > 0 || count($data['out_of_stock_items']) > 0)
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['low_stock_items'] as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="icon icon-shape bg-warning text-white rounded-circle shadow me-2" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $item->quantity }} left</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    @foreach($data['out_of_stock_items'] as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="icon icon-shape bg-danger text-white rounded-circle shadow me-2" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Out of stock</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 3rem;"></i>
                            <p class="mb-0">All inventory items are at adequate stock levels.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expiring Items -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Items Expiring Soon</h6>
                    <span class="expiry-badge">
                        <i class="fas fa-clock me-1"></i>
                        Within 30 Days
                    </span>
                </div>
                <div class="card-body">
                    @if(count($data['expiring_soon_items']) > 0)
                        <div class="row">
                            @foreach($data['expiring_soon_items'] as $item)
                                <div class="col-lg-3 col-md-6">
                                    <div class="card item-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex">
                                                <div class="icon icon-shape bg-info text-white rounded-circle shadow" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                    <p class="text-xs text-muted mb-0">Expires: {{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</p>
                                                    <p class="text-xs mb-0">
                                                        <span class="text-danger">
                                                            {{ \Carbon\Carbon::parse($item->expiry_date)->diffForHumans() }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 3rem;"></i>
                            <p class="mb-0">No items are expiring within the next 30 days.</p>
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
        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryLabels = @json(array_keys($data['category_distribution'] ?? []));
        const categoryData = @json(array_values($data['category_distribution'] ?? []));
        
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels.length > 0 ? categoryLabels : ['No Data'],
                datasets: [{
                    data: categoryData.length > 0 ? categoryData : [1],
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
    });
    
    function printReport() {
        window.print();
    }
</script>
@endpush 