<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the host from the request
        $host = $request->getHost();
        
        // Skip tenant resolution for direct localhost or IP access
        if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }
        
        // Get the app domain from config
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        
        // If the host is exactly our app domain (vetclinic.localhost), proceed normally
        if ($host === $appDomain) {
            // Block access to tenant-only routes from the main domain
            $tenantOnlyRoutes = ['tenant.dashboard'];
            foreach ($tenantOnlyRoutes as $route) {
                if (str_contains($request->route()->getName() ?? '', $route)) {
                    Log::warning('Attempt to access tenant-only route from main domain', [
                        'route' => $request->route()->getName(),
                        'path' => $request->path()
                    ]);
                    
                    return redirect()->route('home')
                        ->with('error', 'This route can only be accessed from a clinic subdomain.');
                }
            }
            
            return $next($request);
        }
        
        // Extract subdomain using our improved method
        $subdomain = $this->getSubdomain($host);
        
        // If no valid subdomain could be extracted, handle appropriately
        if (!$subdomain) {
            return $this->handleInvalidSubdomain($request, $next);
        }
        
        // If user is authenticated with the central system and is an admin, 
        // they should not be allowed to access tenant subdomains
        if (Auth::check() && Auth::user()->role === 'admin') {
            Log::warning('Admin user attempted to access tenant subdomain', [
                'user_id' => Auth::id(),
                'subdomain' => $subdomain,
                'path' => $request->path()
            ]);
            
            Auth::logout();
            session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
            session()->invalidate();
            session()->regenerateToken();
            
            return redirect()->to(config('app.url'))
                ->with('error', 'Admin accounts cannot access tenant subdomains. Please use the main application.');
        }
        
        // Block registration routes when accessed from any subdomain
        if ($request->is('register-clinic') || $request->is('register-clinic/*')) {
            Log::warning('Attempted to register clinic from subdomain', [
                'host' => $host,
                'subdomain' => $subdomain,
                'path' => $request->path()
            ]);
            
            return redirect()->to(config('app.url') . '/register-clinic')
                ->with('error', 'Clinic registration is only available from the main domain. Please use ' . config('app.url'));
        }
        
        // Look for a clinic with this subdomain
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        // If no clinic found with this subdomain, redirect to main site
        if (!$clinic) {
            Log::info('No clinic found for subdomain', ['subdomain' => $subdomain]);
            
            return redirect()->to(config('app.url'))
                ->with('error', 'Clinic not found. The subdomain "' . $subdomain . '" does not exist.');
        }
        
        // Check if the clinic is approved
        if ($clinic->approval_status !== 'approved') {
            // Store clinic info in session for the pending page
            session([
                'pending_clinic_id' => $clinic->id,
                'pending_clinic_name' => $clinic->name,
                'pending_clinic_status' => $clinic->approval_status,
                'pending_clinic_reason' => $clinic->rejection_reason,
                'pending_clinic_subdomain' => $clinic->subdomain
            ]);
            
            Log::info('Attempt to access unapproved clinic', [
                'clinic_id' => $clinic->id, 
                'subdomain' => $subdomain,
                'status' => $clinic->approval_status
            ]);
            
            return redirect()->route('clinics.pending');
        }
        
        // If the clinic is not active, show an error
        if (!$clinic->is_active) {
            abort(403, 'This clinic is currently inactive.');
        }
        
        // Store the current clinic in the session
        session(['current_clinic_id' => $clinic->id]);
        
        try {
            // Switch to the tenant database
            app(TenantDatabaseService::class)->switchToTenant($clinic);
            
            // If user is authenticated with the central database, log them out
            // They should be authenticated with the tenant database instead
            if (Auth::check()) {
                // Check if they belong to this clinic
                if (Auth::user()->clinic_id !== $clinic->id) {
                    Auth::logout();
                    
                    // If not redirecting to login, ensure the context is cleared
                    if (!$request->routeIs('login')) {
                        return redirect()->route('login')
                            ->with('error', 'Please log in with credentials for this clinic.');
                    }
                }
            }
        } catch (\Exception $e) {
            // Log the database error
            Log::error('Tenant database connection error: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'subdomain' => $subdomain,
                'database_name' => $clinic->database_name
            ]);
            
            // Redirect to main site with error
            return redirect()->to(config('app.url'))
                ->with('error', 'Unable to connect to clinic database. Please contact support.');
        }
        
        // Proceed with the request
        return $next($request);
    }

    /**
     * Get the subdomain from the HTTP host
     */
    private function getSubdomain(string $host): ?string
    {
        $appUrl = config('app.url');
        $baseDomain = parse_url($appUrl, PHP_URL_HOST) ?? 'localhost';
        
        // Normalize the base domain by removing www if present
        $baseDomain = preg_replace('/^www\./', '', $baseDomain);
        
        // If the host doesn't end with the base domain, it's not a valid subdomain
        if (!str_ends_with($host, $baseDomain)) {
            Log::warning('Invalid domain detected', [
                'host' => $host,
                'base_domain' => $baseDomain
            ]);
            return null;
        }
        
        // Remove the base domain part to get the subdomain
        $subdomain = substr($host, 0, strlen($host) - strlen($baseDomain) - 1);
        
        // If subdomain is empty or contains further dots (could be a nested subdomain)
        if (empty($subdomain) || strpos($subdomain, '.') !== false) {
            Log::warning('Invalid subdomain format detected', [
                'host' => $host,
                'extracted_subdomain' => $subdomain,
                'base_domain' => $baseDomain
            ]);
            return null;
        }
        
        // Only return the subdomain if it matches our expected format
        if (preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $subdomain)) {
            return $subdomain;
        }
        
        Log::warning('Subdomain format validation failed', [
            'subdomain' => $subdomain,
            'host' => $host
        ]);
        
        return null;
    }
    
    /**
     * Return appropriate error message and redirect based on the current route
     */
    private function handleInvalidSubdomain(Request $request, $next)
    {
        $currentRoute = $request->route()->getName() ?? '';
        $currentPath = $request->path();
        
        // Check if user is trying to access a protected route
        $protectedRoutes = ['tenant.dashboard', 'login', 'register'];
        $isProtectedRoute = false;
        
        foreach ($protectedRoutes as $route) {
            if (str_contains($currentRoute, $route) || str_contains($currentPath, $route)) {
                $isProtectedRoute = true;
                break;
            }
        }
        
        if ($isProtectedRoute) {
            Log::warning('Invalid subdomain access attempt to protected route', [
                'host' => $request->getHost(),
                'route' => $currentRoute,
                'path' => $currentPath
            ]);
            
            return redirect(config('app.url'))->with('error', 'Invalid clinic subdomain. Please check the URL and try again.');
        }
        
        // For other routes, just proceed normally (will fall back to central database)
        return $next($request);
    }
} 