<?php

namespace App\Http\Middleware;

use App\Services\SubdomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Clinic;

class RealTimeSubscriptionCheck
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
        // Skip check for non-tenant requests
        if (!session()->has('current_clinic_id')) {
            return $next($request);
        }

        // Get current clinic from session and verify against fresh data
        $sessionClinicId = session('current_clinic_id');
        $freshClinic = Clinic::find($sessionClinicId);

        // Check if in a premium route
        $isPremiumRoute = $this->isPremiumRoute($request);

        // If subscription status changed and we're in a premium section
        if ($isPremiumRoute && $freshClinic && !$freshClinic->is_subscription_active) {
            Log::warning('Redirecting from premium feature - subscription deactivated', [
                'clinic_id' => $sessionClinicId,
                'path' => $request->path(),
                'subdomain' => $this->subdomainService->current()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'redirect' => route('dashboard'),
                    'message' => 'Your subscription has been deactivated. Redirecting to dashboard...'
                ], 403);
            }

            // Update session with fresh clinic data
            session()->put('current_clinic', $freshClinic);

            // Redirect to dashboard with message
            return redirect()->route('dashboard')->with('error', 
                'This feature requires an active subscription. Your subscription status has changed.'
            );
        }

        // If clinic status changed (disabled/inactive)
        if ($freshClinic && (!$freshClinic->is_enabled || !$freshClinic->is_active)) {
            // Clear session and redirect to main site
            session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
            
            Log::warning('Clinic disabled or deactivated during active session', [
                'clinic_id' => $sessionClinicId,
                'is_enabled' => $freshClinic->is_enabled,
                'is_active' => $freshClinic->is_active
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'redirect' => config('app.url'),
                    'message' => 'Your clinic access has been revoked.'
                ], 403);
            }

            return redirect()->to(config('app.url'))->with('error', 
                'Your clinic access has been ' . 
                (!$freshClinic->is_enabled ? 'disabled' : 'deactivated') . 
                '. Please contact the administrator.'
            );
        }

        return $next($request);
    }

    /**
     * Check if the current route is a premium feature route
     */
    private function isPremiumRoute(Request $request): bool
    {
        $premiumPaths = [
            'premium', 'premium/reports', 'premium/analytics'
        ];

        $currentPath = $request->path();
        
        foreach ($premiumPaths as $premiumPath) {
            if (str_starts_with($currentPath, $premiumPath)) {
                return true;
            }
        }

        return false;
    }
} 