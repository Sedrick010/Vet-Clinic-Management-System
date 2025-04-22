@extends('layouts.app')

@section('title', isset($readOnly) && $readOnly ? 'Clinic Information' : 'Clinic Profile Settings')
@section('page_name', isset($readOnly) && $readOnly ? 'Clinic Information' : 'Clinic Profile Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <h5 class="mb-4">{{ isset($readOnly) && $readOnly ? 'Clinic Information' : 'Clinic Profile Settings' }}</h5>
                
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(isset($readOnly) && $readOnly)
                <div class="row">
                    <!-- Logo Section -->
                    <div class="col-md-3 text-center">
                        <div class="mb-3">
                            <div class="position-relative">
                                <img src="{{ $clinic->getLogoUrl() }}" alt="{{ $clinic->name }}" class="img-fluid rounded shadow mb-3" style="max-height: 150px; width: auto;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="row">
                            <!-- Clinic Name -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-control-label fw-bold">Clinic Name</label>
                                    <p>{{ $clinic->name }}</p>
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-control-label fw-bold">Email Address</label>
                                    <p>{{ $clinic->email }}</p>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-control-label fw-bold">Phone Number</label>
                                    <p>{{ $clinic->phone }}</p>
                                </div>
                            </div>
                            
                            <!-- Subdomain -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-control-label fw-bold">Clinic URL</label>
                                    <p>{{ $clinic->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</p>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-control-label fw-bold">Address</label>
                                    <p>{{ $clinic->address }}</p>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="form-group mb-4">
                                    <label class="form-control-label fw-bold">Description</label>
                                    <p>{{ $clinic->description ?: 'No description available.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <form action="{{ route('clinic.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Logo Upload Section -->
                        <div class="col-md-3 text-center">
                            <div class="mb-3">
                                <div class="position-relative">
                                    <img src="{{ $clinic->getLogoUrl() }}" alt="{{ $clinic->name }}" class="img-fluid rounded shadow mb-3" style="max-height: 150px; width: auto;" id="logo-preview">
                                    
                                    <input type="file" name="logo" id="logo-upload" class="d-none" accept="image/*">
                                    
                                    <label for="logo-upload" class="btn btn-sm btn-primary position-relative">
                                        <i class="fas fa-upload me-1"></i> Change Logo
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Click to upload a logo. Recommended size: 200x200px.
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <!-- Clinic Name -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-control-label">Clinic Name</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $clinic->name) }}" required>
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-control-label">Email Address</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $clinic->email) }}" required>
                                    </div>
                                </div>
                                
                                <!-- Phone -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-control-label">Phone Number</label>
                                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $clinic->phone) }}" required>
                                    </div>
                                </div>
                                
                                <!-- Subdomain (Read-only) -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="subdomain" class="form-control-label">Clinic URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text">https://</span>
                                            <input type="text" id="subdomain" class="form-control" value="{{ $clinic->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}" readonly>
                                        </div>
                                        <small class="form-text text-muted">Your clinic's custom URL cannot be changed.</small>
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="address" class="form-control-label">Address</label>
                                        <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $clinic->address) }}" required>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="description" class="form-control-label">Description</label>
                                        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $clinic->description) }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
@if(!isset($readOnly) || !$readOnly)
<script>
    // Show image preview when a new logo is selected
    document.getElementById('logo-upload').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endif
@endpush 