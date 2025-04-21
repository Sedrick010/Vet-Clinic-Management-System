@extends('layouts.app')

@section('title', 'Admin - Subscription Requests')

@section('page-name', 'Subscription Requests Management')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
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
                                    <th>Guest Info</th>
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
                                        <span class="badge bg-{{ $request->plan == 'basic' ? 'secondary' : ($request->plan == 'standard' ? 'info' : 'primary') }}">
                                            {{ ucfirst($request->plan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(!$request->user_id)
                                            <small>
                                                <strong>Email:</strong> {{ $request->guest_email }}<br>
                                                <strong>Phone:</strong> {{ $request->guest_phone }}
                                            </small>
                                        @else
                                            <span class="text-muted">Registered User</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->duration }} month(s)</td>
                                    <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.subscription-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Processed Subscription Requests
                </div>
                <div class="card-body">
                    @if($processedRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Clinic</th>
                                    <th>Plan</th>
                                    <th>Guest Info</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Processed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedRequests as $request)
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
                                        <span class="badge bg-{{ $request->plan == 'basic' ? 'secondary' : ($request->plan == 'standard' ? 'info' : 'primary') }}">
                                            {{ ucfirst($request->plan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(!$request->user_id)
                                            <small>
                                                <strong>Email:</strong> {{ $request->guest_email }}<br>
                                                <strong>Phone:</strong> {{ $request->guest_phone }}
                                            </small>
                                        @else
                                            <span class="text-muted">Registered User</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->duration }} month(s)</td>
                                    <td>₱{{ number_format($request->amount_paid, 2) }}</td>
                                    <td>
                                        @if($request->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->status == 'approved')
                                            {{ Carbon\Carbon::parse($request->approved_at)->format('M d, Y') }}
                                        @elseif($request->status == 'rejected')
                                            {{ Carbon\Carbon::parse($request->rejected_at)->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscription-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            {{ $processedRequests->links() }}
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> There are no processed subscription requests to display.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 