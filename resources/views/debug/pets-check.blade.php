@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Pet Database Check</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        This page is for debugging pet data relationships.
                    </div>
                    
                    <h5>Clients and Their Pets</h5>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Client ID</th>
                                    <th>Client Name</th>
                                    <th>Client Email</th>
                                    <th>Pet Count</th>
                                    <th>Pet Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ count($clientPets[$client->id] ?? []) }}</span>
                                    </td>
                                    <td>
                                        @if(isset($clientPets[$client->id]) && count($clientPets[$client->id]) > 0)
                                            <ul class="mb-0">
                                                @foreach($clientPets[$client->id] as $pet)
                                                    <li>{{ $pet->name }} ({{ $pet->species }}{{ $pet->breed ? ' - '.$pet->breed : '' }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-danger">No pets found</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <h5 class="mt-4">Direct Database Query Results</h5>
                    <div class="alert alert-secondary">
                        <pre>{{ json_encode($query_results, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 