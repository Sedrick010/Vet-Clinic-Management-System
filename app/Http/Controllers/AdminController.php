<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
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
        
        return view('admin.dashboard', [
            'pendingClinics' => $pendingClinics,
            'approvedClinics' => $approvedClinics,
            'rejectedClinics' => $rejectedClinics,
            'totalClinics' => $pendingClinics + $approvedClinics + $rejectedClinics,
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
