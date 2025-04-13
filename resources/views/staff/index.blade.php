@extends('layouts.app')

@section('title', 'Staff Management')
@section('page_name', 'Staff Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Info Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <strong>Staff Management</strong> - Add veterinarians, technicians, receptionists, and other clinic staff members here. Each staff member will have their own login credentials to access the system.
            </div>
        </div>
    </div>
    
    <!-- Display credentials if available in session -->
    @if(session()->has('staff_credentials'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success pb-0">
                    <h6 class="text-white mb-0">Staff Member Added Successfully</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success mb-3">
                        <strong>The following login credentials have been generated:</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Username/Email:</th>
                                <td><strong>{{ session('staff_credentials.email') }}</strong></td>
                            </tr>
                            <tr>
                                <th>Temporary Password:</th>
                                <td><strong>{{ session('staff_credentials.password') }}</strong></td>
                            </tr>
                            <tr>
                                <th>Login URL:</th>
                                <td><a href="{{ session('staff_credentials.login_url') }}" target="_blank">{{ session('staff_credentials.login_url') }}</a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> In a production environment, these credentials would be automatically emailed to the staff member. For demonstration purposes, they are displayed here.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Staff Members</h6>
                        <p class="text-sm mb-0">Manage your clinic staff</p>
                    </div>
                    <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i> Add New Staff
                    </a>
                </div>
                
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Staff ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact Info</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Position</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Specialization</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $member)
                                <tr>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0 ps-3">{{ $member->employee_id ?? 'N/A' }}</p>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $member->name }}</h6>
                                                @if($member->role == 'vet' && !empty($member->license_number))
                                                <p class="text-xs text-secondary mb-0">License: {{ $member->license_number }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $member->email }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $member->phone ?? 'No phone' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            $roleColors = [
                                                'vet' => 'success',
                                                'vet_assistant' => 'info',
                                                'receptionist' => 'primary',
                                                'admin' => 'danger',
                                                'staff' => 'secondary',
                                                'owner' => 'warning'
                                            ];
                                            $roleDisplay = [
                                                'vet' => 'Veterinarian',
                                                'vet_assistant' => 'Vet Assistant',
                                                'receptionist' => 'Receptionist',
                                                'admin' => 'Admin',
                                                'staff' => 'Staff',
                                                'owner' => 'Owner'
                                            ];
                                            $color = $roleColors[$member->role] ?? 'secondary';
                                            $display = $roleDisplay[$member->role] ?? ucfirst($member->role);
                                        @endphp
                                        <span class="badge badge-sm bg-gradient-{{ $color }}">
                                            {{ $display }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if(!empty($member->specialization))
                                            @php
                                                $specializationDisplay = [
                                                    'small_animals' => 'Small Animals',
                                                    'exotic_pets' => 'Exotic Pets',
                                                    'large_animals' => 'Large Animals',
                                                    'avian' => 'Avian',
                                                    'dentistry' => 'Dentistry',
                                                    'cardiology' => 'Cardiology',
                                                    'dermatology' => 'Dermatology',
                                                    'surgery' => 'Surgery'
                                                ];
                                                $display = $specializationDisplay[$member->specialization] ?? ucfirst($member->specialization);
                                            @endphp
                                            <span class="badge badge-sm bg-gradient-info">{{ $display }}</span>
                                        @else
                                            <span class="text-xs text-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('staff.edit', $member->id) }}" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('staff.destroy', $member->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this staff member?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-sm text-secondary mb-0">No staff members found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 