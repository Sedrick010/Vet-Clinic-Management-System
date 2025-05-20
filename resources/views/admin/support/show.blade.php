@extends('layouts.app')

@section('title', 'Admin Support Ticket #' . $ticket->id)
@section('page_name', 'Support Ticket Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Ticket #{{ $ticket->id }} <span class="text-secondary text-sm">({{ $ticket->clinic ? $ticket->clinic->name : 'N/A' }})</span></h4>
                        <p class="text-sm text-secondary mb-0">{{ $ticket->subject }}</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.support.index') }}" class="btn btn-link text-dark px-3 mb-0">
                            <i class="fas fa-arrow-left me-2"></i> Back to tickets
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-text">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8 mb-4 mb-md-0">
                        <!-- Ticket information -->
                        <div class="card bg-gray-100 mb-4">
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <span class="text-xs text-secondary">Created on {{ $ticket->created_at->format('M d, Y \a\t h:i A') }}</span>
                                </div>
                                <p class="mb-0">{{ $ticket->message }}</p>
                            </div>
                        </div>

                        <!-- Ticket Replies -->
                        <div class="mb-4">
                            <h5 class="mb-3 d-flex align-items-center">
                                <i class="fas fa-comments text-primary me-2"></i> Conversation History
                            </h5>
                            
                            <div class="timeline timeline-one-side">
                                <!-- Original ticket message -->
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step bg-primary">
                                        <i class="fas fa-user-circle text-white"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">Customer</h6>
                                        <p class="text-secondary text-xs mt-1 mb-2">{{ $ticket->created_at->format('M d, Y \a\t h:i A') }}</p>
                                        <p class="text-sm mb-0">{{ $ticket->message }}</p>
                                    </div>
                                </div>
                                
                                @foreach($ticket->replies as $reply)
                                    @if($reply->is_system_message)
                                    <div class="timeline-block mb-3">
                                        <span class="timeline-step bg-info">
                                            <i class="fas fa-info-circle text-white"></i>
                                        </span>
                                        <div class="timeline-content">
                                            <div class="alert alert-info mb-0 py-2 px-3">
                                                <p class="text-sm mb-0">{{ $reply->message }}</p>
                                                <p class="text-secondary text-xs text-end mt-1 mb-0">{{ $reply->created_at->format('M d, Y \a\t h:i A') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="timeline-block mb-3">
                                        <span class="timeline-step {{ $reply->is_admin ? 'bg-success' : 'bg-primary' }}">
                                            @if($reply->is_admin)
                                                <i class="fas fa-headset text-white"></i>
                                            @else
                                                <i class="fas fa-user-circle text-white"></i>
                                            @endif
                                        </span>
                                        <div class="timeline-content">
                                            <h6 class="text-dark text-sm font-weight-bold mb-0">
                                                {{ $reply->is_admin ? 'Support Staff' : 'Customer' }}
                                            </h6>
                                            <p class="text-secondary text-xs mt-1 mb-2">{{ $reply->created_at->format('M d, Y \a\t h:i A') }}</p>
                                            <p class="text-sm mb-0">{{ $reply->message }}</p>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div>
                            <h5 class="mb-3 d-flex align-items-center">
                                <i class="fas fa-reply text-success me-2"></i> Add Admin Reply
                            </h5>
                            
                            <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <textarea name="message" rows="4" 
                                        class="form-control" 
                                        placeholder="Type your reply here..." required></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="mark_resolved" name="mark_resolved" value="1">
                                    <label class="form-check-label" for="mark_resolved">
                                        Mark as resolved after reply
                                    </label>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn bg-gradient-success">
                                        <i class="fas fa-paper-plane me-2"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Ticket Status Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">Ticket Details</h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="mb-3">
                                    <h6 class="text-sm mb-1">Status</h6>
                                    <form action="{{ route('admin.support.status', $ticket->id) }}" method="POST" class="d-flex align-items-center">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm me-2">
                                            @foreach(['open', 'in_progress', 'resolved', 'closed'] as $status)
                                                <option value="{{ $status }}" {{ $ticket->status == $status ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm bg-gradient-primary">Update</button>
                                    </form>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-sm mb-1">Priority</h6>
                                    <form action="{{ route('admin.support.priority', $ticket->id) }}" method="POST" class="d-flex align-items-center">
                                        @csrf
                                        <select name="priority" class="form-control form-control-sm me-2">
                                            @foreach(['low', 'medium', 'high', 'critical'] as $priority)
                                                <option value="{{ $priority }}" {{ $ticket->priority == $priority ? 'selected' : '' }}>
                                                    {{ ucfirst($priority) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm bg-gradient-primary">Update</button>
                                    </form>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-sm mb-1">Clinic</h6>
                                    <p class="text-sm mb-0">{{ $ticket->clinic ? $ticket->clinic->name : 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <h6 class="text-sm mb-1">Last Updated</h6>
                                    <p class="text-sm text-secondary mb-0">{{ $ticket->updated_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">Actions</h6>
                            </div>
                            <div class="card-body pt-2">
                                @if($ticket->clinic)
                                <a href="#" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                    <i class="fas fa-hospital me-2"></i> View Clinic
                                </a>
                                @endif
                                <a href="{{ route('admin.support.index') }}?status=open" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                                    <i class="fas fa-ticket-alt me-2"></i> View Open Tickets
                                </a>
                                
                                <!-- Delete Ticket Button -->
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#deleteTicketModal">
                                    <i class="fas fa-trash me-2"></i> Delete Ticket
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Ticket Confirmation Modal -->
<div class="modal fade" id="deleteTicketModal" tabindex="-1" role="dialog" aria-labelledby="deleteTicketModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTicketModalLabel">Confirm Ticket Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete ticket #{{ $ticket->id }}? This action cannot be undone and will permanently remove the ticket and all associated replies.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('admin.support.destroy', $ticket->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Ticket</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection 