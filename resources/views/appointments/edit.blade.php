@extends('layouts.app')

@section('title', 'Edit Appointment')
@section('page_name', 'Edit Appointment')

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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-control-label mb-0">Pet Information</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="newPetToggle">
                                            <label class="form-check-label" for="newPetToggle">New Pet</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Existing Pet Selection with Manual Input -->
                                    <div id="existingPetSection">
                                        <input type="text" class="form-control" id="pet_name" name="pet_name" list="petsList" 
                                               placeholder="Type or select a pet name" 
                                               value="{{ $appointment->pet ? $appointment->pet->name . ' (Owner: ' . $appointment->pet->owner->name . ')' : '' }}" 
                                               required>
                                        <datalist id="petsList">
                                            @foreach($pets as $pet)
                                                <option value="{{ $pet->name }} (Owner: {{ $pet->owner->name }})" data-id="{{ $pet->id }}">
                                            @endforeach
                                        </datalist>
                                        <input type="hidden" id="pet_id" name="pet_id" value="{{ $appointment->pet_id }}">
                                        <small class="form-text text-muted">Type a new pet name or select from existing pets</small>
                                    </div>

                                    <!-- New Pet Input Fields -->
                                    <div id="newPetSection" style="display: none;">
                                        <div class="mb-2">
                                            <label class="form-control-label">Pet Name</label>
                                            <input type="text" class="form-control" id="new_pet_name" name="new_pet_name" placeholder="Enter pet name">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-control-label">Owner Name</label>
                                            <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Enter owner name">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-control-label">Owner Phone</label>
                                            <input type="text" class="form-control" id="owner_phone" name="owner_phone" placeholder="Enter owner phone">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-control-label">Species</label>
                                            <input type="text" class="form-control" id="species" name="species" placeholder="Enter species">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-control-label">Breed</label>
                                            <input type="text" class="form-control" id="breed" name="breed" placeholder="Enter breed">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-control-label mb-0">Veterinarian</label>
                                    </div>
                                    <input type="text" class="form-control" id="vet_name" name="vet_name" list="vetsList" 
                                           placeholder="Type or select a veterinarian" 
                                           value="Dr. {{ $appointment->veterinarian->name }}"
                                           required>
                                    <datalist id="vetsList">
                                        @foreach($veterinarians as $vet)
                                            <option value="Dr. {{ $vet->name }}" data-id="{{ $vet->id }}">
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" id="veterinarian_id" name="veterinarian_id" value="{{ $appointment->veterinarian_id }}">
                                    <small class="form-text text-muted">Type a new veterinarian name or select from existing ones</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time" class="form-control-label">Start Time</label>
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

                        <div class="form-group">
                            <label for="reason" class="form-control-label">Reason for Visit</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required>{{ $appointment->reason }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-control-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ $appointment->notes }}</textarea>
                        </div>

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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pet input handling
        const petNameInput = document.getElementById('pet_name');
        const petIdInput = document.getElementById('pet_id');
        const petsList = document.getElementById('petsList');

        petNameInput.addEventListener('input', function() {
            const selectedOption = Array.from(petsList.options).find(
                option => option.value === this.value
            );
            
            if (selectedOption) {
                petIdInput.value = selectedOption.dataset.id;
            } else {
                petIdInput.value = 'new:' + this.value;
            }
        });

        // Veterinarian input handling
        const vetNameInput = document.getElementById('vet_name');
        const vetIdInput = document.getElementById('veterinarian_id');
        const vetsList = document.getElementById('vetsList');

        vetNameInput.addEventListener('input', function() {
            const selectedOption = Array.from(vetsList.options).find(
                option => option.value === this.value
            );
            
            if (selectedOption) {
                vetIdInput.value = selectedOption.dataset.id;
            } else {
                vetIdInput.value = 'new:' + this.value;
            }
        });

        // Pet selection toggle
        const newPetToggle = document.getElementById('newPetToggle');
        const existingPetSection = document.getElementById('existingPetSection');
        const newPetSection = document.getElementById('newPetSection');

        newPetToggle.addEventListener('change', function() {
            if (this.checked) {
                existingPetSection.style.display = 'none';
                newPetSection.style.display = 'block';
                petNameInput.removeAttribute('required');
                document.getElementById('new_pet_name').setAttribute('required', '');
                document.getElementById('owner_name').setAttribute('required', '');
                document.getElementById('owner_phone').setAttribute('required', '');
                document.getElementById('species').setAttribute('required', '');
            } else {
                existingPetSection.style.display = 'block';
                newPetSection.style.display = 'none';
                petNameInput.setAttribute('required', '');
                document.getElementById('new_pet_name').removeAttribute('required');
                document.getElementById('owner_name').removeAttribute('required');
                document.getElementById('owner_phone').removeAttribute('required');
                document.getElementById('species').removeAttribute('required');
            }
        });

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
    });
</script>
@endpush 