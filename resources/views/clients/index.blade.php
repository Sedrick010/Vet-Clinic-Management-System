@extends('layouts.app')

@section('content')
<div class="container-fluid clients-list">
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Clients</h5>
                            <!-- Subscription Limit Indicator -->
                            @if(isset($clientsLimit) && isset($clientsCount))
                                <x-subscription-limit-indicator 
                                    :count="$clientsCount" 
                                    :limit="$clientsLimit" 
                                    type="clients" 
                                />
                            @endif
                        </div>
                        <a href="{{ route('clients.create') }}" class="btn btn-sm bg-gradient-primary">
                            <i class="fas fa-plus"></i> Add Client
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 my-2">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 my-2">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Client</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contact</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pets</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <div class="avatar avatar-sm me-3 bg-gradient-primary rounded-circle d-flex justify-content-center align-items-center" style="background-color: var(--primary-color) !important;">
                                                        <i class="text-white fas fa-user"></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $client->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $client->email }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $client->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $client->pets->count() }} pets</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $client->city ?? 'N/A' }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $client->state ?? '' }}</p>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex">
                                                <a href="{{ route('clients.show', $client->id) }}" class="btn btn-link text-info px-1 mb-0">
                                                    <i class="fas fa-eye text-info me-2"></i>
                                                    View
                                                </a>
                                                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-link text-dark px-1 mb-0">
                                                    <i class="fas fa-pencil-alt text-dark me-2"></i>
                                                    Edit
                                                </a>
                                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger px-1 mb-0" onclick="return confirm('Are you sure you want to delete this client?')">
                                                        <i class="fas fa-trash text-danger me-2"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No clients found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 