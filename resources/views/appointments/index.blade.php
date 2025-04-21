@extends('layouts.app')

@section('title', 'Appointments')
@section('page_name', 'Appointments')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Appointments</h6>
                    <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Appointment
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Client</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Doctor</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date & Time</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $appointment)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $appointment->client_name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $appointment->staff->name }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $appointment->start_time->format('M d, Y h:i A') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-{{ 
                                            $appointment->status === 'completed' ? 'success' : 
                                            ($appointment->status === 'cancelled' ? 'danger' : 
                                            ($appointment->status === 'confirmed' ? 'primary' : 'info')) 
                                        }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-link text-secondary mb-0">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-link text-secondary mb-0">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No appointments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-3">
                        {{ $appointments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 