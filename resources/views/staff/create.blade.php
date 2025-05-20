@extends('layouts.app')

@section('title', 'Add New Staff Member')
@section('page_name', 'Add New Staff Member')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Add New Staff Member</h6>
                    <p class="text-sm mb-0">Create a new staff account for your clinic</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="px-4 py-3">
                        <form method="POST" action="{{ route('staff.store') }}">
                            @csrf

                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Name</label>
                                        <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus>
                                        @error('name')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email</label>
                                        <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required>
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
                                        <input id="phone" class="form-control" type="text" name="phone" value="{{ old('phone') }}">
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
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                            <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                            <option value="assistant" {{ old('role') == 'assistant' ? 'selected' : '' }}>Assistant</option>
                                        </select>
                                        @error('role')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="text-sm text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        An invitation email with login details will be sent to the staff member.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('staff.index') }}" class="btn btn-light me-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn bg-gradient-primary">
                                    <i class="fas fa-user-plus me-1"></i> Add Staff Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 