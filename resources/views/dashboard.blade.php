@extends('layouts.app')

@section('title', $clinicName . ' - Dashboard')
@section('page_name', $clinicName . ' Dashboard')

@php
    // Ensure sidebar is enabled for this page
    $isSidebar = true;

    // Check for any available updates
    $systemUpdateService = app(\App\Services\SystemUpdateService::class);
    $updateCheck = $systemUpdateService->checkForUpdates($clinic);
    $hasUpdates = isset($updateCheck['success']) && $updateCheck['success'] && $updateCheck['has_updates'];
    $pendingUpdates = $hasUpdates ? $updateCheck['updates'] : collect();
    $criticalUpdates = $pendingUpdates->where('is_critical', true);
    $mandatoryUpdates = $pendingUpdates->where('is_mandatory', true);
    
    // Get current system version for this clinic
    $currentVersion = isset($updateCheck['current_version']) ? $updateCheck['current_version'] : config('self-update.version_installed');
    
    // For testing purposes - simulate different versions in local environment
    if(app()->environment('local') && request()->has('test_version')) {
        $currentVersion = request()->get('test_version');
    }
@endphp

@section('content')
<div class="container-fluid py-4">
    @php
        // We already have update information from above, no need to call again
    @endphp

    <!-- System Version Information Card -->
    <div class="row mb-4">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>System Version</h6>
                            <p class="text-sm mb-0">
                                <i class="fa fa-check text-success" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1">Current Version: v{{ $currentVersion }}</span>
                            </p>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <a href="{{ route('updates.index') }}" class="btn btn-sm btn-dark">Manage Updates</a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2"></div>
            </div>
        </div>
    </div>

    @if($hasUpdates)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert {{ $criticalUpdates->count() > 0 ? 'bg-gradient-danger' : 'bg-gradient-primary' }} alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-sm me-3">
                            <i class="fas {{ $criticalUpdates->count() > 0 ? 'fa-exclamation-triangle' : 'fa-sync-alt' }} text-white"></i>
                        </div>
                        <div class="text-white flex-grow-1">
                            <span class="fw-bold fs-6">{{ $criticalUpdates->count() > 0 ? 'Critical System Update Available' : 'System Update Available' }}</span>
                            <p class="mb-0 mt-1">
                                Version {{ $pendingUpdates->first()->version }} is ready to install
                                @if($mandatoryUpdates->count() > 0)
                                    (mandatory update)
                                @endif
                            </p>
                        </div>
                        <div class="d-flex">
                            @php
                                $update = $pendingUpdates->first();
                            @endphp
                            
                            <form action="{{ route('updates.apply', $update->id) }}" method="POST" class="d-inline me-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light" 
                                    onclick="return confirm('Are you sure you want to apply this update?');">
                                    <i class="fas fa-download me-1"></i> Apply Now
                                </button>
                            </form>
                            
                            @if(!$update->is_mandatory)
                                <form action="{{ route('updates.dismiss', $update->id) }}" method="POST" class="d-inline me-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-light" 
                                        onclick="return confirm('Are you sure you want to dismiss this update?');">
                                        <i class="fas fa-times me-1"></i> Dismiss
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('updates.index') }}" class="btn btn-sm btn-outline-light me-2">
                                <i class="fas fa-info-circle me-1"></i> Details
                            </a>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Welcome Message for Clinic Owner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-1">Welcome, {{ $userName }}!</h4>
                            <p class="mb-0">You are logged in as <strong>{{ ucfirst($userRole) }}</strong> at <strong>{{ $clinicName }}</strong> Veterinary Clinic</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-gradient-success">Approved Clinic</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pet Care Motivation Message -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape icon-md rounded-circle bg-white text-info me-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-white font-weight-bold">Pets should be taken care just like humans</h5>
                            <p class="mb-0 text-white opacity-8">Our mission is to provide the same level of care for your pets as we would for any family member</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Warning Alert -->
    @if(isset($clinic) && !$clinic->is_subscription_active)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert bg-gradient-primary alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="icon icon-sm me-3">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div class="text-white flex-grow-1">
                        <span class="fw-bold fs-6">Upgrade to Premium!</span>
                        <p class="mb-0 mt-1">Unlock advanced features including detailed analytics, unlimited appointments, client reminders, and comprehensive inventory management. Boost your clinic's efficiency today!</p>
                    </div>
                    <div>
                        <a href="mailto:admin@vetclinic.localtest.me?subject=Premium Subscription Request for {{ $clinic->name }}&body=Hello Administrator,%0D%0A%0D%0AI would like to request activation of the premium subscription for our clinic: {{ $clinic->name }} (ID: {{ $clinic->id }}).%0D%0A%0D%0AThank you." class="btn btn-sm btn-outline-light ms-3">
                            <i class="fas fa-arrow-circle-up me-1"></i> Request Premium Access
                        </a>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- New Feature Notification -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert bg-gradient-info border-0 text-white fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-users-cog fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1"><strong>New Feature: Staff Management</strong></h6>
                    <p class="mb-0">Easily add, edit, and manage clinic staff members.</p>
                </div>
                <div class="ms-auto d-flex align-items-center">
                    <a href="{{ route('staff.index') }}" class="btn btn-sm btn-light me-3">
                        <i class="fas fa-arrow-right me-1"></i> Try it now
                    </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Icon styling for Quick Actions and Statistics */
        .icon-shape {
            width: 48px !important;
            height: 48px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            margin-bottom: 1rem !important;
            position: relative !important;
        }
        
        .icon-shape i {
            font-size: 1.25rem !important;
            line-height: 0 !important;
            position: relative !important;
            top: 0 !important;
            transform: translateY(0) !important;
        }
        
        /* Quick Actions specific styling */
        .card-body .icon-shape.icon-md {
            width: 48px !important;
            height: 48px !important;
            margin: 0 auto 1rem auto !important;
        }
        
        /* Statistics card icons */
        .numbers + .col-4 .icon-shape {
            width: 48px !important;
            height: 48px !important;
            margin: 0 !important;
        }
        
        .numbers + .col-4 .icon-shape i {
            font-size: 1.25rem !important;
        }
        
        /* Remove opacity from icons */
        .opacity-10 {
            opacity: 1 !important;
        }
        
        /* Fix vertical alignment for all icons */
        .icon-shape i.fas,
        .icon-shape i.far,
        .icon-shape i.fab {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
        }
    </style>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <!-- New Appointment -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <a href="{{ route('appointments.create') }}" class="card shadow-sm h-100 text-decoration-none">
                                <div class="card-body p-3 text-center">
                                    <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-info">
                                        <i class="fas fa-calendar-plus text-white opacity-10"></i>
                                    </div>
                                    <h6 class="mb-0 text-dark">New Appointment</h6>
                                </div>
                            </a>
                        </div>

                        <!-- Staff Management -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <a href="{{ route('staff.index') }}" class="card shadow-sm h-100 text-decoration-none">
                                <div class="card-body p-3 text-center">
                                    <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-primary">
                                        <i class="fas fa-user-tie text-white opacity-10"></i>
                                    </div>
                                    <h6 class="mb-0 text-dark">Staff Management</h6>
                                </div>
                            </a>
                        </div>

                        <!-- Inventory -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <a href="{{ route('inventory.index') }}" class="card shadow-sm h-100 text-decoration-none">
                                <div class="card-body p-3 text-center">
                                    <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-warning">
                                        <i class="fas fa-box text-white opacity-10"></i>
                                    </div>
                                    <h6 class="mb-0 text-dark">Inventory</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Appointments</p>
                                <h5 class="font-weight-bolder mb-0">
                                    12
                                    <span class="text-success text-sm font-weight-bolder">+3%</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-calendar text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">New Patients</p>
                                <h5 class="font-weight-bolder mb-0">
                                    5
                                    <span class="text-success text-sm font-weight-bolder">+10%</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-paw text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Clinic Staff</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <a href="{{ route('staff.index') }}" class="text-decoration-none">
                                        {{ $staffCount ?? 0 }}
                                        <span class="text-success text-sm font-weight-bolder ml-1">
                                            <i class="fas fa-users"></i>
                                        </span>
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="fas fa-user-tie text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inventory Stats Card -->
        @if(session('tenant_user') && session('current_clinic_id') && isset($inventoryStats))
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Inventory Items</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <a href="{{ route('inventory.index') }}" class="text-decoration-none">
                                        {{ $inventoryStats['total'] ?? 0 }}
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-boxes text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Monthly Revenue</p>
                                <h5 class="font-weight-bolder mb-0">
                                    $24,300
                                    <span class="text-success text-sm font-weight-bolder">+5%</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-dollar-sign text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Upcoming Appointments</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pet</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Owner</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vet</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <i class="fas fa-cat me-3 text-primary"></i>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Whiskers</h6>
                                                <p class="text-xs text-secondary mb-0">Cat, 3 years</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">John Smith</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Dr. Johnson</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">09:30 AM</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <i class="fas fa-dog me-3 text-info"></i>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Rex</h6>
                                                <p class="text-xs text-secondary mb-0">Dog, 5 years</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Sarah Adams</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Dr. Garcia</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">10:45 AM</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <i class="fas fa-kiwi-bird me-3 text-success"></i>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Tweety</h6>
                                                <p class="text-xs text-secondary mb-0">Bird, 1 year</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Maria Lopez</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Dr. Wilson</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">12:15 PM</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Recent Activities</h6>
                </div>
                <div class="card-body p-3">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="fas fa-user-plus text-success"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">New patient registered</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">22 March 2024, 11:20 AM</p>
                                <p class="text-sm mt-3 mb-0">Rocky (Dog) has been registered with Dr. Garcia</p>
                            </div>
                        </div>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="fas fa-check-circle text-info"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Appointment completed</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">22 March 2024, 10:30 AM</p>
                                <p class="text-sm mt-3 mb-0">Whiskers had a successful check-up with Dr. Johnson</p>
                            </div>
                        </div>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="fas fa-pills text-warning"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Medication prescribed</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">22 March 2024, 9:45 AM</p>
                                <p class="text-sm mt-3 mb-0">Antibiotics prescribed for Max by Dr. Wilson</p>
                            </div>
                        </div>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="fas fa-file-invoice text-primary"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Invoice paid</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">22 March 2024, 9:00 AM</p>
                                <p class="text-sm mt-3 mb-0">Invoice #1234 for $120 has been paid by John Smith</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Any dashboard-specific JavaScript can go here
    console.log('Dashboard loaded');
    
    // Add auto-updating functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Check for updates when the page loads
        checkForUpdates();
        
        // Set interval to check for updates every 5 minutes (300000ms)
        // You can adjust this interval as needed
        setInterval(checkForUpdates, 300000);
        
        // Function to check for updates
        function checkForUpdates() {
            fetch('{{ route("updates.refresh") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.hasUpdates !== false) {
                        // If there's an update, refresh the page to show the notification
                        // or you could update a specific element to show the update notification
                        if (document.querySelector('.update-notification') === null) {
                            window.location.reload();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking for updates:', error);
                });
        }
    });
</script>
@endpush
