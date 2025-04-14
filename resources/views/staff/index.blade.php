@extends('layouts.app')

@section('title', 'Staff Management')
@section('page_name', 'Staff Management')

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
                            <h6 class="mb-0">Staff Members</h6>
                            <p class="text-sm mb-0">Manage your clinic staff accounts</p>
                            @if(app()->environment('local'))
                            <p class="text-xs text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Your role: {{ session('tenant_user')->role ?? 'Unknown' }} 
                                (Management permission: {{ request()->attributes->get('can_manage_staff', false) ? 'Yes' : 'No' }})
                            </p>
                            @endif
                        </div>
                        @if(request()->attributes->get('can_manage_staff', false))
                        <a href="{{ route('staff.create') }}" class="btn bg-gradient-primary">
                            <i class="fas fa-user-plus me-2"></i>Add Staff Member
                        </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                            <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                            <span class="alert-text">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
                            <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                            <span class="alert-text">{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($staff->isEmpty())
                        <div class="text-center py-5">
                            <div class="icon icon-shape icon-md shadow rounded-circle bg-light mx-auto mb-3">
                                <i class="fas fa-users text-primary opacity-10"></i>
                            </div>
                            <h6 class="text-primary">No staff members found</h6>
                            <p class="text-sm text-secondary">Add your first staff member to get started</p>
                            <a href="{{ route('staff.create') }}" class="btn btn-sm bg-gradient-primary mt-3">
                                <i class="fas fa-user-plus me-2"></i>Add Your First Staff Member
                            </a>
                        </div>
                    @else
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Staff Member</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staff as $member)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="icon-sm me-3 bg-gradient-primary shadow text-center rounded-circle">
                                                        <i class="fas fa-user text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $member->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $member->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($member->role == 'admin') 
                                                        bg-gradient-primary
                                                    @elseif($member->role == 'doctor') 
                                                        bg-gradient-success
                                                    @elseif($member->role == 'receptionist') 
                                                        bg-gradient-info
                                                    @else 
                                                        bg-gradient-secondary
                                                    @endif">
                                                    {{ ucfirst($member->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $member->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="{{ route('staff.show', $member->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if(request()->attributes->get('can_manage_staff', false))
                                                <a href="{{ route('staff.edit', $member->id) }}" class="btn btn-sm btn-primary ms-1" data-bs-toggle="tooltip" title="Edit Staff">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('staff.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger ms-1" data-bs-toggle="tooltip" title="Delete Staff">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                
                                                <div class="dropdown d-inline">
                                                    <button class="btn btn-sm btn-secondary ms-1 dropdown-toggle" type="button" id="moreActions{{ $member->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        More
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="moreActions{{ $member->id }}">
                                                        <li>
                                                            <form action="{{ route('staff.resend-invitation', $member->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-envelope text-success me-2"></i>Resend Invitation
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @if(app()->environment('local'))
                                                        <li>
                                                            <form action="{{ route('staff.reset-password', $member->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-key text-warning me-2"></i>Reset Password
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 