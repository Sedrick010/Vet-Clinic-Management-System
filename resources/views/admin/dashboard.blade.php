@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('css')
<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .icon-shape {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-gradient-primary {
        background-image: linear-gradient(310deg, #7928CA 0%, #FF0080 100%);
    }
    .bg-gradient-success {
        background-image: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%);
    }
    .bg-gradient-warning {
        background-image: linear-gradient(310deg, #f53939 0%, #fbcf33 100%);
    }
    .bg-gradient-danger {
        background-image: linear-gradient(310deg, #ea0606 0%, #ff667c 100%);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">VetClinic Admin Dashboard</h6>
                    <p class="text-sm mb-0">Veterinary Clinic Management System</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalClinics }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-hospital-alt text-lg opacity-10" aria-hidden="true"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Approvals</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $pendingClinics }}
                                    @if($pendingClinics > 0)
                                    <span class="text-warning text-sm font-weight-bolder">Needs Review</span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="fas fa-hourglass-half text-lg opacity-10" aria-hidden="true"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Approved Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $approvedClinics }}
                                    <span class="text-success text-sm font-weight-bolder">Active</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-check-circle text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Rejected Clinics</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $rejectedClinics }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="fas fa-times-circle text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Overview -->
    <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card z-index-2">
                <div class="card-header pb-0">
                    <h6>Clinic Registration Overview</h6>
                    <p class="text-sm">
                        <i class="fa fa-arrow-up text-success"></i>
                        <span class="font-weight-bold">Current statistics</span> of registered clinics
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group">
                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center">
                                    <i class="fas fa-clinic-medical text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">View All Clinics</h6>
                                    <span class="text-xs">See all registered clinics in the system</span>
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
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-warning shadow text-center">
                                    <i class="fas fa-hourglass-half text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">Pending Approvals</h6>
                                    <span class="text-xs">Review clinics waiting for approval</span>
                                </div>
                            </div>
                            <div class="d-flex">
                                <a href="{{ route('admin.clinics.index') }}?status=pending" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
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
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Latest Activity</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Details</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
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
                                        <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit user">
                                            Details
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
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<script>
    var ctx = document.getElementById("chart-line").getContext("2d");
    var gradientStroke = ctx.createLinearGradient(0, 230, 0, 50);
    
    gradientStroke.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke.addColorStop(0, 'rgba(94, 114, 228, 0)');
    
    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
                label: "Registered Clinics",
                tension: 0.4,
                borderWidth: 0,
                pointRadius: 0,
                borderColor: "#5e72e4",
                backgroundColor: gradientStroke,
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
                pointRadius: 0,
                borderColor: "#2dce89",
                backgroundColor: gradientStroke,
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
                    display: false,
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
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        color: '#fbfbfb',
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
                        color: '#ccc',
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
</script>
@endpush 