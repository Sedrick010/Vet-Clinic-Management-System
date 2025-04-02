<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Auth;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // In local development with localhost, skip tenant resolution
        $host = $request->getHost();
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return $next($request);
        }
        
        // Get the subdomain from the request
        $subdomain = $this->getSubdomain($request);
        
        // If there's no subdomain, proceed normally
        if (!$subdomain) {
            return $next($request);
        }
        
        try {
            // Find the clinic with the subdomain
            $clinic = Clinic::where('subdomain', $subdomain)->first();
            
            // If no clinic found, redirect to main page
            if (!$clinic) {
                return redirect()->to(config('app.url'));
            }
            
            // If the clinic is not active, show a message
            if (!$clinic->is_active) {
                abort(403, 'This clinic is currently inactive.');
            }
            
            // Store the current clinic in the session
            session(['current_clinic_id' => $clinic->id]);
            
            // Switch to the tenant database
            app(TenantDatabaseService::class)->switchToTenant($clinic);
            
            // If user is authenticated, verify they belong to this clinic
            if (Auth::check() && Auth::user()->clinic_id !== $clinic->id) {
                Auth::logout();
                return redirect()->route('login');
            }
        } catch (\Exception $e) {
            // Log the error but don't break the app
            \Log::error('Tenant resolution error: ' . $e->getMessage());
        }
        
        return $next($request);
    }
    
    /**
     * Get the subdomain from the request
     *
     * @param  Request  $request
     * @return string|null
     */
    private function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Check if we have a subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        return null;
    }
} 