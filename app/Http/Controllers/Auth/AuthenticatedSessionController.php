<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Clinic;
use App\Models\User;
use App\Services\TenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Check if user is already logged in
        if (Auth::check() || session()->has('tenant_user')) {
            // Log this behavior for debugging
            Log::warning('User already authenticated but accessed login page', [
                'auth_check' => Auth::check(),
                'has_tenant_user' => session()->has('tenant_user'),
                'session_id' => session()->getId(),
                'ip' => request()->ip()
            ]);
            
            // Force logout when accessing login page
            Auth::guard('web')->logout();
            session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
            session()->invalidate();
            session()->regenerateToken();
        }
        
        // Check if accessing from a subdomain
        $subdomain = $this->getSubdomain(request());
        $clinic = null;
        
        if ($subdomain) {
            $clinic = Clinic::where('subdomain', $subdomain)->first();
        }
        
        return view('auth.login', [
            'clinic' => $clinic,
            'is_subdomain' => !empty($subdomain)
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Check if request is coming from a subdomain
            $subdomain = $this->getSubdomain($request);
            
            if ($subdomain) {
                // Find the clinic associated with this subdomain
                $clinic = Clinic::where('subdomain', $subdomain)->first();
                
                if ($clinic) {
                    // Check if the clinic is approved
                    if ($clinic->approval_status !== 'approved') {
                        Log::warning('Attempted login to non-approved clinic via subdomain', [
                            'clinic_id' => $clinic->id, 
                            'subdomain' => $subdomain,
                            'email' => $request->email
                        ]);
                        
                        return back()->withErrors([
                            'login_error' => 'This clinic is awaiting approval and cannot be accessed yet.',
                        ])->withInput($request->except('password'));
                    }
                    
                    // First, let's check if this is an admin user trying to access a tenant subdomain
                    // This should be prevented as admin users should only access the central app
                    $centralUser = User::where('email', $request->email)->first();
                    if ($centralUser && Hash::check($request->password, $centralUser->password)) {
                        if ($centralUser->role === 'admin') {
                            Log::warning('Admin user attempted to login via tenant subdomain', [
                                'user_id' => $centralUser->id,
                                'clinic_id' => $clinic->id,
                                'subdomain' => $subdomain
                            ]);
                            
                            return back()->withErrors([
                                'email' => 'Admin accounts cannot access tenant subdomains. Please use the main application.',
                            ])->withInput($request->except('password'));
                        }
                    }
                    
                    // Switch to tenant database
                    $this->tenantDatabaseService->switchToTenant($clinic);
                    
                    try {
                        // Try to find the user in this tenant database
                        $tenantUser = DB::connection('tenant')->table('users')
                            ->where('email', $request->email)
                            ->first();
                        
                        if ($tenantUser && Hash::check($request->password, $tenantUser->password)) {
                            // Tenant user login successful
                            Log::info('Tenant user login successful via subdomain', [
                                'clinic_id' => $clinic->id, 
                                'subdomain' => $subdomain,
                                'email' => $request->email
                            ]);
                            
                            // Store tenant user data in session
                            $request->session()->put('tenant_user', $tenantUser);
                            $request->session()->put('current_clinic_id', $clinic->id);
                            $request->session()->put('current_clinic', $clinic);
                            $request->session()->regenerate();
                            
                            // Redirect to dashboard on the same subdomain
                            return redirect()->route('dashboard')
                                ->with('success', 'Welcome back to your clinic dashboard!');
                        }
                        
                        // Switch back to main database
                        $this->tenantDatabaseService->switchToMain();
                        
                        // If we reach here, tenant authentication failed
                        Log::warning('Failed tenant login attempt via subdomain', [
                            'clinic_id' => $clinic->id,
                            'subdomain' => $subdomain,
                            'email' => $request->email
                        ]);
                        
                        return back()->withErrors([
                            'email' => 'These credentials do not match our records for this clinic.',
                        ])->withInput($request->except('password'));
                        
                    } catch (\Exception $e) {
                        $this->tenantDatabaseService->switchToMain();
                        Log::error('Error checking tenant user via subdomain: ' . $e->getMessage(), [
                            'clinic_id' => $clinic->id,
                            'subdomain' => $subdomain,
                            'email' => $request->email
                        ]);
                        
                        return back()->withErrors([
                            'email' => 'An error occurred while authenticating. Please try again later.',
                        ])->withInput($request->except('password'));
                    }
                }
                
                return back()->withErrors([
                    'email' => 'Invalid subdomain or clinic not found.',
                ])->withInput($request->except('password'));
            }

            // If not coming from a subdomain, proceed with regular auth flow
            
            // First, check if this is an admin user in the central database
            $adminUser = User::where('email', $request->email)->first();
            if ($adminUser && Hash::check($request->password, $adminUser->password)) {
                // Admin login successful
                Auth::login($adminUser, $request->boolean('remember'));
                $request->session()->regenerate();
                
                // Check if admin user
                if ($adminUser->role === 'admin') {
                    Log::info('Admin login successful', ['user_id' => $adminUser->id]);
                    return redirect()->route('admin.dashboard');
                } else {
                    // Regular central db user
                    Log::info('Central user login successful', ['user_id' => $adminUser->id]);
                    return redirect()->route('dashboard');
                }
            }

            // If not an admin, check for clinic users in tenant databases
            $matchingClinics = Clinic::where('email', $request->email)
                ->orWhere('subdomain', 'LIKE', "%{$request->email}%")
                ->get();

            foreach ($matchingClinics as $clinic) {
                // Only consider approved clinics
                if ($clinic->approval_status !== 'approved') {
                    Log::warning('Attempted login to non-approved clinic', ['clinic_id' => $clinic->id, 'email' => $request->email]);
                    continue;
                }

                // Switch to tenant database
                $this->tenantDatabaseService->switchToTenant($clinic);
                
                try {
                    // Try to find the user in this tenant database
                    $tenantUser = DB::connection('tenant')->table('users')
                        ->where('email', $request->email)
                        ->first();

                    if ($tenantUser && Hash::check($request->password, $tenantUser->password)) {
                        // Tenant user login successful
                        Log::info('Tenant user login successful', ['clinic_id' => $clinic->id, 'email' => $request->email]);
                        
                        // Since we can't use Auth with the tenant user directly,
                        // we'll create a session-based authentication for the tenant
                        $request->session()->put('tenant_user', $tenantUser);
                        $request->session()->put('current_clinic_id', $clinic->id);
                        $request->session()->put('current_clinic', $clinic);
                        $request->session()->regenerate();
                        
                        // For local development (localhost/127.0.0.1), redirect directly
                        if (app()->environment('local') && 
                            (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1')) {
                            
                            return redirect()->route('dashboard')
                                ->with('success', 'Welcome back to your clinic dashboard!');
                        }
                        
                        // For production with subdomains, redirect to the proper subdomain
                        $protocol = $request->secure() ? 'https://' : 'http://';
                        $domain = Str::after(config('app.url'), $protocol);
                        
                        return redirect($protocol . $clinic->subdomain . '.' . $domain . '/dashboard');
                    }
                } catch (\Exception $e) {
                    Log::error('Error checking tenant user: ' . $e->getMessage(), [
                        'clinic_id' => $clinic->id, 
                        'email' => $request->email
                    ]);
                }
                
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();
            }

            // If we reach here, authentication failed
            Log::warning('Failed login attempt', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors([
                'login_error' => 'An error occurred during login. Please try again.',
            ])->withInput($request->except('password'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log out Laravel authenticated user if present
        Auth::guard('web')->logout();

        // Clear tenant-specific session data
        session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);

        // Invalidate and regenerate the session
        session()->invalidate();
        session()->regenerateToken();

        // Set cache control headers to prevent back button from showing protected pages
        return redirect()->route('login')
            ->with('success', 'You have been successfully logged out.')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
            ]);
    }
    
    /**
     * Get the subdomain from the request, if any
     */
    protected function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        
        // Check if we're on localhost or an IP (for development)
        if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        // Extract the app domain from config
        $appUrl = config('app.url');
        $appDomain = parse_url($appUrl, PHP_URL_HOST) ?? '';
        
        // If the host doesn't contain the app domain, it's not a subdomain
        if (!str_contains($host, $appDomain)) {
            return null;
        }
        
        // Extract subdomain by removing the app domain
        $subdomain = str_replace('.' . $appDomain, '', $host);
        
        // If the host is the same as app domain, there's no subdomain
        if ($subdomain === $appDomain || empty($subdomain)) {
            return null;
        }
        
        Log::debug('Extracted subdomain', ['subdomain' => $subdomain, 'host' => $host]);
        return $subdomain;
    }
}
