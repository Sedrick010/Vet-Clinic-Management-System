<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Services\TenantDatabaseService;

class CustomerController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    public function showClinicSelection()
    {
        $clinics = Clinic::where('approval_status', 'approved')
            ->orderBy('name')
            ->get();
        return view('customer.clinic-selection', compact('clinics'));
    }

    public function selectClinic($subdomain)
    {
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();
        $domain = config('app.url');
        // Remove http:// or https:// from domain
        $domain = preg_replace('#^https?://#', '', $domain);
        // Redirect to clinic's welcome page instead of registration
        return redirect()->away("http://{$subdomain}.{$domain}");
    }

    public function showRegistrationForm(Request $request)
    {
        // Get the clinic from the subdomain
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();
        
        return view('auth.customer-register', compact('clinic'));
    }

    public function register(Request $request)
    {
        // Get the clinic from the subdomain
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        // Switch to the clinic's database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Check if email is unique in the clinic's database
        if (Customer::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered with this clinic.']);
        }

        // Create customer in the clinic's database
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        
        // Redirect to the clinic's login page
        return redirect()->route('customer.login', ['subdomain' => $subdomain])
            ->with('success', 'Registration successful! You can now log in.');
    }

    public function showLoginForm(Request $request, $subdomain)
    {
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();
        return view('auth.customer-login', compact('clinic'));
    }

    public function login(Request $request, $subdomain)
    {
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Switch to the clinic's database
        $this->tenantDatabaseService->switchToTenant($clinic);

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard', ['subdomain' => $subdomain]));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function dashboard(Request $request, $subdomain)
    {
        $clinic = Clinic::where('subdomain', $subdomain)->firstOrFail();
        
        // Switch to the clinic's database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        $customer = Auth::guard('customer')->user();
        return view('customer.dashboard', compact('clinic', 'customer'));
    }

    public function logout(Request $request, $subdomain)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('customer.login', ['subdomain' => $subdomain]);
    }
}
