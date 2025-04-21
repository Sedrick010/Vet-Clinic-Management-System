<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Services\SubdomainService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubdomainSession
{
    protected $subdomainService;

    public function __construct(SubdomainService $subdomainService)
    {
        $this->subdomainService = $subdomainService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get current subdomain
        $subdomain = $this->subdomainService->current();
        
        // Generate a unique session cookie name based on the subdomain
        if ($subdomain) {
            // Even for admin users browsing tenant sites, use the tenant-specific session
            // This ensures proper separation - admins can browse but can't log in to tenant sites
            $sessionCookie = 'vetclinic_' . $subdomain . '_session';
            
            // Log if an admin is browsing a tenant site
            if (Auth::check() && Auth::user()->role === 'admin') {
                Log::info('Admin browsing tenant site with separate session', [
                    'user_id' => Auth::id(),
                    'subdomain' => $subdomain
                ]);
            }
        } else {
            // Main domain - use admin session
            $sessionCookie = 'vetclinic_admin_session';
        }
        
        // Set the session cookie name
        Config::set('session.cookie', $sessionCookie);
        
        return $next($request);
    }
} 