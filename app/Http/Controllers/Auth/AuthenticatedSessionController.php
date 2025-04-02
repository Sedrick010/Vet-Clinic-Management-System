<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Set the current clinic in the session for tenant identification
        if ($user->clinic_id) {
            session(['current_clinic_id' => $user->clinic_id]);
            
            // Get the clinic
            $clinic = Clinic::find($user->clinic_id);
            if ($clinic) {
                try {
                    // Switch to the tenant database
                    app(TenantDatabaseService::class)->switchToTenant($clinic);
                    
                    // For local development (localhost/127.0.0.1), redirect directly
                    if (app()->environment('local') && 
                        (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1')) {
                        
                        return redirect()->intended(route('dashboard', absolute: false))
                            ->with('success', 'Welcome back to your clinic dashboard!');
                    }
                    
                    // For production with subdomains, check if we need to redirect to the proper subdomain
                    $host = $request->getHost();
                    $subdomain = $this->getSubdomain($request);
                    
                    // If we're not on the correct subdomain, redirect to it
                    if ($subdomain !== $clinic->subdomain) {
                        $protocol = $request->secure() ? 'https://' : 'http://';
                        $domain = Str::after(config('app.url'), $protocol);
                        
                        return redirect($protocol . $clinic->subdomain . '.' . $domain . '/dashboard');
                    }
                } catch (\Exception $e) {
                    // Log the error but continue
                    \Log::error('Error switching to tenant database on login: ' . $e->getMessage());
                }
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
    
    /**
     * Get the subdomain from the request
     *
     * @param  Request  $request
     * @return string|null
     */
    private function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Check if we have a subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        return null;
    }
}
