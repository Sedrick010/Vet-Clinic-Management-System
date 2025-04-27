@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Client Details</h5>
                        <div>
                            <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Back to Clients
                            </a>
                            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Contact Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Name:</strong>
                                        <p>{{ $client->name }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Email:</strong>
                                        <p>{{ $client->email ?: 'Not provided' }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Phone:</strong>
                                        <p>{{ $client->phone ?: 'Not provided' }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Address:</strong>
                                        <p>
                                            @if($client->address)
                                                {{ $client->address }}<br>
                                                @if($client->city || $client->state || $client->zip)
                                                    {{ $client->city ?? '' }}{{ $client->city && ($client->state || $client->zip) ? ',' : '' }}
                                                    {{ $client->state ?? '' }} {{ $client->zip ?? '' }}
                                                @endif
                                            @else
                                                Not provided
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($client->notes)
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $client->notes }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Pets</h6>
                                    <a href="{{ route('pets.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Add Pet
                                    </a>
                                </div>
                                <div class="card-body">
                                    @if($client->pets->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Species</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Breed</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($client->pets as $pet)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex px-2 py-1">
                                                                <div class="d-flex flex-column justify-content-center">
                                                                    <h6 class="mb-0 text-sm">{{ $pet->name }}</h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $pet->species }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $pet->breed ?: 'Not specified' }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">
                                                                @if($pet->birthdate)
                                                                    {{ \Carbon\Carbon::parse($pet->birthdate)->age }} years
                                                                @else
                                                                    Unknown
                                                                @endif
                                                            </p>
                                                        </td>
                                                        <td class="align-middle">
                                                            <a href="{{ route('pets.show', $pet->id) }}" class="btn btn-link text-secondary mb-0">
                                                                <i class="fas fa-eye text-xs"></i>
                                                            </a>
                                                            <a href="{{ route('pets.edit', $pet->id) }}" class="btn btn-link text-secondary mb-0">
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            No pets registered for this client yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Recent Appointments</h6>
                                </div>
                                <div class="card-body">
                                    @if($client->appointments && $client->appointments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pet</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Service</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($client->appointments->sortByDesc('date') as $appointment)
                                                    <tr>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $appointment->pet->name }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $appointment->service }}</p>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-sm bg-{{ $appointment->getStatusColor() }}">{{ $appointment->status }}</span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-link text-secondary mb-0">
                                                                <i class="fas fa-eye text-xs"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            No appointments found for this client.
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
@endsection 