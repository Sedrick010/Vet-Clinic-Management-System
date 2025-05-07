@extends('layouts.app')

@section('title', 'Support Tickets')
@section('page_name', 'Support Tickets')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Support Tickets</h4>
                        <p class="text-sm mb-0">Track and manage your communication with our support team</p>
                    </div>
                    <a href="{{ route('support.create') }}" class="btn btn-sm bg-gradient-primary">
                        <i class="fas fa-plus me-2"></i> Create New Ticket
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-text">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card bg-gradient-info text-white p-3 mb-4">
                    <div class="d-flex align-items-start">
                        <div class="icon icon-shape bg-white shadow text-center me-3">
                            <i class="fas fa-info-circle text-info opacity-10"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-2">How Support Works</h5>
                            <p class="text-sm mb-3">Our support system allows you to create tickets for any issues or questions you have. We typically respond within 24 hours on business days.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h6 class="text-white opacity-8 mb-2">Status Meanings:</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-sm bg-gradient-primary me-2">Open</span>
                                        <span class="text-sm text-white opacity-8">Ticket submitted, awaiting staff review</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-sm bg-gradient-warning me-2">In Progress</span>
                                        <span class="text-sm text-white opacity-8">Staff is working on your issue</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-sm bg-gradient-success me-2">Resolved</span>
                                        <span class="text-sm text-white opacity-8">Issue has been resolved</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-sm bg-gradient-secondary me-2">Closed</span>
                                        <span class="text-sm text-white opacity-8">Ticket has been closed</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-white opacity-8 mb-2">Tips:</h6>
                                    <ul class="ps-4 mb-0 text-sm">
                                        <li>Be specific about your issue for faster resolution</li>
                                        <li>Include any error messages exactly as they appear</li>
                                        <li>You can add replies to your tickets at any time</li>
                                        <li>Closed tickets can be reopened if needed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (count($tickets) > 0)
                <div class="card">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
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
                                            <a href="{{ route('support.show', $ticket->id) }}" class="btn btn-link text-primary px-3 mb-0">
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
                                                            <form action="{{ route('support.destroy', $ticket->id) }}" method="POST">
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
                @else
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="icon icon-shape icon-lg bg-gradient-warning shadow text-center border-radius-lg mb-3 mx-auto">
                            <i class="fas fa-ticket-alt text-lg opacity-10"></i>
                        </div>
                        <h4>No Support Tickets Yet</h4>
                        <p class="mb-4">You haven't created any support tickets yet. Need help with something?</p>
                        <a href="{{ route('support.create') }}" class="btn bg-gradient-primary">
                            <i class="fas fa-plus me-2"></i> Create Your First Ticket
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 