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
                        
                        <!-- Client Information -->
                        <div class="form-group mb-4">
                            <label class="form-control-label mb-2">Client Name</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" 
                                   placeholder="Enter client name" 
                                   value="{{ old('client_name', $appointment->client_name ?? '') }}" 
                                   required>
                            <small class="form-text text-muted">Name of the client</small>
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

@push('scripts')
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
    });
</script>
@endpush 