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
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Client Information</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user text-dark"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $appointment->client_name }}</h6>
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
                                    <h6 class="mb-0">{{ $appointment->staff->name }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="horizontal dark">
                    
                    <!-- Pet Information -->
                    @if($appointment->pet)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Pet Information</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon icon-shape icon-sm shadow border-radius-md text-white text-center me-2 d-flex align-items-center justify-content-center" 
                                    style="background-color: 
                                        @if($appointment->pet->species == 'Dog') #3498db
                                        @elseif($appointment->pet->species == 'Cat') #f39c12
                                        @elseif($appointment->pet->species == 'Bird') #2ecc71
                                        @elseif($appointment->pet->species == 'Reptile') #27ae60
                                        @else #95a5a6
                                        @endif
                                    ">
                                    <i class="fas 
                                        @if($appointment->pet->species == 'Dog') fa-dog
                                        @elseif($appointment->pet->species == 'Cat') fa-cat
                                        @elseif($appointment->pet->species == 'Bird') fa-dove
                                        @elseif($appointment->pet->species == 'Reptile') fa-dragon
                                        @else fa-paw
                                        @endif
                                        text-white">
                                    </i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $appointment->pet->name }}</h6>
                                    <p class="text-sm mb-0">
                                        {{ $appointment->pet->species }} 
                                        @if($appointment->pet->breed)
                                            - {{ $appointment->pet->breed }}
                                        @endif
                                        ({{ ucfirst($appointment->pet->gender ?? 'Unknown') }})
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('pets.show', $appointment->pet_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Pet Details
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

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
                            <span class="badge badge-sm bg-gradient-{{ $appointment->getStatusColor() }}">
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