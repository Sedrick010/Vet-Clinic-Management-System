@extends('layouts.app')

@section('title', $clinicName . ' - Dashboard')
@section('page_name', $clinicName . ' Dashboard')

@section('content')
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

<!-- New Feature Notification -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="icon icon-sm me-3">
                    <i class="fas fa-info-circle text-white"></i>
                </div>
                <div>
                    <span class="fw-bold">New Feature:</span> Staff Management is now available! Easily add, edit, and manage clinic staff members. <a href="{{ route('staff.index') }}" class="alert-link text-white text-decoration-underline">Try it now →</a>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <h6>Quick Actions</h6>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="#" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body p-3 text-center">
                                <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-info">
                                    <i class="fas fa-calendar-plus text-white opacity-10"></i>
                                </div>
                                <h6 class="mb-0 text-dark">New Appointment</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="#" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body p-3 text-center">
                                <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-success">
                                    <i class="fas fa-paw text-white opacity-10"></i>
                                </div>
                                <h6 class="mb-0 text-dark">Register Patient</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('staff.index') }}" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body p-3 text-center">
                                <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-primary">
                                    <i class="fas fa-user-tie text-white opacity-10"></i>
                                </div>
                                <h6 class="mb-0 text-dark">Staff Management</h6>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Inventory Management Quick Access -->
                    @if(session('tenant_user') && session('current_clinic_id'))
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('inventory.index') }}" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body p-3 text-center">
                                <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-info">
                                    <i class="fas fa-boxes text-white opacity-10"></i>
                                </div>
                                <h6 class="mb-0 text-dark">Inventory</h6>
                            </div>
                        </a>
                    </div>
                    @endif
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="#" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body p-3 text-center">
                                <div class="icon icon-shape icon-md shadow rounded-circle mx-auto mb-3 bg-gradient-warning">
                                    <i class="fas fa-chart-bar text-white opacity-10"></i>
                                </div>
                                <h6 class="mb-0 text-dark">Reports</h6>
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
@endsection

@push('js')
<script>
    // Any dashboard-specific JavaScript can go here
    console.log('Dashboard loaded');
</script>
@endpush
