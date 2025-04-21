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
                        
                        <!-- Client Information -->
                        <div class="form-group mb-4">
                            <label for="client_name" class="form-label fw-bold">Client Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror" 
                                   id="client_name" name="client_name" value="{{ old('client_name') }}" required>
                            @error('client_name')
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

@push('scripts')
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
    });
</script>
@endpush 