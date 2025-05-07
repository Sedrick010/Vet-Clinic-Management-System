@extends('layouts.app')

@section('title', 'Manage Clinics')

@push('css')
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Animated Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    :root {
        --primary-gradient: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        --success-gradient: linear-gradient(310deg, #2dce89 0%, #4fd1c5 100%);
        --warning-gradient: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        --danger-gradient: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        --info-gradient: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
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
    
    .badge {
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    .badge-success {
        background-color: #def7ec;
        color: #0e9f6e;
    }
    .badge-warning {
        background-color: #fef3c7;
        color: #d97706;
    }
    .badge-danger {
        background-color: #fee2e2;
        color: #dc2626;
    }
    .btn-action {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        border-radius: 0.35rem;
        transition: all 0.15s ease;
    }
    .btn-success {
        background-color: #2dce89;
        color: white;
    }
    .btn-success:hover {
        background-color: #24a46d;
    }
    .btn-danger {
        background-color: #f5365c;
        color: white;
    }
    .btn-danger:hover {
        background-color: #d03157;
    }
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Manage Veterinary Clinics</h6>
                        <p class="text-sm mb-0">View and manage all clinic registrations</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                    </a>
                </div>
                
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Filter Controls -->
                    <div class="p-4 bg-light rounded mx-4 mb-4 mt-2">
                        <form action="{{ route('admin.clinics.index') }}" method="GET" class="row align-items-end g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label text-xs text-uppercase font-weight-bolder opacity-7">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request()->query('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request()->query('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request()->query('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label text-xs text-uppercase font-weight-bolder opacity-7">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="search" name="search" class="form-control" value="{{ request()->query('search') }}" placeholder="Search clinics by name, email, or subdomain">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn bg-gradient-primary w-100 mb-0">
                                    <i class="fas fa-filter me-2"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Flash Messages -->
                    @if (session('success'))
                        <div class="alert alert-success mx-4 mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mx-4 mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Pending Registrations Section -->
                    <div class="mx-4 mb-5">
                        <h3 class="section-title">
                            <i class="fas fa-hourglass-half me-2 text-warning"></i>
                            Pending Registrations
                        </h3>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Registered On</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasPending = false; @endphp
                                    @foreach ($clinics as $clinic)
                                        @if ($clinic->approval_status === 'pending')
                                            @php $hasPending = true; @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $clinic->subdomain }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $clinic->email }}</p>
                                                    <p class="text-xs text-secondary mb-0">{{ $clinic->phone }}</p>
                                                </td>
                                                <td>
                                                    <span class="text-secondary text-xs font-weight-bold">{{ $clinic->created_at->format('M d, Y H:i') }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <form action="{{ route('admin.clinics.approve', $clinic->id) }}" method="POST" class="approve-form" data-clinic-id="{{ $clinic->id }}">
                                                            @csrf
                                                            <button type="button" class="btn btn-action btn-success" onclick="confirmApprove({{ $clinic->id }}, '{{ $clinic->name }}')">
                                                                <i class="fas fa-check me-1"></i> Approve
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-action btn-danger" onclick="openRejectModal({{ $clinic->id }})">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                        <a href="{{ route('admin.clinics.subscription.edit', $clinic) }}" class="btn btn-action btn-primary">
                                                            <i class="fas fa-cog me-1"></i> Manage
                                                        </a>
                                                        <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this clinic registration? This action cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-action btn-danger">
                                                                <i class="fas fa-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @if (!$hasPending)
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <p class="text-sm text-secondary">No pending registrations found.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Approved Clinics Section -->
                    <div class="mx-4 mb-5">
                        <h3 class="section-title">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Approved Clinics
                        </h3>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Approved On</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasApproved = false; @endphp
                                    @foreach ($clinics as $clinic)
                                        @if ($clinic->approval_status === 'approved')
                                            @php $hasApproved = true; @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $clinic->subdomain }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $clinic->email }}</p>
                                                    <p class="text-xs text-secondary mb-0">{{ $clinic->phone }}</p>
                                                </td>
                                                <td>
                                                    <span class="text-secondary text-xs font-weight-bold">{{ $clinic->updated_at->format('M d, Y H:i') }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $clinic->is_enabled ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                        {{ $clinic->is_enabled ? 'Enabled' : 'Disabled' }}
                                                    </span>
                                                    @if(!$clinic->is_enabled && $clinic->disable_reason)
                                                        <span class="d-block mt-1 text-xs text-muted" data-bs-toggle="tooltip" title="{{ $clinic->disable_reason }}">
                                                            {{ Str::limit($clinic->disable_reason, 30) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @if($clinic->is_enabled)
                                                            <button type="button" class="btn btn-action btn-warning" onclick="openDisableModal({{ $clinic->id }})">
                                                                <i class="fas fa-ban me-1"></i> Disable
                                                            </button>
                                                        @else
                                                            <form action="{{ route('admin.clinics.toggle-enabled', $clinic) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-action btn-success">
                                                                    <i class="fas fa-check-circle me-1"></i> Enable
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <button type="button" class="btn btn-action btn-danger" onclick="openRejectModal({{ $clinic->id }})">
                                                            <i class="fas fa-ban me-1"></i> Revoke
                                                        </button>
                                                        <form action="{{ route('admin.clinics.subscription.toggle', $clinic) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-action {{ $clinic->is_subscription_active ? 'btn-warning' : 'btn-success' }}">
                                                                <i class="fas {{ $clinic->is_subscription_active ? 'fa-toggle-off' : 'fa-toggle-on' }} me-1"></i> 
                                                                {{ $clinic->is_subscription_active ? 'Deactivate Sub' : 'Activate Sub' }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this approved clinic? This will permanently remove all their data and cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-action btn-danger">
                                                                <i class="fas fa-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @if (!$hasApproved)
                                        <tr>
                                            <td colspan="5" class="text-center p-4">
                                                <p class="text-sm text-secondary">No approved clinics found.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Rejected Registrations Section -->
                    <div class="mx-4">
                        <h3 class="section-title">
                            <i class="fas fa-times-circle me-2 text-danger"></i>
                            Rejected Registrations
                        </h3>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rejected On</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Reason</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasRejected = false; @endphp
                                    @foreach ($clinics as $clinic)
                                        @if ($clinic->approval_status === 'rejected')
                                            @php $hasRejected = true; @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $clinic->subdomain }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $clinic->email }}</p>
                                                    <p class="text-xs text-secondary mb-0">{{ $clinic->phone }}</p>
                                                </td>
                                                <td>
                                                    <span class="text-secondary text-xs font-weight-bold">{{ $clinic->updated_at->format('M d, Y H:i') }}</span>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-danger mb-0">{{ $clinic->rejection_reason }}</p>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <form action="{{ route('admin.clinics.approve', $clinic->id) }}" method="POST" class="approve-form" data-clinic-id="{{ $clinic->id }}">
                                                            @csrf
                                                            <button type="button" class="btn btn-action btn-success" onclick="confirmApprove({{ $clinic->id }}, '{{ $clinic->name }}')">
                                                                <i class="fas fa-check me-1"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.clinics.subscription.toggle', $clinic) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-action {{ $clinic->is_subscription_active ? 'btn-warning' : 'btn-success' }}">
                                                                <i class="fas {{ $clinic->is_subscription_active ? 'fa-toggle-off' : 'fa-toggle-on' }} me-1"></i> 
                                                                {{ $clinic->is_subscription_active ? 'Deactivate Sub' : 'Activate Sub' }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rejected clinic registration? This action cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-action btn-danger">
                                                                <i class="fas fa-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @if (!$hasRejected)
                                        <tr>
                                            <td colspan="5" class="text-center p-4">
                                                <p class="text-sm text-secondary">No rejected registrations found.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mx-4 mt-4">
                        {{ $clinics->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject/Revoke Clinic Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeRejectModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-control-label">Reason for Rejection/Revocation</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                            class="form-control"
                            placeholder="Please provide a reason for rejecting/revoking this clinic registration..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn bg-gradient-danger">Reject/Revoke</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disable Clinic Modal -->
<div class="modal fade" id="disableClinicModal" tabindex="-1" role="dialog" aria-labelledby="disableClinicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="disableForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="disableClinicModalLabel">Disable Clinic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeDisableModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="disable_reason" class="form-control-label">Reason for Disabling</label>
                        <textarea id="disable_reason" name="disable_reason" rows="3" required
                            class="form-control"
                            placeholder="Please provide a reason for disabling this clinic..."></textarea>
                        <small class="text-muted">This reason will be displayed to users when they try to access the clinic.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeDisableModal()">Cancel</button>
                    <button type="submit" class="btn bg-gradient-warning">Disable Clinic</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function confirmApprove(clinicId, clinicName) {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            return;
        }

        Swal.fire({
            title: 'Approve Clinic Registration?',
            text: `Are you sure you want to approve the registration for ${clinicName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2dce89',
            cancelButtonColor: '#f5365c',
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.querySelector(`form.approve-form[data-clinic-id="${clinicId}"]`);
                if (form) {
                    form.submit();
                }
            }
        });
    }

    function openRejectModal(clinicId) {
        document.getElementById('rejectForm').action = `/admin/clinics/${clinicId}/reject`;
        var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        myModal.show();
    }

    function closeRejectModal() {
        var myModalEl = document.getElementById('rejectModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();
    }

    function openDisableModal(clinicId) {
        document.getElementById('disableForm').action = `/admin/clinics/${clinicId}/toggle-enabled`;
        var myModal = new bootstrap.Modal(document.getElementById('disableClinicModal'));
        myModal.show();
    }

    function closeDisableModal() {
        var myModalEl = document.getElementById('disableClinicModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();
    }
</script>
@endpush 