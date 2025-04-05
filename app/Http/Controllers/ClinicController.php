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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class ClinicController extends Controller
{
    /**
     * Display the clinic registration form.
     */
    public function create(): View
    {
        // Only allow registrations from the main domain
        $host = request()->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        
        if ($host !== $appDomain) {
            Log::warning('Attempted to access registration form from unauthorized domain', [
                'host' => $host,
                'expected' => $appDomain
            ]);
            
            abort(403, 'Clinic registration is only available from the main domain. Please use ' . config('app.url'));
        }
        
        return view('clinics.register');
    }

    /**
     * Handle an incoming clinic registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, TenantDatabaseService $tenantDatabaseService): RedirectResponse
    {
        // Only allow registrations from the main domain
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        
        if ($host !== $appDomain) {
            Log::warning('Attempted to register clinic from unauthorized domain', [
                'host' => $host,
                'expected' => $appDomain
            ]);
            
            return redirect()->to(config('app.url') . '/register-clinic')
                ->with('error', 'Clinic registration is only available from the main domain.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required', 
                'string', 
                'max:50', 
                'alpha_dash', 
                'unique:clinics,subdomain',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', // Must start and end with alphanumeric, can contain hyphens
                function ($attribute, $value, $fail) {
                    // Reserved words check
                    $reservedWords = ['www', 'mail', 'admin', 'administrator', 'blog', 'dashboard', 'api', 'staging', 
                                     'dev', 'development', 'test', 'prod', 'production', 'demo', 'email', 'app',
                                     'billing', 'support', 'help', 'secure', 'security', 'ftp', 'webmail'];
                    
                    if (in_array(strtolower($value), $reservedWords)) {
                        $fail('The subdomain is reserved and cannot be used.');
                    }
                    
                    // Minimum length check
                    if (strlen($value) < 3) {
                        $fail('The subdomain must be at least 3 characters.');
                    }
                    
                    // Check for consecutive hyphens
                    if (strpos($value, '--') !== false) {
                        $fail('The subdomain cannot contain consecutive hyphens.');
                    }
                },
            ],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Log the start of clinic registration
            Log::info('Beginning clinic registration process', [
                'clinic_name' => $request->name,
                'subdomain' => $request->subdomain,
                'email' => $request->email
            ]);
            
            // Create the database for the clinic
            $databaseName = $tenantDatabaseService->createDatabase($request->name);

            Log::info('Database name generated for clinic', [
                'clinic_name' => $request->name,
                'database_name' => $databaseName
            ]);
            
            // Create the clinic with pending approval status in the central database
            $clinic = Clinic::create([
                'name' => $request->name,
                'subdomain' => $request->subdomain,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'database_name' => $databaseName,
                'approval_status' => 'pending',
            ]);

            Log::info('Clinic record created in central database', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'subdomain' => $clinic->subdomain
            ]);
            
            // Set up the tenant database - this will run our migrations
            $tenantDatabaseService->setupTenantDatabase($clinic);

            Log::info('Tenant database setup completed', [
                'clinic_id' => $clinic->id,
                'database_name' => $databaseName
            ]);
            
            // Switch to tenant database to create the user
            $tenantDatabaseService->switchToTenant($clinic);

            // Create the clinic owner user in the tenant database
            // We use insert directly to avoid model conflicts with the central database
            $userId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log success for debugging
            Log::info('Created tenant clinic owner', [
                'clinic_id' => $clinic->id,
                'clinic_subdomain' => $clinic->subdomain,
                'user_id' => $userId,
                'user_email' => $request->owner_email
            ]);

            // Switch back to the main database
            $tenantDatabaseService->switchToMain();

            // Store clinic info in session for pending page
            $request->session()->put([
                'pending_clinic_id' => $clinic->id,
                'pending_clinic_name' => $clinic->name,
                'pending_clinic_owner' => $request->owner_name,
                'pending_clinic_email' => $request->owner_email,
                'pending_clinic_subdomain' => $clinic->subdomain,
                'pending_clinic_status' => $clinic->approval_status
            ]);
            
            // Flash a success message
            $successMessage = "Thank you for registering your clinic '{$clinic->name}'! Your registration is pending approval. You will receive an email once it's approved.";
            
            // Using flash instead of with to ensure it persists through the redirect
            $request->session()->flash('success', $successMessage);
            
            Log::info('Clinic registration completed successfully', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'subdomain' => $clinic->subdomain
            ]);
            
            // Redirect to pending approval page with success message
            return redirect()->route('clinics.pending');
                
        } catch (\Exception $e) {
            Log::error('Error registering clinic: ' . $e->getMessage(), [
                'request_data' => $request->except('password', 'password_confirmation'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Provide more specific error messages based on the exception
            $errorMessage = 'An error occurred while registering your clinic. Please try again or contact support.';
            
            // Check for specific error types
            if (str_contains($e->getMessage(), 'database') || str_contains($e->getMessage(), 'Database')) {
                $errorMessage = 'Unable to create clinic database. Please contact support.';
            } else if (str_contains($e->getMessage(), 'migration')) {
                $errorMessage = 'Error setting up clinic database. Please contact support.';
            } else if (str_contains($e->getMessage(), 'user')) {
                $errorMessage = 'Error creating clinic owner account. Please try again with different credentials.';
            }
            
            return back()->withErrors([
                'registration_error' => $errorMessage
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Display the pending approval page.
     */
    public function pending(): View
    {
        return view('clinics.pending');
    }

    /**
     * List all clinics - for admin use.
     */
    public function index(Request $request): View
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $query = Clinic::query();
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('approval_status', $request->status);
        }
        
        // Filter by search term if provided
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('subdomain', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm)
                  ->orWhere('address', 'like', $searchTerm);
            });
        }
        
        // Order by creation date, newest first
        $query->orderBy('created_at', 'desc');
        
        // Paginate results
        $clinics = $query->paginate(10)->withQueryString();
        
        return view('admin.clinics.index', [
            'clinics' => $clinics,
            'isSidebar' => true,
        ]);
    }

    /**
     * Approve a clinic registration.
     */
    public function approve($id): RedirectResponse
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $clinic = Clinic::findOrFail($id);
        
        // Only send notification if status is changing
        $statusChanged = $clinic->approval_status !== 'approved';
        
        $clinic->update([
            'approval_status' => 'approved',
        ]);
        
        if ($statusChanged) {
            try {
                // Send notification email
                $clinic->notify(new \App\Notifications\ClinicStatusUpdate(
                    $clinic, 
                    'approved'
                ));
                
                return back()->with('success', 'Clinic has been approved! Notification email has been sent.');
            } catch (\Exception $e) {
                \Log::error('Failed to send approval notification: ' . $e->getMessage());
                return back()->with('success', 'Clinic has been approved! However, the notification email could not be sent.');
            }
        }

        return back()->with('success', 'Clinic has been approved!');
    }

    /**
     * Reject a clinic registration.
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $clinic = Clinic::findOrFail($id);
        
        // Only send notification if status is changing
        $statusChanged = $clinic->approval_status !== 'rejected';
        
        $clinic->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        if ($statusChanged) {
            try {
                // Send notification email
                $clinic->notify(new \App\Notifications\ClinicStatusUpdate(
                    $clinic, 
                    'rejected', 
                    null,
                    null,
                    $request->rejection_reason
                ));
                
                return back()->with('success', 'Clinic has been rejected! Notification email has been sent.');
            } catch (\Exception $e) {
                \Log::error('Failed to send rejection notification: ' . $e->getMessage());
                return back()->with('success', 'Clinic has been rejected! However, the notification email could not be sent.');
            }
        }

        return back()->with('success', 'Clinic has been rejected!');
    }
} 