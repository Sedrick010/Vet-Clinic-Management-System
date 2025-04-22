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
        
        // Block and redirect old domain to new domain (both exact match and subdomains)
        if ($host === 'vetclinic.localhost' || str_ends_with($host, '.vetclinic.localhost')) {
            $newDomain = str_replace('vetclinic.localhost', 'vetclinic.localtest.me', $host);
            $path = $request->getPathInfo();
            $query = $request->getQueryString() ? '?' . $request->getQueryString() : '';
            $newUrl = ($request->secure() ? 'https://' : 'http://') . $newDomain . $path . $query;
            
            Log::info('Redirecting from old domain to new domain', [
                'old_host' => $host,
                'new_host' => $newDomain,
                'new_url' => $newUrl
            ]);
            
            return redirect()->to($newUrl);
        }
        
        // Skip tenant resolution for direct localhost or IP access
        if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }
        
        // Get the app domain from config
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        
        // If the host is exactly our app domain (vetclinic.localtest.me), proceed normally
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
        // we'll let them view public pages on the tenant site but redirect for protected content
        if (Auth::check() && Auth::user()->role === 'admin') {
            Log::info('Admin user browsing tenant subdomain', [
                'user_id' => Auth::id(),
                'subdomain' => $subdomain,
                'path' => $request->path()
            ]);
            
            // Redirect admin away from protected tenant areas
            $protectedPaths = ['dashboard', 'profile', 'settings', 'appointments', 'customers', 'staff', 'payments'];
            foreach ($protectedPaths as $path) {
                if ($request->is($path) || $request->is($path . '/*')) {
                    Log::warning('Admin attempted to access protected tenant area', [
                        'user_id' => Auth::id(),
                        'path' => $request->path(),
                        'subdomain' => $subdomain
                    ]);
                    
                    return redirect()->to(config('app.url') . '/admin/dashboard')
                        ->with('warning', 'Administrators cannot access clinic dashboards. Please manage clinics from the admin panel.');
                }
            }
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
        
        // If no clinic found with this subdomain, block access and redirect to main site
        if (!$clinic) {
            Log::warning('Access attempt to unregistered subdomain', [
                'subdomain' => $subdomain,
                'host' => $host,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            return redirect()->to(config('app.url'))
                ->with('error', 'Access denied. The subdomain "' . $subdomain . '" is not registered in our system.');
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
            return redirect()->to(config('app.url'))->with('error', 
                'This clinic is currently inactive. Please contact support for assistance.');
        }
        
        // Check if the clinic is enabled
        if (!$clinic->is_enabled) {
            return redirect()->to(config('app.url'))->with('error', 
                'This clinic has been disabled. ' . 
                ($clinic->disable_reason ? 'Reason: ' . $clinic->disable_reason : 'Please contact the administrator for assistance.')
            );
        }
        
        // Store the current clinic in the session
        session(['current_clinic_id' => $clinic->id]);
        
        try {
            // Switch to the tenant database
            app(TenantDatabaseService::class)->switchToTenant($clinic);
            
            // If user is authenticated with the central database
            if (Auth::check()) {
                // Exception for admin users - they can view tenant sites
                if (Auth::user()->role === 'admin') {
                    // Admin users can view tenant sites, but should not interact with tenant features
                    Log::info('Admin user viewing tenant site', [
                        'user_id' => Auth::id(),
                        'subdomain' => $subdomain,
                        'path' => $request->path()
                    ]);
                    
                    // If admin is trying to access tenant-specific features, redirect them
                    $tenantOnlyPaths = ['dashboard', 'profile', 'settings', 'appointments', 'customers', 'payments'];
                    foreach ($tenantOnlyPaths as $path) {
                        if ($request->is($path) || $request->is("$path/*")) {
                            // Redirect to main admin dashboard with info message
                            Log::info('Admin redirected from tenant-specific feature', [
                                'user_id' => Auth::id(),
                                'path' => $request->path()
                            ]);
                            return redirect()->to(config('app.url') . '/admin/dashboard')
                                ->with('info', 'Please use the admin panel for management tasks.');
                        }
                    }
                }
                // For non-admin users, ensure they belong to this clinic
                else if (Auth::user()->clinic_id !== $clinic->id) {
                    // Do not log out the user from the main domain, just deny access to this subdomain
                    Log::warning('User from different clinic attempted to access subdomain', [
                        'user_id' => Auth::id(),
                        'user_clinic_id' => Auth::user()->clinic_id,
                        'subdomain_clinic_id' => $clinic->id
                    ]);
                    
                    // If not already redirecting to login, redirect there with error
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
        
        // If the host is exactly the base domain, there's no subdomain
        if ($host === $baseDomain) {
            return null;
        }
        
        // If the host doesn't contain the base domain, it's not a valid host for our app
        if (!str_contains($host, $baseDomain)) {
            Log::warning('Invalid domain detected', [
                'host' => $host,
                'base_domain' => $baseDomain
            ]);
            return null;
        }
        
        // Extract the subdomain part (everything before the base domain)
        $subdomainPart = str_replace('.' . $baseDomain, '', $host);
        
        // Handle the case of possible nested subdomains
        // For example: something.clinic.vetclinic.localtest.me
        // We want to extract "clinic" as the main subdomain
        $parts = explode('.', $subdomainPart);
        
        // If there are multiple parts, log it for debugging
        if (count($parts) > 1) {
            Log::info('Complex subdomain structure detected', [
                'host' => $host,
                'subdomain_parts' => $parts
            ]);
            
            // Use the first-level subdomain (the one directly before the base domain)
            // In our example: clinic.vetclinic.localtest.me -> "clinic"
            $subdomain = $parts[count($parts) - 1];
        } else {
            $subdomain = $subdomainPart;
        }
        
        // Validate the extracted subdomain
        if (empty($subdomain)) {
            Log::warning('Empty subdomain extracted', ['host' => $host]);
            return null;
        }
        
        // Only return the subdomain if it matches our expected format
        if (preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $subdomain)) {
            Log::info('Valid subdomain extracted', [
                'host' => $host,
                'subdomain' => $subdomain
            ]);
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
        
        // Log the invalid access attempt
        Log::warning('Invalid subdomain access attempt', [
            'host' => $request->getHost(),
            'route' => $currentRoute,
            'path' => $currentPath,
            'ip' => $request->ip()
        ]);
        
        // Block access to all invalid subdomains
        return redirect(config('app.url'))->with('error', 
            'Invalid domain format. Please use the main application at ' . config('app.url'));
    }
} 