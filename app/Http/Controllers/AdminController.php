<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        $pendingClinics = Clinic::where('approval_status', 'pending')->count();
        $approvedClinics = Clinic::where('approval_status', 'approved')->count();
        $rejectedClinics = Clinic::where('approval_status', 'rejected')->count();
        
        // Get support ticket statistics
        $totalTickets = SupportTicket::count();
        $openTickets = SupportTicket::where('status', 'open')->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $resolvedTickets = SupportTicket::where('status', 'resolved')->count();
        $closedTickets = SupportTicket::where('status', 'closed')->count();
        $criticalTickets = SupportTicket::where('priority', 'critical')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        return view('admin.dashboard', [
            'pendingClinics' => $pendingClinics,
            'approvedClinics' => $approvedClinics,
            'rejectedClinics' => $rejectedClinics,
            'totalClinics' => $pendingClinics + $approvedClinics + $rejectedClinics,
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
            'closedTickets' => $closedTickets,
            'criticalTickets' => $criticalTickets,
            'isSidebar' => true,
        ]);
    }
    
    /**
     * Display the theme reference page.
     */
    public function themeReference(): View
    {
        return view('admin.theme-reference', [
            'isSidebar' => true,
        ]);
    }
}
