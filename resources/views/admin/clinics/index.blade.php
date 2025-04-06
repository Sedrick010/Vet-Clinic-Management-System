@extends('layouts.app')

@section('title', 'Manage Clinics')

@push('css')
<style>
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

                    <!-- Clinics Table -->
                    <div class="table-responsive p-0 mx-4">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Registered On</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clinics as $clinic)
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
                                            @if ($clinic->approval_status === 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif ($clinic->approval_status === 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif ($clinic->approval_status === 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @if ($clinic->rejection_reason)
                                                    <p class="text-xs text-danger mt-1">{{ $clinic->rejection_reason }}</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-secondary text-xs font-weight-bold">{{ $clinic->created_at->format('M d, Y H:i') }}</span>
                                        </td>
                                        <td>
                                            @if ($clinic->approval_status === 'pending')
                                                <div class="d-flex">
                                                    <form action="{{ route('admin.clinics.approve', $clinic->id) }}" method="POST" class="me-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-action btn-success">
                                                            <i class="fas fa-check me-1"></i> Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-action btn-danger" onclick="openRejectModal({{ $clinic->id }})">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </div>
                                            @elseif ($clinic->approval_status === 'rejected')
                                                <form action="{{ route('admin.clinics.approve', $clinic->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-action btn-success">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to delete this clinic registration? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-action btn-danger">
                                                        <i class="fas fa-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-4">
                                            <p class="text-sm text-secondary">No clinics found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                <h5 class="modal-title" id="rejectModalLabel">Reject Clinic Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeRejectModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-control-label">Reason for Rejection</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                            class="form-control"
                            placeholder="Please provide a reason for rejecting this clinic registration..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn bg-gradient-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    function openRejectModal(clinicId) {
        document.getElementById('rejectForm').action = `/admin/clinics/${clinicId}/reject`;
        var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        myModal.show();
    }

    function closeRejectModal() {
        var myModal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        if (myModal) {
            myModal.hide();
        }
    }
</script>
@endpush 