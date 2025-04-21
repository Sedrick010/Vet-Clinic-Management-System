<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionAccess
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

        // If no clinic found or subscription is not active, redirect with error message
        if (!$clinic || !$clinic->is_subscription_active) {
            Log::warning('Access attempt to premium feature with inactive subscription', [
                'subdomain' => $this->subdomainService->current(),
                'clinic_id' => $clinic ? $clinic->id : null,
                'subscription_active' => $clinic ? $clinic->is_subscription_active : null,
                'path' => $request->path(),
            ]);

            // Check if it's an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature requires an active subscription.',
                ], 403);
            }

            // Flash a message for web request
            return redirect()->route('dashboard')->with('error', 
                'This feature requires an active subscription. Please contact administration to activate your subscription.'
            );
        }

        return $next($request);
    }
} 