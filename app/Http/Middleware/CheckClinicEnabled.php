<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class CheckClinicEnabled
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
        // Get the current subdomain
        $subdomain = $this->subdomainService->current();
        
        // Only apply this middleware for subdomain access, not the main site
        if (!$subdomain) {
            return $next($request);
        }
        
        // Get the current clinic from the subdomain
        $clinic = $this->subdomainService->getCurrentClinic();

        // Enhanced logging to troubleshoot middleware execution
        Log::debug('CheckClinicEnabled middleware executing', [
            'subdomain' => $subdomain,
            'clinic_found' => (bool)$clinic,
            'clinic_id' => $clinic ? $clinic->id : null,
            'is_enabled' => $clinic ? $clinic->is_enabled : null,
            'path' => $request->path(),
            'full_url' => $request->fullUrl()
        ]);

        // If no clinic found or clinic is disabled, show disabled message
        if (!$clinic || !$clinic->is_enabled) {
            // Log the access attempt for troubleshooting
            Log::warning('Access attempt to disabled clinic', [
                'subdomain' => $subdomain,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'clinic_id' => $clinic ? $clinic->id : null,
                'clinic_enabled' => $clinic ? $clinic->is_enabled : null,
                'disable_reason' => $clinic ? $clinic->disable_reason : null,
            ]);

            // Check if it's an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This clinic has been disabled. Please contact the administrator for assistance.',
                ], 403);
            }

            // Flash a message if it's a web request
            return redirect()->to(config('app.url'))->with('error', 
                'This clinic has been disabled. ' . 
                ($clinic && $clinic->disable_reason ? 'Reason: ' . $clinic->disable_reason : 'Please contact the administrator for assistance.')
            );
        }

        return $next($request);
    }
}
