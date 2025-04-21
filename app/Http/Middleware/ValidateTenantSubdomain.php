<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;
use Illuminate\Support\Facades\Log;

class ValidateTenantSubdomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the host from the request
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        
        // If it's the main domain, proceed
        if ($host === $appDomain) {
            // Only clear tenant session data if not an admin user
            if (session()->has('tenant_user')) {
                // If there's a tenant session on main domain, clear it
                session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                
                // Don't redirect - just clear the session data from the incorrect domain
                Log::warning('Cleared tenant session data on main domain', [
                    'host' => $host,
                    'path' => $request->path(),
                    'ip' => $request->ip()
                ]);
            }
            
            return $next($request);
        }
        
        // If the host doesn't end with our base domain, it's not relevant
        if (!str_ends_with($host, $appDomain)) {
            // This is a completely different domain, let other middleware handle it
            return $next($request);
        }
        
        // At this point, we have a subdomain of our app domain
        // Extract subdomain from host
        $subdomainPart = str_replace('.' . $appDomain, '', $host);
        
        // Check for nested subdomains
        $parts = explode('.', $subdomainPart);
        $subdomain = count($parts) > 1 ? $parts[count($parts) - 1] : $subdomainPart;
        
        // Skip validation for public asset paths
        if ($this->isPublicPath($request->path())) {
            return $next($request);
        }
        
        // Check if this is a valid registered subdomain
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        // Block access to unregistered subdomains
        if (!$clinic) {
            Log::warning('Access attempt to unregistered subdomain', [
                'subdomain' => $subdomain,
                'host' => $host,
                'path' => $request->path(),
                'ip' => $request->ip()
            ]);
            
            // Store the invalid subdomain in session for highlighting
            session()->flash('invalid_subdomain', $subdomain);
            
            return redirect()->to(config('app.url'))
                ->with('error', 'SECURITY ALERT: Invalid subdomain "' . $subdomain . '"');
        }
        
        // Store clinic info in request
        $request->attributes->set('current_clinic', $clinic);
        
        // Strict domain separation - if admin user tries to access a subdomain, redirect to main domain
        if (auth()->check() && auth()->user()->role === 'admin') {
            // Admin user trying to access subdomain - redirect to main domain with warning
            auth()->logout();
            return redirect()->to(config('app.url') . '/login')
                ->with('warning', 'Admin accounts must use the main domain. Please log in again.');
        }
        
        // If a tenant session exists but for a different clinic, clear it
        if (session()->has('current_clinic_id') && session('current_clinic_id') != $clinic->id) {
            session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
            Log::warning('Cleared tenant session data due to mismatched clinic', [
                'subdomain' => $subdomain,
                'previous_clinic_id' => session('current_clinic_id'),
                'current_clinic_id' => $clinic->id,
                'path' => $request->path()
            ]);
        }
        
        return $next($request);
    }
    
    /**
     * Check if the path is for public assets that should be accessible
     */
    private function isPublicPath(string $path): bool
    {
        $publicPaths = [
            'assets/', 'css/', 'js/', 'images/', 'fonts/',
            'favicon.ico', 'robots.txt'
        ];
        
        foreach ($publicPaths as $publicPath) {
            if (str_starts_with($path, $publicPath)) {
                return true;
            }
        }
        
        return false;
    }
} 