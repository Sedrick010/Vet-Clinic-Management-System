@extends('layouts.app')

@section('title', 'Edit Staff Member')
@section('page_name', 'Edit Staff Member')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Edit Staff Member</h6>
                    <p class="text-sm mb-0">Update staff account information</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="px-4 py-3">
                        <form method="POST" action="{{ route('staff.update', ['id' => $staff->id]) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Name</label>
                                        <input id="name" class="form-control" type="text" name="name" value="{{ old('name', $staff->name) }}" required autofocus>
                                        @error('name')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email</label>
                                        <input id="email" class="form-control" type="email" name="email" value="{{ old('email', $staff->email) }}" required>
                                        @error('email')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Phone -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Phone</label>
                                        <input id="phone" class="form-control" type="text" name="phone" value="{{ old('phone', $staff->phone) }}">
                                        @error('phone')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role" class="form-control-label">Role</label>
                                        <select id="role" name="role" class="form-control">
                                            <option value="admin" {{ old('role', $staff->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="doctor" {{ old('role', $staff->role) == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                            <option value="receptionist" {{ old('role', $staff->role) == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                            <option value="assistant" {{ old('role', $staff->role) == 'assistant' ? 'selected' : '' }}>Assistant</option>
                                        </select>
                                        @error('role')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $staff->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active Account</label>
                                <p class="text-sm text-muted mt-1">Inactive accounts cannot log in to the system.</p>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('staff.index') }}" class="btn btn-light me-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn bg-gradient-primary">
                                    <i class="fas fa-save me-1"></i> Update Staff Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card mb-4">
                <div class="card-header pb-0 bg-light-danger">
                    <h6 class="mb-0 text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-sm mb-0">Once you delete a staff member, there is no going back. This action cannot be undone.</p>
                        </div>
                        <form action="{{ route('staff.destroy', ['id' => $staff->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete Staff Member
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 