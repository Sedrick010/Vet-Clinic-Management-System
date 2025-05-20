<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClinicSelectorController extends Controller
{
    /**
     * Display the clinic selector page for users associated with multiple clinics.
     */
    public function index()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        // Get the user's primary clinic
        $primaryClinic = Clinic::find($user->clinic_id);
        
        // In a real multi-tenant system, we might query for all clinics where this user has access
        // For now, we'll assume they only have access to their primary clinic
        $clinics = [$primaryClinic];
        
        // In a real system, you'd implement a more complex query here
        // $clinics = Clinic::whereHas('users', function($query) use ($user) {
        //     $query->where('user_id', $user->id);
        // })->get();
        
        return view('clinics.select', [
            'clinics' => $clinics,
            'user' => $user
        ]);
    }
    
    /**
     * Switch to the selected clinic.
     */
    public function switchClinic(Request $request, $clinicId)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        $clinic = Clinic::find($clinicId);
        
        if (!$clinic) {
            return redirect()->route('clinics.select')
                ->with('error', 'Clinic not found.');
        }
        
        // In a real system, verify the user has access to this clinic
        // For now, we'll just check if it's their primary clinic
        if ($user->clinic_id != $clinic->id) {
            return redirect()->route('clinics.select')
                ->with('error', 'You do not have access to this clinic.');
        }
        
        // Store the selected clinic ID in the session
        session(['current_clinic_id' => $clinic->id]);
        
        try {
            // Switch to the tenant database
            app(TenantDatabaseService::class)->switchToTenant($clinic);
            
            // For local development, redirect to dashboard
            if (app()->environment('local') && 
                (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1')) {
                return redirect()->route('dashboard')
                    ->with('success', 'You are now working in ' . $clinic->name);
            }
            
            // For production, redirect to the subdomain
            $protocol = $request->secure() ? 'https://' : 'http://';
            $domain = Str::after(config('app.url'), $protocol);
            
            return redirect($protocol . $clinic->subdomain . '.' . $domain . '/dashboard');
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error switching clinics: ' . $e->getMessage());
            
            return redirect()->route('clinics.select')
                ->with('error', 'There was a problem switching to the selected clinic.');
        }
    }
} 