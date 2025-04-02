<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\User;
use App\Services\TenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClinicController extends Controller
{
    /**
     * Display the clinic registration form.
     */
    public function create(): View
    {
        return view('clinics.register');
    }

    /**
     * Handle an incoming clinic registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, TenantDatabaseService $tenantDatabaseService): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:clinics,subdomain'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create the database for the clinic
        $databaseName = $tenantDatabaseService->createDatabase($request->name);

        // Create the clinic
        $clinic = Clinic::create([
            'name' => $request->name,
            'subdomain' => $request->subdomain,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'database_name' => $databaseName,
        ]);

        // Create the clinic owner user
        $user = User::create([
            'name' => $request->owner_name,
            'email' => $request->owner_email,
            'password' => Hash::make($request->password),
            'clinic_id' => $clinic->id,
            'role' => 'owner',
        ]);

        // Set up the tenant database
        $tenantDatabaseService->setupTenantDatabase($clinic);

        // Log the user in
        Auth::login($user);

        // For local development, redirect to the dashboard directly
        if (app()->environment('local') && 
           (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1')) {
            // Store the current clinic in the session
            session(['current_clinic_id' => $clinic->id]);
            
            return redirect()->route('dashboard')->with('success', 'Your clinic has been registered successfully!');
        }

        // For production with subdomains, redirect to the clinic subdomain
        $protocol = $request->secure() ? 'https://' : 'http://';
        $subdomain = $request->subdomain;
        $domain = Str::after(config('app.url'), $protocol);
        
        return redirect($protocol . $subdomain . '.' . $domain . '/dashboard');
    }
} 