@extends('layouts.app')

@section('title', 'Edit Appointment')
@section('page_name', 'Edit Appointment')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Edit Appointment</h6>
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

                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Client Selection -->
                        <div class="form-group mb-4">
                            <label for="client_id" class="form-control-label mb-2">Select Client</label>
                            <select class="form-control @error('client_id') is-invalid @enderror" 
                                   id="client_id" name="client_id" required>
                                <option value="">-- Select a Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" 
                                            {{ (old('client_id', $selectedPet ? $selectedPet->owner_id : null) == $client->id) ? 'selected' : '' }}>
                                        {{ $client->name }} {{ $client->email ? '('.$client->email.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">If the client is not listed, please <a href="{{ route('clients.create') }}" target="_blank">add a new client</a> first.</small>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Hidden client name field to maintain compatibility -->
                            <input type="hidden" id="client_name" name="client_name" value="{{ old('client_name', $appointment->client_name) }}">
                        </div>
                        
                        <!-- Pet Selection -->
                        <div class="form-group mb-4">
                            <label for="pet_id" class="form-control-label mb-2">Select Pet</label>
                            <select class="form-control @error('pet_id') is-invalid @enderror" 
                                   id="pet_id" name="pet_id" required>
                                <option value="">-- Select a Pet --</option>
                                <!-- Options will be populated based on selected client -->
                            </select>
                            <small class="form-text text-muted">If the pet is not listed, please <a href="{{ route('pets.create') }}" target="_blank">add a new pet</a> first.</small>
                            @error('pet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time" class="form-control-label">Appointment Date & Time</label>
                                    <input class="form-control" type="datetime-local" id="start_time" name="start_time" 
                                           value="{{ $appointment->start_time->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time" class="form-control-label">End Time</label>
                                    <input class="form-control" type="datetime-local" id="end_time" name="end_time" 
                                           value="{{ $appointment->end_time->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="reason" class="form-control-label">Reason for Visit</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required>{{ $appointment->reason }}</textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label for="notes" class="form-control-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ $appointment->notes }}</textarea>
                        </div>
                        
                        <!-- Hidden input to hold the staff ID -->
                        <input type="hidden" id="staff_id" name="staff_id" value="{{ $appointment->staff_id }}">

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('appointments.index') }}" class="btn btn-light m-0">Cancel</a>
                            <button type="submit" class="btn bg-gradient-primary m-0 ms-2">Update Appointment</button>
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
        // Date and time handling
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_time').min = today + 'T00:00';
        document.getElementById('end_time').min = today + 'T00:00';

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
        const currentPetId = "{{ $appointment->pet_id }}";
        
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
            
            fetch(`/appointments/get-pets/${clientId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
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
                            // Select the current pet if it matches
                            if (pet.id == currentPetId) {
                                option.selected = true;
                            }
                            petSelect.appendChild(option);
                        });
                    } else {
                        console.log('No pets found or empty data:', data);
                        petSelect.innerHTML = '<option value="">No pets found for this client</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching pets:', error);
                    petSelect.innerHTML = '<option value="">Error loading pets</option>';
                });
        }
        
        // Initialize pets if a client is already selected
        if (clientSelect.value) {
            fetchPetsForClient(clientSelect.value);
        }
    });
</script>
@endpush 