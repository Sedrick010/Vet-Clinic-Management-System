<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseService;

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
        
        try {
            // First ensure we're working with a fresh connection
            DB::purge('tenant');
            
            // Switch to the tenant database using the macro if available
            if (method_exists(DB::class, 'useTenant')) {
                DB::useTenant($clinic);
            } else {
                // Fall back to the service if macro isn't available yet
                app(TenantDatabaseService::class)->switchToTenant($clinic);
            }
            
            // Test the connection by performing a simple query
            try {
                // Verify we have PDO connection
                DB::connection('tenant')->getPdo();
                
                // Run a simple test query
                $testResult = DB::connection('tenant')->select('SELECT 1 as test');
                
                if (empty($testResult) || !isset($testResult[0]->test) || $testResult[0]->test != 1) {
                    throw new \Exception('Test query returned invalid result');
                }
                
                Log::debug('Successfully connected to tenant database', [
                    'clinic_id' => $clinic->id,
                    'database' => $clinic->database_name
                ]);
            } catch (\Exception $e) {
                Log::warning('First tenant connection attempt failed: ' . $e->getMessage(), [
                    'clinic_id' => $clinic->id, 
                    'database' => $clinic->database_name,
                    'error' => $e->getMessage()
                ]);
                
                // If we can't query, recreate the connection and try again
                DB::purge('tenant');
                
                // Reconfigure and attempt again
                app(TenantDatabaseService::class)->switchToTenant($clinic);
                
                // Final attempt to run query
                $retryTest = DB::connection('tenant')->select('SELECT 1 as test');
                if (empty($retryTest)) {
                    throw new \Exception('Retry test query failed');
                }
            }
            
            // Store the clinic ID in session for access throughout the app
            session()->put('current_clinic_id', $clinic->id);
            
            // Update cache with latest access data
            try {
                // Cache the clinic info with a long expiry - reduces DB queries
                cache()->put('clinic_' . $clinic->id, $clinic, now()->addDay());
            } catch (\Exception $e) {
                // Cache error is non-fatal
                Log::warning('Failed to cache clinic info: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // If there's a terminal error switching to the tenant database, log it and redirect
            Log::error('Critical error connecting to tenant database: ' . $e->getMessage(), [
                'subdomain' => $subdomain,
                'host' => $host,
                'path' => $request->path(),
                'ip' => $request->ip(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->to(config('app.url'))
                ->with('error', 'Database connection error. Please try again later or contact support.');
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