<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class CheckClinicActive
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
        // Get the current clinic from the subdomain
        $clinic = $this->subdomainService->getCurrentClinic();

        // If no clinic found or clinic is inactive, show deactivation message
        if (!$clinic || !$clinic->is_active) {
            // Log the access attempt for troubleshooting
            Log::warning('Access attempt to inactive clinic', [
                'subdomain' => $this->subdomainService->current(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'clinic_id' => $clinic ? $clinic->id : null,
                'clinic_active' => $clinic ? $clinic->is_active : null,
            ]);

            // Check if it's an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This clinic is currently inactive. Please contact support for assistance.',
                ], 403);
            }

            // Flash a message if it's a web request
            return redirect()->route('login')->with('error', 
                'This clinic is currently inactive. ' . 
                ($clinic && $clinic->deactivation_reason ? 'Reason: ' . $clinic->deactivation_reason : 'Please contact support for assistance.')
            );
        }

        return $next($request);
    }
} 