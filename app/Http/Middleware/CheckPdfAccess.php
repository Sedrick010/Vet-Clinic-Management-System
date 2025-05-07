<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPdfAccess
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
    public function handle(Request $request, Closure $next): Response
    {
        // For debugging, log all PDF requests
        Log::debug('PDF access attempt', [
            'path' => $request->path(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'user_id' => auth()->id() ?? 'not authenticated',
            'timestamp' => now()->toDateTimeString()
        ]);

        // Get the current clinic from the subdomain
        $clinic = $this->subdomainService->getCurrentClinic();

        if (!$clinic) {
            Log::warning('PDF access attempt with no clinic found', [
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            return redirect()->route('dashboard')->with('error', 'Clinic not found.');
        }

        // Get the clinic's subscription plan and ensure case-insensitive comparison
        $plan = strtolower($clinic->subscription_plan ?? 'free');

        // Enhanced logging for debugging
        Log::info('PDF access check', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'plan' => $plan,
            'path' => $request->path(),
            'is_subscription_active' => $clinic->is_subscription_active,
            'user_id' => auth()->id() ?? 'not authenticated',
            'role' => auth()->check() ? auth()->user()->roles->pluck('name') : 'not authenticated'
        ]);

        // Check if the plan allows PDF generation (all paid plans)
        // Also verify that the subscription is active
        if ($plan === 'free' || !$clinic->is_subscription_active) {
            Log::warning('Free plan or inactive subscription user attempted to access PDF feature', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'plan' => $plan,
                'is_subscription_active' => $clinic->is_subscription_active,
                'path' => $request->path()
            ]);

            // Check if it's an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF export is only available on paid plans. Please upgrade your subscription.',
                    'upgrade_required' => true
                ], 403);
            }

            // Redirect to the subscription page with an upgrade prompt
            return redirect()->route('subscription.index')
                ->with('upgrade_required', true)
                ->with('error', 'PDF export is only available on paid plans (Basic, Standard, and Business). Please upgrade your subscription to access this feature.');
        }

        // If we're allowing access, log that too
        Log::info('PDF access granted', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'plan' => $plan,
            'path' => $request->path()
        ]);

        // If plan is not free and subscription is active, continue
        return $next($request);
    }
} 