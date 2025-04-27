@extends('layouts.app')

@section('page_name', 'Pet Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Pet Details</h6>
                    <div>
                        <a href="{{ route('pets.edit', $pet->id) }}" class="btn btn-sm bg-gradient-info me-2">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('pets.index') }}" class="btn btn-sm bg-gradient-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Pets
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success text-white">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row border-bottom pb-4">
                                <div class="col-md-12 mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-lg text-center border-radius-md text-white me-3" style="background-color: 
                                            @if($pet->species == 'Dog') #3498db
                                            @elseif($pet->species == 'Cat') #f39c12
                                            @elseif($pet->species == 'Bird') #2ecc71
                                            @elseif($pet->species == 'Reptile') #27ae60
                                            @else #95a5a6
                                            @endif
                                        ">
                                            <i class="fas 
                                                @if($pet->species == 'Dog') fa-dog
                                                @elseif($pet->species == 'Cat') fa-cat
                                                @elseif($pet->species == 'Bird') fa-dove
                                                @elseif($pet->species == 'Reptile') fa-dragon
                                                @else fa-paw
                                                @endif
                                                fa-2x opacity-10">
                                            </i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 font-weight-bold">{{ $pet->name }}</h3>
                                            <p class="text-sm mb-0">{{ $pet->species }} • {{ $pet->breed }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Basic Information</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Species:</span>
                                                <span class="text-sm">{{ $pet->species }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Breed:</span>
                                                <span class="text-sm">{{ $pet->breed ?: 'Not specified' }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Gender:</span>
                                                <span class="text-sm">{{ $pet->gender ? ucfirst($pet->gender) : 'Not specified' }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Age:</span>
                                                <span class="text-sm">
                                                    @if($pet->birthdate)
                                                        {{ $pet->birthdate->age }} years (born {{ $pet->birthdate->format('M d, Y') }})
                                                    @else
                                                        Not specified
                                                    @endif
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Owner Information</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Name:</span>
                                                <span class="text-sm">{{ optional($pet->owner)->name }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Email:</span>
                                                <span class="text-sm">{{ optional($pet->owner)->email ?: 'Not available' }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-sm font-weight-bold">Phone:</span>
                                                <span class="text-sm">{{ optional($pet->owner)->phone ?: 'Not available' }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            @if($pet->notes)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Notes</h6>
                                    <div class="card bg-gray-100">
                                        <div class="card-body py-3">
                                            <p class="text-sm mb-0">{{ $pet->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-gradient-primary shadow-lg">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Total Appointments</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $pet->appointments->count() }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                                <i class="fas fa-calendar-check text-primary opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-4">
                                <div class="card-header pb-0 p-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0">Quick Actions</h6>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <a href="{{ route('appointments.create') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                        <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                                    </a>
                                    <form action="{{ route('pets.destroy', $pet->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                                onclick="return confirm('Are you sure you want to delete this pet? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt me-2"></i> Delete Pet
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Appointment History</h6>
                                    <a href="{{ route('appointments.create') }}" class="btn btn-sm bg-gradient-success">
                                        <i class="fas fa-plus-circle me-1"></i> New Appointment
                                    </a>
                                </div>
                                <div class="card-body px-0 pt-0 pb-2">
                                    <div class="table-responsive p-0">
                                        @if($pet->appointments->count() > 0)
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date & Time</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Staff</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Reason</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                                        <th class="text-secondary opacity-7"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($pet->appointments->sortByDesc('start_time') as $appointment)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $appointment->start_time->format('M d, Y') }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ optional($appointment->staff)->name ?? 'Not assigned' }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $appointment->reason }}</p>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $appointment->getStatusColor() }}">
                                                                    {{ ucfirst($appointment->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-link text-dark p-0">
                                                                    <i class="fas fa-eye text-sm"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="text-center py-4">
                                                <p class="text-secondary mb-0">No appointment history available</p>
                                                <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary mt-3">
                                                    <i class="fas fa-calendar-plus me-1"></i> Schedule First Appointment
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 