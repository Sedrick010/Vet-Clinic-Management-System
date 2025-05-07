@extends('layouts.app')

@section('title', 'Clinic Reports')
@section('page_name', 'Comprehensive Reports')

@push('css')
<style>
    .report-card {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .report-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .feature-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(45deg, #5e72e4, #825ee4);
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
</style>
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
                            <h6 class="mb-0">Comprehensive Reporting System</h6>
                            <p class="text-sm mb-0">Access detailed reports and insights for your veterinary clinic</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <strong>Data-Driven Decisions:</strong> These reports provide insights into your clinic's operations, helping you make informed decisions to improve client satisfaction and business performance.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card report-card h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon text-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Dashboard Summary</h5>
                    <p class="text-muted">Get a comprehensive overview of your clinic's key metrics and performance indicators.</p>
                    <a href="{{ route('reports.dashboard_summary') }}" class="btn btn-primary mt-3">
                        View Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card report-card h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon text-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Client & Patient</h5>
                    <p class="text-muted">Analyze client demographics and patient statistics to understand your customer base.</p>
                    <a href="{{ route('reports.client_patient') }}" class="btn btn-success mt-3">
                        View Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card report-card h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon text-info">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h5>Appointment Analytics</h5>
                    <p class="text-muted">Track appointment patterns, completion rates, and optimize your scheduling.</p>
                    <a href="{{ route('reports.appointment_analytics') }}" class="btn btn-info mt-3">
                        View Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card report-card h-100">
                <div class="card-body text-center p-4">
                    <div class="report-icon text-warning">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h5>Inventory Management</h5>
                    <p class="text-muted">Monitor inventory levels, expiring products, and optimize your supply chain.</p>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-warning mt-3">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Export Options</h6>
                </div>
                <div class="card-body">
                    <p>All reports can be exported in various formats for your records or presentations:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6><i class="fas fa-file-pdf text-danger me-2"></i> PDF Export</h6>
                                    <p class="small mb-0">Perfect for sharing formal reports with stakeholders or for printing.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6><i class="fas fa-file-csv text-success me-2"></i> CSV/Excel Export</h6>
                                    <p class="small mb-0">Ideal for further data analysis in spreadsheet software.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 