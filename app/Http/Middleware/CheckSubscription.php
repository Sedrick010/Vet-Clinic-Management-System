<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as a tenant
        if (!session()->has('tenant_user') || !session()->has('current_clinic_id')) {
            return redirect()->route('login');
        }

        // Get the current clinic's subscription
        $subscription = Subscription::where('clinic_id', session('current_clinic_id'))
            ->where('approval_status', 'approved')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            return redirect()->route('subscription.plans')->with('error', 
                'You need an active subscription to access inventory management. Please subscribe to a plan.');
        }

        return $next($request);
    }
} 