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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Generate a random password
            $generatedPassword = Str::random(12);

            // Log the start of clinic registration
            Log::info('Beginning clinic registration process', [
                'clinic_name' => $request->name,
                'subdomain' => $request->subdomain,
                'email' => $request->email
            ]);
            
            // Generate a database name but don't create the database yet
            $tenantDatabaseService = app(TenantDatabaseService::class);
            $databaseName = 'vet_clinic_' . Str::slug($request->name) . '_' . Str::lower(Str::random(8));
            
            Log::info('Database name generated for clinic (will be created on approval)', [
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
                'owner_email' => $request->owner_email,
                'owner_name' => $request->owner_name,
                'temp_password' => $generatedPassword,
            ]);

            Log::info('Clinic record created in central database', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'subdomain' => $clinic->subdomain
            ]);
            
            // Send registration notification
            try {
                $clinic->notify(new \App\Notifications\ClinicRegistrationNotification(
                    $clinic,
                    $request->owner_email,
                    $request->owner_name
                ));
                Log::info('Registration notification sent', [
                    'clinic_id' => $clinic->id,
                    'owner_email' => $request->owner_email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send registration notification: ' . $e->getMessage(), [
                    'clinic_id' => $clinic->id,
                    'owner_email' => $request->owner_email
                ]);
            }

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
            $successMessage = "Thank you for registering your clinic {$clinic->name}! Your registration is pending approval. You will receive an email once it's approved.";
            
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

        // Update session data for the pending page
        session([
            'pending_clinic_id' => $clinic->id,
            'pending_clinic_name' => $clinic->name,
            'pending_clinic_status' => 'approved',
            'pending_clinic_subdomain' => $clinic->subdomain
        ]);
        
        if ($statusChanged) {
            try {
                // Now create and set up the tenant database only after approval
                $tenantDatabaseService = app(TenantDatabaseService::class);
                
                // Set up the tenant database (this will create tables and owner account)
                $tenantDatabaseService->setupTenantDatabase($clinic);
                
                // Send notification email with owner details
                $clinic->notify(new \App\Notifications\ClinicStatusUpdate(
                    $clinic, 
                    'approved',
                    $clinic->owner_email, // Use the owner_email from the clinic record
                    $clinic->owner_name,  // Use the owner_name from the clinic record
                ));
                
                return back()->with('success', 'Clinic has been approved! Database has been created and notification email has been sent.');
            } catch (\Exception $e) {
                \Log::error('Failed to send approval notification: ' . $e->getMessage());
                return back()->with('success', 'Clinic has been approved! However, there was an issue with database setup or notification.');
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
        
        // Only process if status is changing
        $statusChanged = $clinic->approval_status !== 'rejected';
        
        // Check if clinic was previously approved and now being rejected
        $wasApproved = $clinic->approval_status === 'approved';
        
        $clinic->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        // Update session data for the pending page
        session([
            'pending_clinic_id' => $clinic->id,
            'pending_clinic_name' => $clinic->name,
            'pending_clinic_status' => 'rejected',
            'pending_clinic_reason' => $request->rejection_reason,
            'pending_clinic_subdomain' => $clinic->subdomain
        ]);
        
        // If clinic was previously approved, we should clean up their database
        if ($wasApproved) {
            try {
                $tenantDatabaseService = app(TenantDatabaseService::class);
                
                // Check if database exists, if so, delete it
                if ($tenantDatabaseService->databaseExists($clinic->database_name)) {
                    Log::info('Deleting database of rejected clinic that was previously approved', [
                        'clinic_id' => $clinic->id,
                        'database_name' => $clinic->database_name
                    ]);
                    
                    // Drop the database
                    DB::statement("DROP DATABASE IF EXISTS `" . str_replace('`', '', $clinic->database_name) . "`");
                    
                    Log::info('Successfully deleted database of rejected clinic', [
                        'clinic_id' => $clinic->id,
                        'database_name' => $clinic->database_name
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete database of rejected clinic: ' . $e->getMessage(), [
                    'clinic_id' => $clinic->id,
                    'database_name' => $clinic->database_name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($statusChanged) {
            try {
                // For rejecting a clinic, we don't need to access the tenant database
                // as it might not exist or we just deleted it
                
                // Send notification email with rejection reason
                $clinic->notify(new \App\Notifications\ClinicStatusUpdate(
                    $clinic, 
                    'rejected', 
                    $clinic->owner_email,
                    $clinic->owner_name,
                    $request->rejection_reason
                ));
                
                return back()->with('success', 'Clinic has been rejected! Notification email has been sent.' . 
                    ($wasApproved ? ' Database has been cleaned up.' : ''));
            } catch (\Exception $e) {
                \Log::error('Failed to send rejection notification: ' . $e->getMessage());
                return back()->with('success', 'Clinic has been rejected! However, the notification email could not be sent.' . 
                    ($wasApproved ? ' Database has been cleaned up.' : ''));
            }
        }

        return back()->with('success', 'Clinic has been rejected!' . ($wasApproved ? ' Database has been cleaned up.' : ''));
    }

    /**
     * Delete a clinic registration.
     */
    public function destroy($id): RedirectResponse
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $clinic = Clinic::findOrFail($id);

        try {
            // Delete the clinic's database if it exists
            $tenantDatabaseService = app(TenantDatabaseService::class);
            
            // First check if database exists
            if ($tenantDatabaseService->databaseExists($clinic->database_name)) {
                Log::info('Deleting database of clinic being deleted', [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'database_name' => $clinic->database_name
                ]);
                
                // Drop the database with proper SQL injection prevention
                DB::statement("DROP DATABASE IF EXISTS `" . str_replace('`', '', $clinic->database_name) . "`");
                
                Log::info('Successfully deleted clinic database', [
                    'clinic_id' => $clinic->id,
                    'database_name' => $clinic->database_name
                ]);
            } else {
                Log::info('No database found for clinic being deleted', [
                    'clinic_id' => $clinic->id,
                    'database_name' => $clinic->database_name
                ]);
            }

            // Delete the clinic record
            $clinic->delete();

            return back()->with('success', 'Clinic registration and all associated data have been deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting clinic: ' . $e->getMessage(), [
                'clinic_id' => $id,
                'database_name' => $clinic->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while deleting the clinic registration: ' . $e->getMessage());
        }
    }

    /**
     * Recreate a clinic's database if it doesn't exist.
     * This is useful for fixing issues where a clinic is approved but its database wasn't created.
     */
    public function recreateDatabase($id): RedirectResponse
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $clinic = Clinic::findOrFail($id);
        
        // Only allow recreation for approved clinics
        if ($clinic->approval_status !== 'approved') {
            return back()->with('error', 'Only approved clinics can have their databases recreated.');
        }
        
        $tenantDatabaseService = app(TenantDatabaseService::class);
        
        // Check if the database already exists
        if ($tenantDatabaseService->databaseExists($clinic->database_name)) {
            return back()->with('info', 'Database already exists for this clinic.');
        }
        
        try {
            // Create the database using plain SQL
            Log::info('Recreating database for approved clinic', [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name
            ]);
            
            DB::statement("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $clinic->database_name) . "`");
            
            // Set up the tenant database schema
            $tenantDatabaseService->setupTenantDatabase($clinic);
            
            // Switch to tenant database to create the user
            $tenantDatabaseService->switchToTenant($clinic);
            
            // Create the clinic owner user in the tenant database
            $userId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $clinic->owner_name,
                'email' => $clinic->owner_email,
                'password' => Hash::make($clinic->temp_password),
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Switch back to main database
            $tenantDatabaseService->switchToMain();
            
            Log::info('Successfully recreated database and owner account', [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name,
                'owner_id' => $userId
            ]);
            
            return back()->with('success', "Database for {$clinic->name} has been successfully created.");
        } catch (\Exception $e) {
            Log::error('Error recreating clinic database: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', "Failed to recreate database: {$e->getMessage()}");
        }
    }
} 