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
        
        // Log for debugging
        Log::info('Subdomain extracted from complex host', [
            'host' => $host,
            'subdomain_part' => $subdomainPart,
            'resolved_subdomain' => $subdomain,
            'parts' => $parts
        ]);
        
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