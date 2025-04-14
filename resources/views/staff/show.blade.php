@extends('layouts.app')

@section('title', 'Staff Details')
@section('page_name', 'Staff Details')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Staff Member Details</h6>
                            <p class="text-sm mb-0">Information about {{ $staff->name }}</p>
                        </div>
                        <div>
                            <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-primary ms-1">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('staff.index') }}" class="btn btn-sm btn-secondary ms-1">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-8">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Basic Information</h6>
                            <div class="bg-light rounded p-3 mb-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-secondary mb-1">Name</p>
                                        <p class="font-weight-bold mb-0">{{ $staff->name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-secondary mb-1">Email</p>
                                        <p class="font-weight-bold mb-0">{{ $staff->email }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-secondary mb-1">Phone</p>
                                        <p class="font-weight-bold mb-0">{{ $staff->phone ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-secondary mb-1">Role</p>
                                        <span class="badge 
                                            @if($staff->role == 'admin') 
                                                bg-gradient-primary
                                            @elseif($staff->role == 'doctor') 
                                                bg-gradient-success
                                            @elseif($staff->role == 'receptionist') 
                                                bg-gradient-info
                                            @else 
                                                bg-gradient-secondary
                                            @endif">
                                            {{ ucfirst($staff->role) }}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-secondary mb-1">Status</p>
                                        <span class="badge {{ $staff->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-md-4">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Quick Actions</h6>
                            <div class="bg-light rounded p-3">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Edit Staff Member
                                    </a>
                                    <form action="{{ route('staff.resend-invitation', $staff->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="fas fa-envelope me-2"></i>Resend Login Details
                                        </button>
                                    </form>
                                    <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="fas fa-trash me-2"></i>Delete Staff Member
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Additional Information</h6>
                            <div class="bg-light rounded p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <p class="text-xs text-secondary mb-1">Member Since</p>
                                        <p class="font-weight-bold mb-0">{{ $staff->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <p class="text-xs text-secondary mb-1">Last Updated</p>
                                        <p class="font-weight-bold mb-0">{{ $staff->updated_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <p class="text-xs text-secondary mb-1">Email Verified</p>
                                        <p class="font-weight-bold mb-0">
                                            @if($staff->email_verified_at)
                                                <span class="text-success">Yes ({{ $staff->email_verified_at->format('M d, Y') }})</span>
                                            @else
                                                <span class="text-danger">No</span>
                                            @endif
                                        </p>
                                    </div>
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