<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionAccess
{
    protected $subdomainService;
    protected $subscriptionService;

    public function __construct(SubdomainService $subdomainService, SubscriptionService $subscriptionService)
    {
        $this->subdomainService = $subdomainService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature = null): Response
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
                'feature' => $feature,
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

        // If a specific feature is provided, check if the clinic has access to it
        if ($feature && !$this->subscriptionService->hasFeatureAccess($clinic, $feature)) {
            Log::warning('Access attempt to feature not included in subscription plan', [
                'subdomain' => $this->subdomainService->current(),
                'clinic_id' => $clinic->id,
                'subscription_plan' => $clinic->subscription_plan,
                'requested_feature' => $feature,
                'path' => $request->path(),
            ]);

            // Check if it's an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature requires a higher subscription plan.',
                ], 403);
            }

            // Flash a message for web request
            return redirect()->route('dashboard')->with('error', 
                "The {$feature} feature is not available in your current {$clinic->subscription_plan} plan. Please upgrade to access this feature."
            );
        }

        return $next($request);
    }
} 