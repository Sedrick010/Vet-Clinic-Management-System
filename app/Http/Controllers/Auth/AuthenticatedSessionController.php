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
use Illuminate\Support\Facades\Schema;

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
        // Don't force logout when accessing login page - allow multi-tab logins
        // Just check if accessing from a subdomain to show appropriate login form
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
                    
                    // Check if this is an admin user trying to access a tenant subdomain
                    $centralUser = User::where('email', $request->email)->first();
                    if ($centralUser) {
                        // Admin users should not log in via clinic subdomains
                        Log::warning('Admin/central user attempted login via clinic subdomain', [
                            'user_id' => $centralUser->id,
                            'clinic_id' => $clinic->id,
                            'subdomain' => $subdomain
                        ]);
                        
                        // Return an error message - block admin/central users login on subdomain
                        return back()->withErrors([
                            'email' => 'You cannot log in to clinic sites with a central account. Please use the main application.',
                        ])->withInput($request->except('password'));
                    }
                    
                    // Switch to tenant database
                    $this->tenantDatabaseService->switchToTenant($clinic);
                    
                    try {
                        // Try to find the user in this tenant database - check both users and staff tables
                        $tenantUserExists = false;
                        $tenantUser = null;
                        
                        // Check if we can connect to the tenant database
                        try {
                            DB::connection('tenant')->getPdo();
                            Log::info('Successfully connected to tenant database', [
                                'clinic_id' => $clinic->id,
                                'database' => $clinic->database_name
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to connect to tenant database: ' . $e->getMessage(), [
                                'clinic_id' => $clinic->id,
                                'database' => $clinic->database_name
                            ]);
                            
                            return back()->withErrors([
                                'email' => 'Database connection error. Please contact support.',
                            ])->withInput($request->except('password'));
                        }
                        
                        // Check if the users table exists
                        $hasUsersTable = Schema::connection('tenant')->hasTable('users');
                        Log::info('Users table existence check', [
                            'clinic_id' => $clinic->id,
                            'has_users_table' => $hasUsersTable
                        ]);
                        
                        // Check if the staff table exists
                        $hasStaffTable = Schema::connection('tenant')->hasTable('staff');
                        Log::info('Staff table existence check', [
                            'clinic_id' => $clinic->id,
                            'has_staff_table' => $hasStaffTable
                        ]);
                        
                        // First try the users table
                        if ($hasUsersTable) {
                            $tenantUser = DB::connection('tenant')->table('users')
                                ->where('email', $request->email)
                                ->first();
                                
                            if ($tenantUser && Hash::check($request->password, $tenantUser->password)) {
                                $tenantUserExists = true;
                                Log::info('Found user in users table', [
                                    'clinic_id' => $clinic->id,
                                    'email' => $request->email
                                ]);
                            }
                        }
                        
                        // Then try the staff table if not found in users
                        if (!$tenantUserExists && $hasStaffTable) {
                            $staffUser = DB::connection('tenant')->table('staff')
                                ->where('email', $request->email)
                                ->first();
                                
                            if ($staffUser && Hash::check($request->password, $staffUser->password)) {
                                $tenantUser = $staffUser;
                                $tenantUserExists = true;
                                Log::info('Found user in staff table', [
                                    'clinic_id' => $clinic->id,
                                    'email' => $request->email,
                                    'role' => $staffUser->role
                                ]);
                            } else if ($staffUser) {
                                Log::warning('Staff user found but password mismatch', [
                                    'clinic_id' => $clinic->id,
                                    'email' => $request->email
                                ]);
                            }
                        }
                        
                        if ($tenantUserExists && $tenantUser) {
                            // Clear any existing auth sessions
                            if (Auth::check()) {
                                Auth::logout();
                            }
                            
                            // Ensure no session data from other clinics
                            session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
                            
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
                            'email' => $request->email,
                            'trace' => $e->getTraceAsString()
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
            // Clear any existing tenant sessions
            if (session()->has('tenant_user')) {
                session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
            }
            
            // First, check if this is an admin user in the central database
            $centralUser = User::where('email', $request->email)->first();
            if ($centralUser && Hash::check($request->password, $centralUser->password)) {
                // Admin login successful
                Auth::login($centralUser, $request->boolean('remember'));
                $request->session()->regenerate();
                
                Log::info('Central user login successful', [
                    'user_id' => $centralUser->id,
                    'role' => $centralUser->role
                ]);
                
                // Check if admin user
                if ($centralUser->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->intended(route('dashboard'));
                }
            }
            
            // No matching central user found
            return back()->withErrors([
                'email' => 'These credentials do not match our central system records.',
            ])->withInput($request->except('password'));
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again later.',
            ])->withInput($request->except('password'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log the logout attempt
        Log::info('User logging out', [
            'user_id' => Auth::id(),
            'tenant_user' => session('tenant_user'),
            'clinic_id' => session('current_clinic_id')
        ]);

        // Clear authentication
        Auth::guard('web')->logout();

        // Clear all session data
        session()->forget(['tenant_user', 'current_clinic_id', 'current_clinic']);
        session()->invalidate();
        session()->regenerateToken();

        // Set the flag to prevent back navigation after logout
        session()->flash('just_logged_out', true);

        // Set cache control headers
        $response = redirect()->route('login')->with('status', 'You have been logged out successfully.');
        
        // Clear any cached pages
        return $response->header('Cache-Control','nocache, no-store, max-age=0, must-revalidate')
            ->header('Pragma','no-cache')
            ->header('Expires','Sun, 02 Jan 1990 00:00:00 GMT');
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
