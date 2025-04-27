@extends('layouts.app')

@section('page_name', 'Add New Pet')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Add New Pet</h6>
                    <a href="{{ route('pets.index') }}" class="btn btn-sm bg-gradient-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Pets
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger text-white">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('pets.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Pet Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="owner_id" class="form-control-label">Owner <span class="text-danger">*</span></label>
                                    <select class="form-select @error('owner_id') is-invalid @enderror" id="owner_id" name="owner_id" required>
                                        <option value="">Select Owner</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('owner_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('owner_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="species" class="form-control-label">Species <span class="text-danger">*</span></label>
                                    <select class="form-select @error('species') is-invalid @enderror" id="species" name="species" required>
                                        <option value="">Select Species</option>
                                        <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                                        <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
                                        <option value="Bird" {{ old('species') == 'Bird' ? 'selected' : '' }}>Bird</option>
                                        <option value="Reptile" {{ old('species') == 'Reptile' ? 'selected' : '' }}>Reptile</option>
                                        <option value="Other" {{ old('species') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('species')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="breed" class="form-control-label">Breed</label>
                                    <input type="text" class="form-control @error('breed') is-invalid @enderror" id="breed" name="breed" value="{{ old('breed') }}">
                                    @error('breed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender" class="form-control-label">Gender</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="unknown" {{ old('gender') == 'unknown' ? 'selected' : '' }}>Unknown</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birthdate" class="form-control-label">Birthdate</label>
                                    <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate') }}">
                                    @error('birthdate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes" class="form-control-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('pets.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn bg-gradient-primary">Save Pet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 