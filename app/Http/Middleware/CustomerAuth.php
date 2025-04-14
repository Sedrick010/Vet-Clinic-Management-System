<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login', ['subdomain' => $request->route('subdomain')]);
        }

        // Verify that the customer belongs to the correct clinic
        $customer = Auth::guard('customer')->user();
        $clinic = \App\Models\Clinic::where('subdomain', $request->route('subdomain'))->first();

        if (!$clinic || $customer->clinic_id !== $clinic->id) {
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login', ['subdomain' => $request->route('subdomain')])
                ->withErrors(['email' => 'You do not have access to this clinic.']);
        }

        return $next($request);
    }
}
