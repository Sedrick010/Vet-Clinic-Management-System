<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionValid
{
    /**
     * Handle an incoming request.
     * 
     * This middleware:
     * 1. Validates user is authenticated (session is valid)
     * 2. Prevents caching of authenticated pages
     * 3. Adds headers to prevent browser back button issues
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Part 1: Session validation check
        // For regular authenticated routes - check if user is still authenticated
        if (!Auth::check() && !session()->has('tenant_user')) {
            // User is not authenticated, redirect to login page
            // This catches cases where the session expired but browser cache shows the page
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }

        // For tenant routes - verify that the tenant session still exists and matches
        if (session()->has('tenant_user')) {
            // If we're on a tenant route, verify the session still has valid clinic data
            if (!session()->has('current_clinic_id') || !session()->has('current_clinic')) {
                // Tenant session data is incomplete or invalid
                session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                session()->flash('error', 'Your clinic session has expired. Please log in again.');
                return redirect()->route('login');
            }
        }
        
        // Continue with the request
        $response = $next($request);
        
        // Part 2: Add cache prevention headers
        // These headers prevent browsers from caching authenticated pages
        // Combined from both NoCacheHeaders and PreventBackHistory middleware
        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }
}
