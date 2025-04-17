@extends('layouts.app')

@section('title', 'Premium Reports')
@section('page_name', 'Premium Reports Dashboard')

@push('css')
<style>
    .feature-card {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
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
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Premium Reports</h6>
                            <p class="text-sm mb-0">Access exclusive analytics and insights for your clinic</p>
                        </div>
                        <span class="badge bg-gradient-warning">Subscription Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        <strong>Premium Feature:</strong> Thank you for being a valued subscriber! Your active subscription gives you access to all premium reports and analytics.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card feature-card h-100">
                <span class="premium-badge">Premium</span>
                <div class="card-body text-center p-4">
                    <div class="feature-icon text-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Advanced Analytics</h5>
                    <p class="text-muted">Get detailed insights about your clinic's performance, patient trends, and financial metrics.</p>
                    <a href="{{ route('premium.analytics') }}" class="btn btn-primary mt-3">
                        View Analytics
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card h-100">
                <span class="premium-badge">Premium</span>
                <div class="card-body text-center p-4">
                    <div class="feature-icon text-success">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h5>Export Reports</h5>
                    <p class="text-muted">Export customized reports in various formats including PDF, Excel, and CSV.</p>
                    <button type="button" class="btn btn-success mt-3" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card h-100">
                <span class="premium-badge">Premium</span>
                <div class="card-body text-center p-4">
                    <div class="feature-icon text-info">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h5>Patient Insights</h5>
                    <p class="text-muted">Understand patient behaviors, demographics, and satisfaction metrics.</p>
                    <button type="button" class="btn btn-info mt-3" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 