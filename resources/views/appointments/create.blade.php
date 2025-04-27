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
                                <label for="start_time" class="form-label fw-bold">Start Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="end_time" class="form-label fw-bold">End Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
        // Set minimum dates for appointment times
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_time').min = today + 'T00:00';
        document.getElementById('end_time').min = today + 'T00:00';

        // Update end time minimum when start time changes
        document.getElementById('start_time').addEventListener('change', function() {
            document.getElementById('end_time').min = this.value;
            if (document.getElementById('end_time').value < this.value) {
                document.getElementById('end_time').value = this.value;
            }
        });
        
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
    });
</script>
@endpush 