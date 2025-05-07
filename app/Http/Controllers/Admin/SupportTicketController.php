<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of all support tickets.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::query();
        
        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }
        
        // Sort by latest by default
        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.support.index', [
            'tickets' => $tickets,
            'statuses' => [
                SupportTicket::STATUS_OPEN => 'Open',
                SupportTicket::STATUS_IN_PROGRESS => 'In Progress',
                SupportTicket::STATUS_RESOLVED => 'Resolved',
                SupportTicket::STATUS_CLOSED => 'Closed'
            ],
            'priorities' => [
                SupportTicket::PRIORITY_LOW => 'Low',
                SupportTicket::PRIORITY_MEDIUM => 'Medium',
                SupportTicket::PRIORITY_HIGH => 'High',
                SupportTicket::PRIORITY_CRITICAL => 'Critical'
            ],
            'currentStatus' => $request->status,
            'currentPriority' => $request->priority,
            'currentClinic' => $request->clinic_id,
            'isSidebar' => true
        ]);
    }

    /**
     * Display the specified support ticket.
     */
    public function show(SupportTicket $ticket)
    {
        return view('admin.support.show', [
            'ticket' => $ticket->load(['replies', 'clinic']),
            'isSidebar' => true
        ]);
    }

    /**
     * Update the status of a support ticket.
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);
        
        // Get old status for comparison
        $oldStatus = $ticket->status;
        
        // Update the status
        $ticket->status = $request->status;
        $result = $ticket->save();
        
        // Log the status change
        \Illuminate\Support\Facades\Log::info('Support ticket status update', [
            'ticket_id' => $ticket->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'updated_by' => auth()->id(),
            'success' => $result
        ]);
        
        // Add a system note about the status change
        $reply = new SupportTicketReply();
        $reply->message = "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . 
                          " to " . ucfirst(str_replace('_', ' ', $request->status)) . " by admin.";
        $reply->support_ticket_id = $ticket->id;
        $reply->user_id = auth()->id();
        $reply->is_admin = true;
        $reply->is_system_message = true;
        $reply->save();
        
        // Force refresh the ticket from database to ensure we have latest state
        $ticket = SupportTicket::find($ticket->id);
        
        return redirect()->route('admin.support.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully to ' . ucfirst(str_replace('_', ' ', $ticket->status)) . '.');
    }

    /**
     * Update the priority of a support ticket.
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,critical'
        ]);
        
        $ticket->priority = $request->priority;
        $ticket->save();
        
        return redirect()->route('admin.support.show', $ticket->id)
            ->with('success', 'Ticket priority updated successfully.');
    }

    /**
     * Add a reply to a support ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string'
        ]);
        
        $reply = new SupportTicketReply();
        $reply->message = $request->message;
        $reply->support_ticket_id = $ticket->id;
        $reply->user_id = auth()->id();
        $reply->is_admin = true;
        $reply->save();
        
        // Update ticket status to in_progress if it was open
        if ($ticket->status === SupportTicket::STATUS_OPEN) {
            $ticket->status = SupportTicket::STATUS_IN_PROGRESS;
            $ticket->save();
        }
        
        // Optionally mark as resolved
        if ($request->has('mark_resolved') && $request->mark_resolved) {
            $ticket->status = SupportTicket::STATUS_RESOLVED;
            $ticket->save();
        }
        
        return redirect()->route('admin.support.show', $ticket->id)
            ->with('success', 'Reply added successfully.');
    }
    
    /**
     * Get statistics for the admin dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'in_progress' => SupportTicket::where('status', SupportTicket::STATUS_IN_PROGRESS)->count(),
            'resolved' => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed' => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
            'critical' => SupportTicket::where('priority', SupportTicket::PRIORITY_CRITICAL)
                ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])
                ->count()
        ];
        
        return $stats;
    }

    /**
     * Remove the specified support ticket from storage.
     */
    public function destroy(SupportTicket $ticket)
    {
        // Store ticket ID for messaging
        $ticketId = $ticket->id;
        
        // Delete all associated replies first to avoid foreign key constraint issues
        $ticket->replies()->delete();
        
        // Delete the ticket
        $ticket->delete();
        
        // Log the deletion
        \Illuminate\Support\Facades\Log::info('Support ticket deleted by admin', [
            'ticket_id' => $ticketId,
            'admin_id' => auth()->id()
        ]);
        
        return redirect()->route('admin.support.index')
            ->with('success', "Support ticket #{$ticketId} has been deleted.");
    }
}
