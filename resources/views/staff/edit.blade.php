@extends('layouts.app')

@section('title', 'Edit Staff Member')
@section('page_name', 'Edit Staff Member')

@section('content')
<div class="container-fluid py-4">
    <!-- Info Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <strong>Editing Staff</strong> - Update the information for this staff member. All fields marked with an asterisk (*) are required.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Edit Staff Member</h6>
                        <p class="text-sm mb-0">Update details for {{ $staff->name }}</p>
                    </div>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to Staff List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.update', $staff->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $staff->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-control-label">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-control-label">Contact Number *</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $staff->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dob" class="form-control-label">Date of Birth *</label>
                                    <input type="date" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob', $staff->dob) }}" required>
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender" class="form-control-label">Gender *</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $staff->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $staff->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $staff->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id" class="form-control-label">Staff ID / Employee Number *</label>
                                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" value="{{ old('employee_id', $staff->employee_id) }}" required>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4 mb-3">Address Information</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address" class="form-control-label">Address *</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $staff->address) }}" required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="form-control-label">City *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $staff->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="form-control-label">State/Province *</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $staff->state) }}" required>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code" class="form-control-label">Postal Code *</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $staff->postal_code) }}" required>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employment Information -->
                        <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4 mb-3">Employment Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-control-label">Position / Role *</label>
                                    <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="vet" {{ old('role', $staff->role) == 'vet' ? 'selected' : '' }}>Veterinarian</option>
                                        <option value="vet_assistant" {{ old('role', $staff->role) == 'vet_assistant' ? 'selected' : '' }}>Vet Assistant</option>
                                        <option value="receptionist" {{ old('role', $staff->role) == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                        <option value="admin" {{ old('role', $staff->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="staff" {{ old('role', $staff->role) == 'staff' ? 'selected' : '' }}>Other Staff</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hire_date" class="form-control-label">Date Hired / Start Date *</label>
                                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror" id="hire_date" name="hire_date" value="{{ old('hire_date', $staff->hire_date) }}" required>
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="specialization" class="form-control-label">Specialization</label>
                                    <select class="form-control @error('specialization') is-invalid @enderror" id="specialization" name="specialization">
                                        <option value="">Select Specialization (if applicable)</option>
                                        <option value="small_animals" {{ old('specialization', $staff->specialization) == 'small_animals' ? 'selected' : '' }}>Small Animals</option>
                                        <option value="exotic_pets" {{ old('specialization', $staff->specialization) == 'exotic_pets' ? 'selected' : '' }}>Exotic Pets</option>
                                        <option value="large_animals" {{ old('specialization', $staff->specialization) == 'large_animals' ? 'selected' : '' }}>Large Animals</option>
                                        <option value="avian" {{ old('specialization', $staff->specialization) == 'avian' ? 'selected' : '' }}>Avian</option>
                                        <option value="dentistry" {{ old('specialization', $staff->specialization) == 'dentistry' ? 'selected' : '' }}>Dentistry</option>
                                        <option value="cardiology" {{ old('specialization', $staff->specialization) == 'cardiology' ? 'selected' : '' }}>Cardiology</option>
                                        <option value="dermatology" {{ old('specialization', $staff->specialization) == 'dermatology' ? 'selected' : '' }}>Dermatology</option>
                                        <option value="surgery" {{ old('specialization', $staff->specialization) == 'surgery' ? 'selected' : '' }}>Surgery</option>
                                    </select>
                                    <small class="form-text text-muted">Only applicable for veterinarians</small>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_number" class="form-control-label">License Number</label>
                                    <input type="text" class="form-control @error('license_number') is-invalid @enderror" id="license_number" name="license_number" value="{{ old('license_number', $staff->license_number) }}">
                                    <small class="form-text text-muted">Required for veterinarians only</small>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Update Staff Member
                                </button>
                                <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 