<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
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
        // Skip session validation on login/logout routes
        if ($request->routeIs('login') || $request->routeIs('logout') || 
            $request->is('login') || $request->is('logout')) {
            return $next($request);
        }
        
        // Get the host and check if we're on the main domain or a subdomain
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $isMainDomain = ($host === $appDomain);
        
        // Check for admin routes accessed outside main domain
        if (!$isMainDomain && $request->is('admin*')) {
            // Redirect admin routes to main domain
            return redirect()->to(config('app.url') . '/admin/dashboard')
                ->with('warning', 'Admin sections should be accessed via the main domain.');
        }
        
        // Part 1: Session validation check
        // For regular authenticated routes - check if user is still authenticated
        if (!Auth::check() && !session()->has('tenant_user')) {
            // User is not authenticated, redirect to login page
            // This catches cases where the session expired but browser cache shows the page
            Log::info('Session validation failed - redirecting to login', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'auth_check' => Auth::check(),
                'has_tenant_user' => session()->has('tenant_user'),
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }

        // For tenant routes - verify that the tenant session still exists and matches
        if (session()->has('tenant_user')) {
            // If we're on a tenant route, verify the session still has valid clinic data
            if (!session()->has('current_clinic_id') || !session()->has('current_clinic')) {
                // Tenant session data is incomplete or invalid
                Log::warning('Incomplete tenant session data - redirecting to login', [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'session_id' => session()->getId(),
                    'has_tenant_user' => true,
                    'has_clinic_id' => session()->has('current_clinic_id'),
                    'has_clinic' => session()->has('current_clinic')
                ]);
                
                session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                session()->flash('error', 'Your clinic session has expired. Please log in again.');
                return redirect()->route('login');
            }
            
            // Verify we're on the correct subdomain for this clinic session
            $currentClinicData = session('current_clinic');
            if (!$isMainDomain && isset($currentClinicData['subdomain'])) {
                $currentHost = $request->getHost();
                $expectedHost = $currentClinicData['subdomain'] . '.' . $appDomain;
                
                // If we're on a different clinic's subdomain than the one in session
                if ($currentHost !== $expectedHost && !$request->is('login') && !$request->is('logout')) {
                    Log::warning('User accessing incorrect clinic subdomain', [
                        'current_host' => $currentHost,
                        'expected_host' => $expectedHost,
                        'session_clinic' => $currentClinicData['subdomain'],
                        'path' => $request->path()
                    ]);
                    
                    // Redirect to the correct subdomain
                    return redirect()->to('http://' . $expectedHost . '/dashboard')
                        ->with('warning', 'You have been redirected to your authorized clinic.');
                }
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
