<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        // Check for regular authenticated users
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Log::info('Redirected authenticated user from guest route', [
                    'user_id' => Auth::guard($guard)->id(),
                    'route' => $request->route()->getName(),
                    'ip' => $request->ip()
                ]);
                
                return redirect(route('dashboard'));
            }
        }
        
        // Also check for tenant users in session
        if (session()->has('tenant_user')) {
            Log::info('Redirected tenant user from guest route', [
                'tenant_user_id' => session('tenant_user')->id ?? 'unknown',
                'tenant_clinic_id' => session('current_clinic_id'),
                'route' => $request->route()->getName(),
                'ip' => $request->ip()
            ]);
            
            return redirect(route('dashboard'));
        }

        return $next($request);
    }
} 