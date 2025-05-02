@extends('layouts.app')

@section('title', 'Admin - Subscription Requests')

@section('page-name', 'Subscription Requests Management')

@section('styles')
<style>
    .dashboard-container {
        background-color: #f8f9fc;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-3">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-clock me-1"></i>
                    Pending Subscription Requests
                </div>
                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Clinic</th>
                                    <th>Plan</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->id }}</td>
                                    <td>
                                        @if($request->user_id)
                                            {{ $request->user->name ?? 'N/A' }}
                                        @else
                                            <strong>{{ $request->guest_clinic_name }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->plan == 'free' ? 'secondary' : ($request->plan == 'basic' ? 'info' : ($request->plan == 'standard' ? 'primary' : 'dark')) }}">
                                            {{ $request->plan == 'premium' ? 'Business' : ucfirst($request->plan) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->duration }} month(s)</td>
                                    <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.subscription-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            
                                            <!-- Approve Button - Opens Modal -->
                                            <button type="button" class="btn btn-sm btn-success ms-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            
                                            <!-- Reject Button - Opens Modal -->
                                            <button type="button" class="btn btn-sm btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                        
                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">Approve Subscription Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.subscription-requests.approve', $request->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>You are approving the subscription request for <strong>{{ $request->user_id ? ($request->user->name ?? 'N/A') : $request->guest_clinic_name }}</strong>.</p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="expiration_date{{ $request->id }}" class="form-label">Expiration Date <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" id="expiration_date{{ $request->id }}" name="expiration_date" 
                                                                    value="{{ Carbon\Carbon::now()->addMonths($request->duration)->format('Y-m-d') }}" required>
                                                                <small class="text-muted">Default is {{ $request->duration }} month(s) from today</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="admin_notes{{ $request->id }}" class="form-label">Admin Notes (Optional)</label>
                                                                <textarea class="form-control" id="admin_notes{{ $request->id }}" name="admin_notes" rows="3" placeholder="Optional notes about this approval"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Approval</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="rejectModalLabel{{ $request->id }}">Reject Subscription Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.subscription-requests.reject', $request->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>You are rejecting the subscription request for <strong>{{ $request->user_id ? ($request->user->name ?? 'N/A') : $request->guest_clinic_name }}</strong>.</p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="rejection_reason{{ $request->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="rejection_reason{{ $request->id }}" name="rejection_reason" rows="3" placeholder="Provide reason for rejection" required></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="admin_notes_reject{{ $request->id }}" class="form-label">Admin Notes (Optional)</label>
                                                                <textarea class="form-control" id="admin_notes_reject{{ $request->id }}" name="admin_notes" rows="3" placeholder="Optional internal notes"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> There are no pending subscription requests at this time.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 