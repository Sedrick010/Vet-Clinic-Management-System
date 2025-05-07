<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the support tickets.
     */
    public function index()
    {
        $tickets = [];
        $user = null;
        $clinicId = null;
        
        // Check if user is authenticated with Laravel Auth
        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->role === 'admin') {
                // Admin can see all tickets
                $tickets = SupportTicket::orderBy('created_at', 'desc')->get();
            } else {
                // Non-admin user sees only their tickets
                $tickets = SupportTicket::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } 
        // Check if user is authenticated as a tenant user
        elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Ensure we only see tickets for the current clinic
            $tickets = SupportTicket::where('clinic_id', $clinicId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Add debug information
            \Illuminate\Support\Facades\Log::info('Tenant viewing support tickets', [
                'tenant_id' => $tenantUser->id,
                'clinic_id' => $clinicId,
                'ticket_count' => $tickets->count(),
                'ticket_ids' => $tickets->pluck('id')
            ]);
        }
        
        return view('support.index', [
            'tickets' => $tickets,
            'isSidebar' => true
        ]);
    }

    /**
     * Show the form for creating a new support ticket.
     */
    public function create()
    {
        return view('support.create', [
            'isSidebar' => true
        ]);
    }

    /**
     * Store a newly created support ticket in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical'
        ]);
        
        $ticket = new SupportTicket();
        $ticket->subject = $request->subject;
        $ticket->message = $request->message;
        $ticket->priority = $request->priority;
        $ticket->status = SupportTicket::STATUS_OPEN;
        
        // Check if user is authenticated with Laravel Auth
        if (auth()->check()) {
            $ticket->user_id = auth()->id();
        } 
        // Check if user is authenticated as a tenant user
        elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Ensure both clinic_id and tenant_id are set
            $ticket->clinic_id = $clinicId;
            $ticket->tenant_id = $tenantUser->id;
            
            // Log the ticket creation for debugging
            \Illuminate\Support\Facades\Log::info('Creating support ticket', [
                'tenant_id' => $tenantUser->id,
                'clinic_id' => $clinicId,
                'subject' => $ticket->subject
            ]);
        }
        
        $ticket->save();
        
        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Support ticket created successfully.');
    }

    /**
     * Display the specified support ticket.
     */
    public function show(SupportTicket $ticket)
    {
        // Authorize access to the ticket
        $this->authorizeAccess($ticket);
        
        return view('support.show', [
            'ticket' => $ticket->load('replies'),
            'isSidebar' => true
        ]);
    }

    /**
     * Add a reply to a support ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        // Authorize access to the ticket
        $this->authorizeAccess($ticket);
        
        $request->validate([
            'message' => 'required|string'
        ]);
        
        $reply = new SupportTicketReply();
        $reply->message = $request->message;
        $reply->support_ticket_id = $ticket->id;
        $reply->is_admin = false;
        
        // Check if user is authenticated with Laravel Auth
        if (auth()->check()) {
            $reply->user_id = auth()->id();
        } 
        // Check if user is authenticated as a tenant user
        elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $reply->tenant_id = $tenantUser->id;
        }
        
        $reply->save();
        
        // Update ticket status to open if it was resolved
        if ($ticket->status === SupportTicket::STATUS_RESOLVED) {
            $ticket->status = SupportTicket::STATUS_OPEN;
            $ticket->save();
        }
        
        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Reply added successfully.');
    }
    
    /**
     * Close a support ticket.
     */
    public function close(SupportTicket $ticket)
    {
        // Authorize access to the ticket
        $this->authorizeAccess($ticket);
        
        $ticket->status = SupportTicket::STATUS_CLOSED;
        $ticket->save();
        
        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Ticket closed successfully.');
    }
    
    /**
     * Reopen a closed support ticket.
     */
    public function reopen(SupportTicket $ticket)
    {
        // Authorize access to the ticket
        $this->authorizeAccess($ticket);
        
        $ticket->status = SupportTicket::STATUS_OPEN;
        $ticket->save();
        
        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Ticket reopened successfully.');
    }
    
    /**
     * Authorize access to a ticket.
     */
    private function authorizeAccess(SupportTicket $ticket)
    {
        // Check if user is authenticated with Laravel Auth
        if (auth()->check()) {
            $user = auth()->user();
            
            // Admin can access all tickets
            if ($user->role === 'admin') {
                return true;
            }
            
            // User can only access their own tickets
            if ($ticket->user_id !== $user->id) {
                abort(403, 'Unauthorized action.');
            }
        } 
        // Check if user is authenticated as a tenant user
        elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Only allow viewing tickets for the currently selected clinic
            if ($ticket->clinic_id == $clinicId) {
                return true;
            }
            
            // Log access attempt for debugging
            \Illuminate\Support\Facades\Log::warning('Unauthorized ticket access attempt', [
                'tenant_id' => $tenantUser->id, 
                'clinic_id' => $clinicId,
                'ticket_id' => $ticket->id,
                'ticket_clinic_id' => $ticket->clinic_id
            ]);
            
            abort(403, 'Unauthorized action. This ticket belongs to another clinic.');
        } else {
            abort(403, 'Unauthorized action.');
        }
        
        return true;
    }

    /**
     * Remove the specified support ticket from storage.
     */
    public function destroy(SupportTicket $ticket)
    {
        // Authorize access to the ticket
        $this->authorizeAccess($ticket);
        
        // Store ticket ID for messaging
        $ticketId = $ticket->id;
        
        // Delete all associated replies first to avoid foreign key constraint issues
        $ticket->replies()->delete();
        
        // Delete the ticket
        $ticket->delete();
        
        // Log the deletion
        \Illuminate\Support\Facades\Log::info('Support ticket deleted', [
            'ticket_id' => $ticketId,
            'deleted_by' => auth()->check() ? auth()->id() : session('tenant_user.id'),
            'is_admin' => auth()->check() && auth()->user()->role === 'admin'
        ]);
        
        return redirect()->route('support.index')
            ->with('success', "Support ticket #{$ticketId} has been deleted.");
    }
}
