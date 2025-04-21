@extends('layouts.app')

@section('title', 'Appointment Details')
@section('page_name', 'Appointment Details')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Appointment Details</h6>
                        <div>
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-secondary ms-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Pet Information</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-paw text-dark"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $appointment->pet->name }}</h6>
                                    <p class="text-xs text-secondary mb-0">Owner: {{ $appointment->pet->owner->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Veterinarian</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-md text-dark"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Dr. {{ $appointment->veterinarian->name }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Appointment Time</h6>
                            <div class="mb-3">
                                <p class="text-sm mb-1">Start Time:</p>
                                <p class="text-dark font-weight-bold mb-0">{{ $appointment->start_time->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-sm mb-1">End Time:</p>
                                <p class="text-dark font-weight-bold mb-0">{{ $appointment->end_time->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Status</h6>
                            <span class="badge badge-sm bg-gradient-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'info') }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Reason for Visit</h6>
                            <p class="text-sm mb-4">{{ $appointment->reason }}</p>

                            @if($appointment->notes)
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Additional Notes</h6>
                            <p class="text-sm mb-0">{{ $appointment->notes }}</p>
                            @endif
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="d-flex justify-content-end">
                        <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?');">
                                <i class="fas fa-trash"></i> Delete Appointment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 