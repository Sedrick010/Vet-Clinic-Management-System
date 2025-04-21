<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Clinic;

class RedirectIfAuthenticatedForWrongDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;
        
        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }
        
        // Check for admin users on subdomain
        if ($subdomain && Auth::check() && Auth::user()->role === 'admin') {
            // Admin user on subdomain - log out and redirect to main domain
            Auth::logout();
            return redirect()->to(config('app.url') . '/login')
                ->with('warning', 'Admin accounts must use the main domain. Please log in again.');
        }
        
        // Check for tenant users on wrong subdomain
        if (session()->has('tenant_user') && session('current_clinic_id')) {
            $clinic = Clinic::find(session('current_clinic_id'));
            
            if ($clinic) {
                if (!$subdomain) {
                    // Tenant user on main domain - clear session and redirect to proper subdomain
                    $sessionData = session('tenant_user');
                    $clinicId = session('current_clinic_id');
                    
                    // Clear tenant session
                    session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                    
                    // Generate the correct subdomain URL
                    $protocol = $request->secure() ? 'https://' : 'http://';
                    $redirectUrl = $protocol . $clinic->subdomain . '.' . $appDomain . '/login';
                    
                    Log::warning('Tenant user attempted access on main domain', [
                        'email' => $sessionData->email ?? 'unknown',
                        'clinic_id' => $clinicId,
                        'redirecting_to' => $redirectUrl
                    ]);
                    
                    return redirect()->to($redirectUrl)
                        ->with('warning', 'You must use your clinic\'s specific domain to access your account.');
                } else if ($subdomain !== $clinic->subdomain) {
                    // Tenant user on wrong subdomain - clear session and redirect to proper subdomain
                    $sessionData = session('tenant_user');
                    $clinicId = session('current_clinic_id');
                    
                    // Clear tenant session
                    session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                    
                    // Generate the correct subdomain URL
                    $protocol = $request->secure() ? 'https://' : 'http://';
                    $redirectUrl = $protocol . $clinic->subdomain . '.' . $appDomain . '/login';
                    
                    Log::warning('Tenant user attempted access on wrong subdomain', [
                        'email' => $sessionData->email ?? 'unknown',
                        'clinic_id' => $clinicId,
                        'current_subdomain' => $subdomain,
                        'correct_subdomain' => $clinic->subdomain,
                        'redirecting_to' => $redirectUrl
                    ]);
                    
                    return redirect()->to($redirectUrl)
                        ->with('warning', 'You are trying to access a different clinic\'s domain. Please use your clinic\'s specific domain.');
                }
            }
        }
        
        return $next($request);
    }
} 