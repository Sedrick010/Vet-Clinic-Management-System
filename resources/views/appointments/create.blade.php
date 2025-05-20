@extends('layouts.app')

@section('title', 'Create Appointment')
@section('page_name', 'Create Appointment')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Create New Appointment</h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <form id="appointmentForm" method="POST" action="{{ route('appointments.store') }}">
                        @csrf
                        
                        <!-- Client Selection -->
                        <div class="form-group mb-4">
                            <label for="client_id" class="form-label fw-bold">Select Client <span class="text-danger">*</span></label>
                            <select class="form-control @error('client_id') is-invalid @enderror" 
                                   id="client_id" name="client_id" required>
                                <option value="">-- Select a Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} {{ $client->email ? '('.$client->email.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">If the client is not listed, please <a href="{{ route('clients.create') }}" target="_blank">add a new client</a> first.</small>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Hidden client name field to maintain compatibility -->
                            <input type="hidden" id="client_name" name="client_name" value="{{ old('client_name') }}">
                        </div>
                        
                        <!-- Pet Selection -->
                        <div class="form-group mb-4">
                            <label for="pet_id" class="form-label fw-bold">Select Pet <span class="text-danger">*</span></label>
                            <select class="form-control @error('pet_id') is-invalid @enderror" 
                                   id="pet_id" name="pet_id" required>
                                <option value="">-- Select a Client First --</option>
                            </select>
                            <small class="form-text text-muted">If the pet is not listed, please <a href="{{ route('pets.create') }}" target="_blank">add a new pet</a> first.</small>
                            @error('pet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div class="form-group mb-4">
                            <label for="staff_id" class="form-label fw-bold">Select Doctor <span class="text-danger">*</span></label>
                            <select class="form-control @error('staff_id') is-invalid @enderror" 
                                   id="staff_id" name="staff_id" required>
                                <option value="">-- Select a Doctor --</option>
                                @foreach($staff as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('staff_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('staff_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Appointment Time -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="start_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="start_time" class="form-label fw-bold">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Duration and Type -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="duration" class="form-label fw-bold">Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" value="{{ old('duration', 30) }}" min="5" required>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="appointment_type" class="form-label fw-bold">Appointment Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('appointment_type') is-invalid @enderror" 
                                       id="appointment_type" name="appointment_type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="check-up" {{ old('appointment_type') == 'check-up' ? 'selected' : '' }}>Check Up</option>
                                    <option value="vaccination" {{ old('appointment_type') == 'vaccination' ? 'selected' : '' }}>Vaccination</option>
                                    <option value="surgery" {{ old('appointment_type') == 'surgery' ? 'selected' : '' }}>Surgery</option>
                                    <option value="consultation" {{ old('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                    <option value="emergency" {{ old('appointment_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="follow-up" {{ old('appointment_type') == 'follow-up' ? 'selected' : '' }}>Follow Up</option>
                                    <option value="grooming" {{ old('appointment_type') == 'grooming' ? 'selected' : '' }}>Grooming</option>
                                    <option value="other" {{ old('appointment_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('appointment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                   id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="no-show" {{ old('status') == 'no-show' ? 'selected' : '' }}>No Show</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reason for Visit -->
                        <div class="form-group mb-4">
                            <label for="reason" class="form-label fw-bold">Reason for Visit <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                   id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                   id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('appointments.index') }}" class="btn btn-light m-0">Cancel</a>
                            <button type="submit" class="btn bg-gradient-primary m-0 ms-2">Create Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set minimum date for appointment date field to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('start_date').value = today;
        
        // Calculate estimated end time when duration or start time changes
        function updateEndTimeEstimate() {
            const startDate = document.getElementById('start_date').value;
            const startTime = document.getElementById('start_time').value;
            const duration = parseInt(document.getElementById('duration').value) || 30;
            
            if (startDate && startTime && duration) {
                // Create a start datetime object
                const startDateTime = new Date(`${startDate}T${startTime}`);
                
                // Add duration minutes
                const endDateTime = new Date(startDateTime.getTime() + duration * 60000);
                
                // Format end time
                const endTime = endDateTime.toTimeString().slice(0, 5);
                
                // Display estimated end time (optional)
                if (document.getElementById('end_time_estimate')) {
                    document.getElementById('end_time_estimate').textContent = endTime;
                }
            }
        }
        
        // Add event listeners for duration and time fields
        document.getElementById('start_date').addEventListener('change', updateEndTimeEstimate);
        document.getElementById('start_time').addEventListener('change', updateEndTimeEstimate);
        document.getElementById('duration').addEventListener('input', updateEndTimeEstimate);
        
        // Client selection change handler
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        const clientNameInput = document.getElementById('client_name');
        
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            
            // Update the hidden client_name field with the selected client's name
            if (clientId) {
                const selectedOption = this.options[this.selectedIndex];
                const clientName = selectedOption.text.split(' (')[0]; // Get just the name part
                clientNameInput.value = clientName;
                
                // Fetch pets for the selected client
                fetchPetsForClient(clientId);
            } else {
                // Clear the pet dropdown if no client is selected
                petSelect.innerHTML = '<option value="">-- Select a Client First --</option>';
                clientNameInput.value = '';
            }
        });
        
        // Function to fetch pets for a selected client
        function fetchPetsForClient(clientId) {
            petSelect.innerHTML = '<option value="">Loading pets...</option>';
            
            console.log(`Fetching pets for client ID: ${clientId}`);
            
            fetch(`/appointments/get-pets/${clientId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`Network response error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear existing options
                    petSelect.innerHTML = '';
                    
                    console.log('Pet data received:', data);
                    
                    // Check for pets in either data.data (new format) or data.pets (old format)
                    const petsData = data.data || data.pets || [];
                    
                    if (data.success && petsData && petsData.length > 0) {
                        // Add default option
                        petSelect.innerHTML = '<option value="">-- Select a Pet --</option>';
                        
                        // Add the pets
                        petsData.forEach(pet => {
                            const option = document.createElement('option');
                            option.value = pet.id;
                            option.textContent = `${pet.name} (${pet.species}${pet.breed ? ' - ' + pet.breed : ''})`;
                            petSelect.appendChild(option);
                        });
                    } else {
                        console.log('No pets found or empty data:', data);
                        petSelect.innerHTML = '<option value="">No pets found for this client</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching pets:', error);
                    petSelect.innerHTML = '<option value="">Error loading pets. Please check console.</option>';
                });
        }
        
        // Initialize pets if a client is already selected (e.g., on form validation error)
        if (clientSelect.value) {
            fetchPetsForClient(clientSelect.value);
        }
        
        // Add end time estimate display to the form
        const durationField = document.getElementById('duration');
        const durationFieldParent = durationField.closest('.col-md-6');
        const endTimeEstimateElement = document.createElement('small');
        endTimeEstimateElement.classList.add('form-text', 'text-muted');
        endTimeEstimateElement.innerHTML = 'Estimated end time: <span id="end_time_estimate"></span>';
        durationFieldParent.appendChild(endTimeEstimateElement);
        
        // Initialize end time estimate
        updateEndTimeEstimate();
    });
</script>
@endpush 