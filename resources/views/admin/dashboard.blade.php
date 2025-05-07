@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('css')
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Animated Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    :root {
        --primary-gradient: var(--primary-gradient);
        --success-gradient: var(--success-gradient);
        --warning-gradient: var(--warning-gradient);
        --danger-gradient: var(--danger-gradient);
        --info-gradient: var(--info-gradient);
    }
    
    .card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border-radius: 18px;
        border: none;
        box-shadow: 0 8px 18px rgba(0,0,0,0.05);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-image: var(--primary-gradient);
        opacity: 0;
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    .card:hover::after {
        opacity: 1;
    }
    .card.primary-card::after { background-image: var(--primary-gradient); }
    .card.success-card::after { background-image: var(--success-gradient); }
    .card.warning-card::after { background-image: var(--warning-gradient); }
    .card.danger-card::after { background-image: var(--danger-gradient); }
    
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.75rem 1.75rem 1.25rem;
    }
    .card-header .card-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        background-image: var(--primary-gradient);
        box-shadow: 0 5px 15px rgba(94, 114, 228, 0.3);
    }
    .card-header .title-container {
        display: flex;
        align-items: center;
    }
    
    .icon-shape {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .icon-shape i {
        transition: all 0.3s ease;
        font-size: 1.5rem;
    }
    .icon-pulse:hover i {
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .icon-rotate:hover i {
        animation: rotate 1.5s infinite ease;
    }
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(10deg); }
        75% { transform: rotate(-10deg); }
        100% { transform: rotate(0deg); }
    }
    
    .bg-gradient-primary {
        background-image: var(--primary-gradient);
    }
    .bg-gradient-success {
        background-image: var(--success-gradient);
    }
    .bg-gradient-warning {
        background-image: var(--warning-gradient);
    }
    .bg-gradient-danger {
        background-image: var(--danger-gradient);
    }
    .bg-gradient-info {
        background-image: var(--info-gradient);
    }
    
    .numbers h5 {
        font-size: 1.85rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .numbers .card-subtitle {
        font-size: 0.875rem;
        color: var(--text-secondary-color);
        margin-top: -5px;
    }
    .text-capitalize {
        font-weight: 600;
        color: var(--text-color);
        letter-spacing: 0.3px;
    }
    
    .chart-container {
        position: relative;
        height: 350px;
        padding: 10px;
    }
    
    .list-group-item {
        padding: 1.25rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    }
    .list-group-item:last-child {
        border-bottom: none !important;
    }
    .quick-action-icon {
        transition: all 0.4s;
    }
    .list-group-item:hover .quick-action-icon {
        transform: scale(1.15) rotate(5deg);
    }
    
    .btn-icon-only {
        transition: all 0.3s;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-icon-only:hover {
        background-color: var(--card-secondary-color);
        transform: translateX(5px);
    }
    
    .page-header {
        padding: 1.75rem;
        border-radius: 18px;
        background-color: var(--card-color);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(0,0,0,0.05);
    }
    .page-header .header-bg {
        position: absolute;
        top: 0;
        right: 0;
        width: 280px;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='580' height='400' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3ClinearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%235e72e4;stop-opacity:0.1' /%3E%3Cstop offset='100%25' style='stop-color:%23825ee4;stop-opacity:0.3' /%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23grad)' d='M0,192L48,176C96,160,192,128,288,138.7C384,149,480,203,576,224C672,245,768,235,864,197.3C960,160,1056,96,1152,74.7C1248,53,1344,75,1392,85.3L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z'%3E%3C/path%3E%3C/svg%3E");
        background-size: cover;
        opacity: 0.8;
        z-index: 0;
    }
    .page-header .content-container {
        position: relative;
        z-index: 1;
    }
    .page-header h1 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    .page-header h1 i {
        margin-right: 12px;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .page-header p {
        color: var(--text-secondary-color);
        font-weight: 400;
        max-width: 70%;
    }
    .page-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-image: linear-gradient(to right, #5e72e4, #825ee4, #2dce89, #fbb140, #f5365c);
    }
    
    .stat-card {
        border-radius: 18px;
        overflow: hidden;
    }
    .stat-card .numbers {
        padding-top: 0.5rem;
    }
    .stat-card .icon-shape {
        position: relative;
    }
    .stat-card .icon-shape::before {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        opacity: 0.15;
        background: inherit;
        transition: all 0.3s ease;
    }
    .stat-card:hover .icon-shape::before {
        transform: scale(1.2);
    }
    
    .table th {
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        font-weight: 700;
        padding: 12px 24px;
        background-color: var(--card-secondary-color);
    }
    .table td {
        padding: 14px 24px;
        vertical-align: middle;
    }
    .table tr {
        border-bottom: 1px solid var(--card-secondary-color);
    }
    .table tbody tr:hover {
        background-color: var(--card-accent-color);
    }
    
    .progress-container {
        height: 7px;
        margin-top: 5px;
        background-color: var(--card-secondary-color);
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        height: 100%;
        border-radius: 10px;
        background-image: var(--primary-gradient);
    }
    
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: var(--danger-color);
        color: var(--card-color);
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        animation: pulse 1.5s infinite;
    }
    
    /* New styles for enhanced animations and effects */
    .floating-icon {
        position: absolute;
        opacity: 0.1;
        z-index: 0;
        color: var(--primary-color);
        animation: float 8s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-20px) rotate(5deg); }
        100% { transform: translateY(0) rotate(0); }
    }
    
    .glow-on-hover:hover {
        box-shadow: 0 0 15px rgba(94, 114, 228, 0.5);
    }
    
    .btn-floating {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-image: var(--primary-gradient);
        color: var(--card-color);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(94, 114, 228, 0.3);
        cursor: pointer;
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .btn-floating:hover {
        transform: scale(1.1);
    }
    
    .btn-floating i {
        font-size: 24px;
    }
    
    .highlight-text {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Floating decoration icons -->
    <div class="floating-icon" style="top: 15%; left: 10%; font-size: 2rem;">
        <i class="fas fa-paw"></i>
    </div>
    <div class="floating-icon" style="top: 30%; right: 15%; font-size: 3rem; animation-delay: 1s">
        <i class="fas fa-stethoscope"></i>
    </div>
    <div class="floating-icon" style="bottom: 20%; left: 20%; font-size: 2.5rem; animation-delay: 2s">
        <i class="fas fa-syringe"></i>
    </div>
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-bg"></div>
        <div class="content-container">
            <h1><i class="fas fa-clinic-medical fa-lg animate__animated animate__heartBeat"></i> VetClinic Admin Dashboard</h1>
            <p>Manage and monitor your veterinary clinic system with <span class="highlight-text">real-time analytics</span> and powerful tools</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card primary-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Total Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalClinics }}
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-arrow-up text-success me-1"></i>
                                    <span>+5% from last month</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center icon-pulse">
                                <i class="fas fa-hospital-alt text-lg opacity-10 text-white" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card warning-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Pending Approvals</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $pendingClinics }}
                                    @if($pendingClinics > 0)
                                    <span class="text-warning text-sm font-weight-bolder">Needs Review</span>
                                    @endif
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-clock text-warning me-1"></i>
                                    <span>Waiting for action</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center icon-pulse">
                                <i class="fas fa-hourglass-half text-lg opacity-10 text-white" aria-hidden="true"></i>
                                @if($pendingClinics > 0)
                                <span class="notification-badge">{{ $pendingClinics }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card success-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Approved Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $approvedClinics }}
                                    <span class="text-success text-sm font-weight-bolder">Active</span>
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-calendar-check text-success me-1"></i>
                                    <span>All systems operational</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center icon-rotate">
                                <i class="fas fa-check-circle text-lg opacity-10 text-white" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card danger-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Rejected Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $rejectedClinics }}
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                    <span>Didn't meet requirements</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center icon-rotate">
                                <i class="fas fa-times-circle text-lg opacity-10 text-white" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Ticket Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3">Support Ticket Overview</h5>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card info-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Total Tickets</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalTickets }}
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-ticket-alt text-info me-1"></i>
                                    <span>All support requests</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center icon-pulse">
                                <i class="fas fa-headset text-lg opacity-10 text-white" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card primary-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Open Tickets</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $openTickets }}
                                    @if($openTickets > 0)
                                    <span class="text-primary text-sm font-weight-bolder">New</span>
                                    @endif
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-envelope-open text-primary me-1"></i>
                                    <span>Need attention</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center icon-pulse">
                                <i class="fas fa-inbox text-lg opacity-10 text-white" aria-hidden="true"></i>
                                @if($openTickets > 0)
                                <span class="notification-badge">{{ $openTickets }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card warning-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">In Progress</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $inProgressTickets }}
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-sync text-warning me-1"></i>
                                    <span>Being handled</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center icon-rotate">
                                <i class="fas fa-cogs text-lg opacity-10 text-white" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card danger-card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize">Critical Tickets</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $criticalTickets }}
                                    @if($criticalTickets > 0)
                                    <span class="text-danger text-sm font-weight-bolder">Urgent</span>
                                    @endif
                                </h5>
                                <p class="card-subtitle mb-0">
                                    <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                    <span>High priority</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center icon-pulse">
                                <i class="fas fa-fire text-lg opacity-10 text-white" aria-hidden="true"></i>
                                @if($criticalTickets > 0)
                                <span class="notification-badge pulse">{{ $criticalTickets }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Overview -->
    <div class="row">
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card glow-on-hover">
                <div class="card-header pb-0">
                    <div class="title-container">
                        <div class="card-icon bg-gradient-primary">
                            <i class="fas fa-chart-line text-white animate__animated animate__fadeIn"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold">Clinic Registration Overview</h6>
                            <p class="text-sm mb-0">
                                <i class="fa fa-arrow-up text-success me-1"></i>
                                <span class="font-weight-bold">Current statistics</span> of registered clinics
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container">
                        <canvas id="chart-line" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card glow-on-hover">
                <div class="card-header pb-0 p-3">
                    <div class="title-container">
                        <div class="card-icon bg-gradient-info">
                            <i class="fas fa-bolt text-white animate__animated animate__fadeIn"></i>
                        </div>
                        <h6 class="font-weight-bold mb-0">Quick Actions</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group">
                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center quick-action-icon">
                                    <i class="fas fa-clinic-medical text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">View All Clinics</h6>
                                    <span class="text-xs">See all registered clinics in the system</span>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <a href="{{ route('admin.clinics.index') }}" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </a>
                            </div>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-warning shadow text-center quick-action-icon">
                                    <i class="fas fa-hourglass-half text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">Pending Approvals</h6>
                                    <span class="text-xs">Review clinics waiting for approval</span>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 75%; background-image: var(--warning-gradient)"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <a href="{{ route('admin.clinics.index') }}?status=pending" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </a>
                            </div>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center quick-action-icon">
                                    <i class="fas fa-credit-card text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">Subscription Requests</h6>
                                    <span class="text-xs">Manage clinic subscription plans and requests</span>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 85%; background-image: var(--info-gradient)"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </a>
                            </div>
                        </li>
                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-success shadow text-center quick-action-icon">
                                    <i class="fas fa-user-md text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">Manage Users</h6>
                                    <span class="text-xs">View and manage system users</span>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 90%; background-image: var(--success-gradient)"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <a href="#" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card glow-on-hover">
                <div class="card-header pb-0">
                    <div class="title-container">
                        <div class="card-icon bg-gradient-warning">
                            <i class="fas fa-history text-white animate__animated animate__fadeIn"></i>
                        </div>
                        <h6 class="font-weight-bold">Latest Activity</h6>
                    </div>
                </div>
                <div class="card-body px-3 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Details</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-secondary opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                                <i class="fas fa-info-circle text-white opacity-10"></i>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">System Message</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Activity log will be implemented soon</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ date('M d, Y') }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:;" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="View details">
                                            <i class="fas fa-external-link-alt me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-success shadow text-center">
                                                <i class="fas fa-check text-white opacity-10"></i>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Clinic Approved</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">PawCare Veterinary Clinic has been approved</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ date('M d, Y', strtotime('-1 day')) }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:;" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="View details">
                                            <i class="fas fa-external-link-alt me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating action button -->
    <div class="btn-floating">
        <i class="fas fa-plus"></i>
    </div>
</div>
@endsection

@push('js')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var ctx = document.getElementById("chart-line").getContext("2d");
    var gradientStroke1 = ctx.createLinearGradient(0, 230, 0, 50);
    
    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    
    var gradientStroke2 = ctx.createLinearGradient(0, 230, 0, 50);
    
    gradientStroke2.addColorStop(1, 'rgba(45, 206, 137, 0.2)');
    gradientStroke2.addColorStop(0.2, 'rgba(45, 206, 137, 0.0)');
    gradientStroke2.addColorStop(0, 'rgba(45, 206, 137, 0)');
    
    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
                label: "Registered Clinics",
                tension: 0.4,
                borderWidth: 0,
                pointRadius: 4,
                pointBackgroundColor: "#5e72e4",
                borderColor: "#5e72e4",
                backgroundColor: gradientStroke1,
                borderWidth: 3,
                fill: true,
                data: [
                    {{ $pendingClinics }}, 
                    {{ $pendingClinics + 2 }}, 
                    {{ $pendingClinics + $approvedClinics }}, 
                    {{ $totalClinics + 1 }}, 
                    {{ $totalClinics + 2 }}, 
                    {{ $totalClinics + 3 }}, 
                    {{ $totalClinics + 5 }}, 
                    {{ $totalClinics + 7 }}, 
                    {{ $totalClinics + 9 }}
                ],
                maxBarThickness: 6

            },
            {
                label: "Approved Clinics",
                tension: 0.4,
                borderWidth: 0,
                pointRadius: 4,
                pointBackgroundColor: "#2dce89",
                borderColor: "#2dce89",
                backgroundColor: gradientStroke2,
                borderWidth: 3,
                fill: true,
                data: [
                    {{ $approvedClinics }}, 
                    {{ $approvedClinics + 1 }}, 
                    {{ $approvedClinics + 2 }}, 
                    {{ $approvedClinics + 3 }}, 
                    {{ $approvedClinics + 4 }}, 
                    {{ $approvedClinics + 5 }}, 
                    {{ $approvedClinics + 6 }}, 
                    {{ $approvedClinics + 7 }}, 
                    {{ $approvedClinics + 8 }}
                ],
                maxBarThickness: 6
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#344767',
                        font: {
                            family: 'Open Sans',
                            size: 13,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#344767',
                    bodyColor: '#344767',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    usePointStyle: true,
                    boxPadding: 6
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        color: '#344767',
                        font: {
                            size: 11,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        color: '#344767',
                        padding: 20,
                        font: {
                            size: 11,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
            },
        },
    });

    // Initialize tooltips
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Floating action button
        document.querySelector('.btn-floating').addEventListener('click', function() {
            alert('Add a new clinic');
        });
    });
</script>
@endpush 