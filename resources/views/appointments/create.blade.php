@extends('layouts.app')

@section('title', 'Create Appointment')
@section('page_name', 'Create Appointment')

@php
    $isSidebar = true;
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Create New Appointment</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form id="appointmentForm" method="POST" action="{{ route('appointments.store') }}">
                        @csrf
                        
                        <!-- Toggle Switch for New Pet -->
                        <div class="form-group mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="newPetToggle" name="newPetToggle">
                                <label class="custom-control-label" for="newPetToggle">New Pet</label>
                            </div>
                        </div>

                        <!-- Existing Pet Selection Section -->
                        <div id="existingPetSection">
                            <div class="form-group mb-3">
                                <label for="pet_name">Select or Enter Pet Name</label>
                                <select class="form-control @error('pet_id') is-invalid @enderror" 
                                       id="pet_id" name="pet_id" required>
                                    <option value="">Select a Pet</option>
                                    @foreach($pets as $pet)
                                        <option value="{{ $pet->id }}">{{ $pet->name }} (Owner: {{ $pet->owner->name }})</option>
                                    @endforeach
                                </select>
                                @error('pet_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- New Pet Details Section -->
                        <div id="newPetSection" style="display: none;">
                            <div class="form-group mb-3">
                                <label for="new_pet_name">Pet Name</label>
                                <input type="text" class="form-control @error('new_pet_name') is-invalid @enderror" 
                                       id="new_pet_name" name="new_pet_name" value="{{ old('new_pet_name') }}">
                                @error('new_pet_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="owner_name">Owner Name</label>
                                <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                                       id="owner_name" name="owner_name" value="{{ old('owner_name') }}">
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="owner_phone">Owner Phone</label>
                                <input type="text" class="form-control @error('owner_phone') is-invalid @enderror" 
                                       id="owner_phone" name="owner_phone" value="{{ old('owner_phone') }}">
                                @error('owner_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="species">Species</label>
                                <input type="text" class="form-control @error('species') is-invalid @enderror" 
                                       id="species" name="species" value="{{ old('species') }}">
                                @error('species')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="gender">Gender</label>
                                <select class="form-control @error('gender') is-invalid @enderror" 
                                        id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="unknown" {{ old('gender') == 'unknown' ? 'selected' : '' }}>Unknown</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="breed">Breed (Optional)</label>
                                <input type="text" class="form-control @error('breed') is-invalid @enderror" 
                                       id="breed" name="breed" value="{{ old('breed') }}">
                                @error('breed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Veterinarian Selection -->
                        <div class="form-group mb-3">
                            <label for="veterinarian_id">Select Veterinarian</label>
                            <select class="form-control @error('veterinarian_id') is-invalid @enderror" 
                                   id="veterinarian_id" name="veterinarian_id">
                                <option value="">Select a Veterinarian</option>
                                @foreach($veterinarians as $vet)
                                    <option value="{{ $vet->id }}" {{ old('veterinarian_id') == $vet->id ? 'selected' : '' }}>{{ $vet->name }}</option>
                                @endforeach
                            </select>
                            @error('veterinarian_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Appointment Details -->
                        <div class="form-group mb-3">
                            <label for="start_time">Start Time</label>
                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                   id="start_time" name="start_time" value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="end_time">End Time</label>
                            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" name="end_time" value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="reason">Reason for Visit</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('appointments.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary ms-2">Create Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const form = document.getElementById('appointmentForm');
        const newPetToggle = document.getElementById('newPetToggle');
        const existingPetSection = document.getElementById('existingPetSection');
        const newPetSection = document.getElementById('newPetSection');
        const petSelect = document.getElementById('pet_id');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const veterinarianSelect = document.getElementById('veterinarian_id');
        const reasonInput = document.getElementById('reason');
        const genderSelect = document.getElementById('gender');

        // Initialize Select2
        $('#pet_id').select2({
            placeholder: 'Select a Pet'
        });

        $('#veterinarian_id').select2({
            placeholder: 'Select a Veterinarian'
        });

        // Set minimum date for appointment times
        const now = new Date();
        const formattedNow = now.toISOString().slice(0, 16);
        startTimeInput.min = formattedNow;
        endTimeInput.min = formattedNow;

        // Toggle pet sections
        function togglePetSections() {
            const isNewPet = newPetToggle.checked;
            existingPetSection.style.display = isNewPet ? 'none' : 'block';
            newPetSection.style.display = isNewPet ? 'block' : 'none';

            // Reset validation classes
            const allInputs = form.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                input.classList.remove('is-invalid');
            });

            // Toggle required fields
            if (isNewPet) {
                petSelect.removeAttribute('required');
                $('#pet_id').val(null).trigger('change');
                newPetSection.querySelectorAll('input, select').forEach(input => {
                    if (input.id !== 'breed') {
                        input.setAttribute('required', 'required');
                    }
                });
            } else {
                petSelect.setAttribute('required', 'required');
                newPetSection.querySelectorAll('input, select').forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            }
        }

        // Initialize toggle state and add listener
        togglePetSections();
        newPetToggle.addEventListener('change', togglePetSections);

        // Form validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Reset validation classes
            const allInputs = form.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                input.classList.remove('is-invalid');
            });

            // Validate required fields based on new/existing pet
            if (newPetToggle.checked) {
                // Validate new pet fields
                newPetSection.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                });
            } else {
                // Validate existing pet selection
                if (!petSelect.value) {
                    petSelect.classList.add('is-invalid');
                    isValid = false;
                }
            }

            // Validate veterinarian
            if (!veterinarianSelect.value) {
                veterinarianSelect.classList.add('is-invalid');
                isValid = false;
            }

            // Validate appointment times
            if (!startTimeInput.value) {
                startTimeInput.classList.add('is-invalid');
                isValid = false;
            }
            if (!endTimeInput.value) {
                endTimeInput.classList.add('is-invalid');
                isValid = false;
            }
            if (startTimeInput.value && endTimeInput.value && startTimeInput.value >= endTimeInput.value) {
                endTimeInput.classList.add('is-invalid');
                isValid = false;
            }

            // Validate reason
            if (!reasonInput.value.trim()) {
                reasonInput.classList.add('is-invalid');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            }
        });

        // Handle end time validation
        startTimeInput.addEventListener('change', function() {
            endTimeInput.min = this.value;
            if (endTimeInput.value && endTimeInput.value <= this.value) {
                endTimeInput.value = '';
            }
        });
    });
</script>
@endpush 