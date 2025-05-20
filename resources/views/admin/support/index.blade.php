@extends('layouts.app')

@section('title', 'Admin Support Tickets')
@section('page_name', 'Support Tickets Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Support Tickets Management</h4>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-text">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filters -->
                <div class="card bg-gray-100 p-3 mb-4">
                    <h5 class="mb-3">Filters</h5>
                    
                    <form action="{{ route('admin.support.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-control-label">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $key => $value)
                                    <option value="{{ $key }}" {{ $currentStatus == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="priority" class="form-control-label">Priority</label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="">All Priorities</option>
                                @foreach($priorities as $key => $value)
                                    <option value="{{ $key }}" {{ $currentPriority == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn bg-gradient-primary">
                                    <i class="fas fa-filter me-2"></i> Apply Filters
                                </button>
                                
                                <a href="{{ route('admin.support.index') }}" class="btn btn-link text-secondary ms-2">
                                    Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tickets Table -->
                @if (count($tickets) > 0)
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Priority</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Created</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Update</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tickets as $ticket)
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">{{ $ticket->id }}</p>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <i class="fas fa-ticket-alt text-warning me-2"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $ticket->subject }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $ticket->clinic ? $ticket->clinic->name : 'N/A' }}
                                                </p>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'open' => 'bg-gradient-primary',
                                                        'in_progress' => 'bg-gradient-warning',
                                                        'resolved' => 'bg-gradient-success',
                                                        'closed' => 'bg-gradient-secondary'
                                                    ];
                                                    $statusClass = $statusClasses[$ticket->status] ?? 'bg-gradient-secondary';
                                                @endphp
                                                <span class="badge badge-sm {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $priorityClasses = [
                                                        'low' => 'bg-gradient-secondary',
                                                        'medium' => 'bg-gradient-info',
                                                        'high' => 'bg-gradient-warning',
                                                        'critical' => 'bg-gradient-danger'
                                                    ];
                                                    $priorityClass = $priorityClasses[$ticket->priority] ?? 'bg-gradient-secondary';
                                                @endphp
                                                <span class="badge badge-sm {{ $priorityClass }}">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-secondary text-xs font-weight-bold">{{ $ticket->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td>
                                                <span class="text-secondary text-xs font-weight-bold">{{ $ticket->updated_at->format('M d, Y') }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-link text-primary px-3 mb-0">
                                                    <i class="fas fa-eye text-primary me-2"></i>View
                                                </a>
                                                <button type="button" class="btn btn-link text-danger mb-0" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteTicketModal{{ $ticket->id }}">
                                                    <i class="fas fa-trash text-danger me-2"></i>Delete
                                                </button>
                                                
                                                <!-- Delete Modal for each ticket -->
                                                <div class="modal fade" id="deleteTicketModal{{ $ticket->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete ticket #{{ $ticket->id }}?</p>
                                                                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('admin.support.destroy', $ticket->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <div class="icon icon-shape icon-lg bg-gradient-warning shadow text-center border-radius-lg mb-3 mx-auto">
                                <i class="fas fa-ticket-alt text-lg opacity-10"></i>
                            </div>
                            <h4>No Support Tickets Found</h4>
                            <p class="mb-0">There are no support tickets matching your filter criteria.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 