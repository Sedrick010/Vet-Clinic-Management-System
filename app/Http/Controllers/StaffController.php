<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Clinic;
use App\Mail\StaffInvitation;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Get the clinic from request or session
     */
    private function getClinic(Request $request)
    {
        // Get the clinic - either from Laravel Auth or tenant session
        $clinicId = null;
        
        if (Auth::check() && Auth::user()->role === 'admin') {
            // Admin users access - get clinic ID from request if provided
            $clinicId = $request->input('clinic_id', session('current_clinic_id'));
        } elseif (session()->has('tenant_user')) {
            // Tenant user access
            $clinicId = session('current_clinic_id');
        } else {
            // Fallback - try to get from session
            $clinicId = session('current_clinic_id');
        }
        
        // Ensure we have a clinic ID
        if (!$clinicId) {
            return null;
        }
        
        // Get the clinic
        return Clinic::find($clinicId);
    }

    /**
     * Display a listing of staff members
     */
    public function index(Request $request)
    {
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Add debug information
        $tenantDbName = config('database.connections.tenant.database');
        $tablesExist = false;
        $staffTableExists = false;

        try {
            // Ensure we're using the tenant connection
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Check if the staff table exists
            $staffTableExists = Schema::connection('tenant')->hasTable('staff');
            
            if (!$staffTableExists) {
                // Try to create the staff table directly
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `staff` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `phone` varchar(255) DEFAULT NULL,
                        `role` enum("admin","doctor","receptionist","assistant") DEFAULT "assistant",
                        `is_active` tinyint(1) DEFAULT 1,
                        `email_verified_at` timestamp NULL DEFAULT NULL,
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `staff_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                // Check again if the table was created
                $staffTableExists = Schema::connection('tenant')->hasTable('staff');
            }
            
            // Get all staff members from tenant database
            $staff = collect();
            if ($staffTableExists) {
                $staff = DB::connection('tenant')->table('staff')->get();
            }
        } catch (\Exception $e) {
            Log::error('Error in staff index: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database' => $tenantDbName
            ]);
            
            return view('staff.index', [
                'staff' => collect(),
                'clinic' => $clinic,
                'error' => 'Database error: ' . $e->getMessage(),
                'debug' => [
                    'tenant_db' => $tenantDbName,
                    'staff_table_exists' => $staffTableExists
                ]
            ]);
        }

        return view('staff.index', compact('staff', 'clinic'));
    }

    /**
     * Show the form for creating a new staff member
     */
    public function create(Request $request)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to add staff members.');
        }

        return view('staff.create', compact('clinic'));
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to add staff members.');
        }

        // Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['admin', 'doctor', 'receptionist', 'assistant'])],
        ]);

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                // Run the fix command if table doesn't exist
                \Artisan::call('tenant:fix', ['clinic_id' => $clinic->id]);
                
                if (!Schema::connection('tenant')->hasTable('staff')) {
                    return redirect()->route('staff.index')
                        ->with('error', 'Staff database table not found. Please contact support.');
                }
            }

            // Check if email already exists in tenant database
            $emailExists = DB::connection('tenant')
                ->table('staff')
                ->where('email', $validated['email'])
                ->exists();
                
            if ($emailExists) {
                return back()->withErrors(['email' => 'This email is already associated with a staff member.'])->withInput();
            }

            // Generate a random password
            $password = Str::random(12);

            // Create the staff member
            $staffId = DB::connection('tenant')->table('staff')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'phone' => $validated['phone'],
                'role' => $validated['role'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Get the new staff member data
            $staff = DB::connection('tenant')->table('staff')->where('id', $staffId)->first();
            
            // Create a Staff model instance for the mail
            $staffModel = new Staff((array)$staff);
            $staffModel->exists = true;
            $staffModel->id = $staffId;

            // Send invitation email
            Mail::to($validated['email'])->send(new StaffInvitation($staffModel, $clinic, $password));
            
            return redirect()->route('staff.index')
                ->with('success', 'Staff member added successfully! An invitation email has been sent.');
        } catch (\Exception $e) {
            Log::error('Failed to create staff member: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'tenant_db' => $clinic->database_name,
                'inputs' => $validated
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Failed to create staff member: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified staff member
     */
    public function show(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff database table not found. Please contact support.');
            }
            
            // Get the staff member
            $staffData = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staffData) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }
            
            // Convert to model instance for view compatibility
            $staff = new Staff((array)$staffData);
            $staff->exists = true;
            $staff->id = $id;
            
            // Ensure date attributes are Carbon instances
            $staff->created_at = isset($staffData->created_at) ? \Carbon\Carbon::parse($staffData->created_at) : null;
            $staff->updated_at = isset($staffData->updated_at) ? \Carbon\Carbon::parse($staffData->updated_at) : null;
            $staff->email_verified_at = isset($staffData->email_verified_at) ? \Carbon\Carbon::parse($staffData->email_verified_at) : null;

            return view('staff.show', compact('staff', 'clinic'));
        } catch (\Exception $e) {
            Log::error('Error showing staff: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Error accessing staff information: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified staff member
     */
    public function edit(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to edit staff members.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                // Run the fix command if table doesn't exist
                \Artisan::call('tenant:fix', ['clinic_id' => $clinic->id]);
                
                if (!Schema::connection('tenant')->hasTable('staff')) {
                    return redirect()->route('staff.index')
                        ->with('error', 'Staff database table not found. Please contact support.');
                }
            }
            
            // Get the staff member using DB facade instead of model for reliability
            $staffData = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staffData) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }
            
            // Convert to model instance for view compatibility
            $staff = new Staff((array)$staffData);
            $staff->exists = true;
            $staff->id = $id;
            
            // Ensure date attributes are Carbon instances
            $staff->created_at = isset($staffData->created_at) ? \Carbon\Carbon::parse($staffData->created_at) : null;
            $staff->updated_at = isset($staffData->updated_at) ? \Carbon\Carbon::parse($staffData->updated_at) : null;
            $staff->email_verified_at = isset($staffData->email_verified_at) ? \Carbon\Carbon::parse($staffData->email_verified_at) : null;
            
            return view('staff.edit', compact('staff', 'clinic'));
        } catch (\Exception $e) {
            Log::error('Error editing staff: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Error accessing staff information: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified staff member
     */
    public function update(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to update staff members.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff database table not found. Please contact support.');
            }
            
            // Get the staff member
            $staff = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staff) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }

            // Validate input
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 
                    'string', 
                    'email', 
                    'max:255', 
                    function ($attribute, $value, $fail) use ($id) {
                        // Custom validation for email uniqueness
                        $exists = DB::connection('tenant')
                            ->table('staff')
                            ->where('email', $value)
                            ->where('id', '<>', $id)
                            ->exists();
                            
                        if ($exists) {
                            $fail('The email has already been taken.');
                        }
                    }
                ],
                'phone' => ['nullable', 'string', 'max:20'],
                'role' => ['required', 'string', Rule::in(['admin', 'doctor', 'receptionist', 'assistant'])],
            ]);

            // Update the staff member using query builder
            DB::connection('tenant')->table('staff')
                ->where('id', $id)
                ->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'role' => $validated['role'],
                    'is_active' => $request->has('is_active') ? true : false,
                    'updated_at' => now()
                ]);

            return redirect()->route('staff.index')
                ->with('success', 'Staff member updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Error updating staff: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Error updating staff information: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member
     */
    public function destroy(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to delete staff members.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff database table not found. Please contact support.');
            }
            
            // Get the staff member
            $staff = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staff) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }
            
            // Wrap in a transaction for data consistency
            DB::connection('tenant')->beginTransaction();
            
            // Create the deleted_staff table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('deleted_staff')) {
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `deleted_staff` (
                        `id` bigint unsigned NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `phone` varchar(255) DEFAULT NULL,
                        `role` varchar(255) NOT NULL,
                        `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `deleted_by` bigint unsigned DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
            }
            
            // Check if email already exists in deleted_staff table
            $emailExists = DB::connection('tenant')
                ->table('deleted_staff')
                ->where('email', $staff->email)
                ->exists();
                
            if ($emailExists) {
                // Update the existing record instead of inserting a new one
                DB::connection('tenant')
                    ->table('deleted_staff')
                    ->where('email', $staff->email)
                    ->update([
                        'id' => (int)$staff->id,
                        'name' => $staff->name,
                        'role' => $staff->role,
                        'phone' => $staff->phone,
                        'deleted_at' => now()
                    ]);
            } else {
                // Store the deleted staff info for session validation
                DB::connection('tenant')->table('deleted_staff')->insert([
                    'id' => (int)$staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'role' => $staff->role,
                    'deleted_at' => now()
                ]);
            }
            
            // Delete the staff member
            DB::connection('tenant')->table('staff')->where('id', $id)->delete();
            
            // Commit the transaction
            DB::connection('tenant')->commit();
            
            return redirect()->route('staff.index')
                ->with('success', 'Staff member deleted successfully!');
        } catch (\Exception $e) {
            // Rollback transaction if anything goes wrong
            if (DB::connection('tenant')->transactionLevel() > 0) {
                DB::connection('tenant')->rollBack();
            }
            
            // Log the error
            Log::error('Failed to delete staff member: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Failed to delete staff member: ' . $e->getMessage());
        }
    }

    /**
     * Resend invitation email to staff member
     */
    public function resendInvitation(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to manage staff invitations.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff database table not found. Please contact support.');
            }
            
            // Get the staff member
            $staff = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staff) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }

            // Generate a new password
            $password = Str::random(12);
            
            // Update the staff member's password
            DB::connection('tenant')->table('staff')
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($password),
                    'updated_at' => now()
                ]);
            
            // Create a Staff model instance for the mail
            $staffModel = new Staff((array)$staff);
            $staffModel->exists = true;
            $staffModel->id = $id;

            // Send invitation email
            Mail::to($staff->email)->send(new StaffInvitation($staffModel, $clinic, $password));
            
            return redirect()->route('staff.index')
                ->with('success', 'Invitation email sent successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to send staff invitation email: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Failed to send invitation email: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset password for debugging purposes
     */
    public function resetPassword(Request $request, $id)
    {
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Check if user has permission to manage staff
        if (!$request->attributes->get('can_manage_staff', false)) {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to reset staff passwords.');
        }

        try {
            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Ensure connection is fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Verify staff table exists
            if (!Schema::connection('tenant')->hasTable('staff')) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff database table not found. Please contact support.');
            }
            
            // Get the staff member
            $staff = DB::connection('tenant')->table('staff')->where('id', $id)->first();
            
            if (!$staff) {
                return redirect()->route('staff.index')
                    ->with('error', 'Staff member not found.');
            }

            // Set a fixed password for testing
            $password = 'password123';
            
            // Update the staff member's password
            DB::connection('tenant')->table('staff')
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($password),
                    'updated_at' => now()
                ]);

            // Log the change
            Log::info('Staff password reset for debugging', [
                'staff_id' => $id,
                'email' => $staff->email,
                'clinic_id' => $clinic->id,
                'new_password' => $password // Only log in development!
            ]);

            return redirect()->route('staff.index')
                ->with('success', 'Password reset to "password123" for debugging purposes.');
                
        } catch (\Exception $e) {
            Log::error('Error resetting staff password: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'staff_id' => $id,
                'tenant_db' => $clinic->database_name
            ]);
            
            return redirect()->route('staff.index')
                ->with('error', 'Error resetting password: ' . $e->getMessage());
        }
    }
}
