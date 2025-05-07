@extends('layouts.app')

@section('title', 'Support Ticket #' . $ticket->id)
@section('page_name', 'Support Ticket Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Ticket #{{ $ticket->id }}</h4>
                        <p class="text-sm text-secondary mb-0">{{ $ticket->subject }}</p>
                    </div>
                    <div>
                        <a href="{{ route('support.index') }}" class="btn btn-link text-dark px-3 mb-0">
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
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">You</h6>
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
                                                {{ $reply->is_admin ? 'Support Staff' : 'You' }}
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
                        @if ($ticket->status !== 'closed')
                            <div>
                                <h5 class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-reply text-primary me-2"></i> Add Reply
                                </h5>
                                
                                <form action="{{ route('support.reply', $ticket->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <textarea name="message" rows="4" 
                                            class="form-control" 
                                            placeholder="Type your reply here... Include any additional information that might help resolve your issue." required></textarea>
                                        <small class="form-text text-muted">Your reply will be sent to our support team and added to this ticket's history.</small>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn bg-gradient-primary">
                                            <i class="fas fa-paper-plane me-2"></i> Send Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <p class="mb-0">This ticket is closed. If you need further assistance, you can reopen it.</p>
                                </div>
                            </div>
                        @endif
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
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-sm mb-1">Priority</h6>
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
                                </div>
                                
                                <div>
                                    <h6 class="text-sm mb-1">Last Updated</h6>
                                    <p class="text-sm text-secondary mb-0">{{ $ticket->updated_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Actions -->
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">Actions</h6>
                            </div>
                            <div class="card-body pt-2">
                                @if ($ticket->status === 'closed')
                                    <form action="{{ route('support.reopen', $ticket->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn bg-gradient-primary w-100">
                                            <i class="fas fa-redo-alt me-2"></i> Reopen Ticket
                                        </button>
                                    </form>
                                    <p class="text-xs text-secondary mb-3">
                                        Reopening this ticket will allow you to add more information and continue the conversation with support staff.
                                    </p>
                                @else
                                    <form action="{{ route('support.close', $ticket->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn bg-gradient-secondary w-100">
                                            <i class="fas fa-times-circle me-2"></i> Close Ticket
                                        </button>
                                    </form>
                                    <p class="text-xs text-secondary mb-3">
                                        Close this ticket if your issue has been resolved or you no longer need assistance.
                                    </p>
                                @endif
                                
                                <!-- Delete Ticket Button -->
                                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteTicketModal">
                                    <i class="fas fa-trash me-2"></i> Delete Ticket
                                </button>
                                <p class="text-xs text-secondary mt-2 mb-0">
                                    Permanently remove this ticket and all replies from the system.
                                </p>
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
        <form action="{{ route('support.destroy', $ticket->id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Ticket</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection 