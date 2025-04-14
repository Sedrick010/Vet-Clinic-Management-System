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
        // Get the clinic
        $clinic = $this->getClinic($request);
        
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Ensure we're using the tenant connection
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Get all staff members from tenant database
        $staff = Staff::all();

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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Check if email already exists in tenant database
        if (Staff::where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'This email is already associated with a staff member.'])->withInput();
        }

        // Generate a random password
        $password = Str::random(12);

        // Create the staff member
        $staff = Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        // Send invitation email
        try {
            Mail::to($staff->email)->send(new StaffInvitation($staff, $clinic, $password));
        } catch (\Exception $e) {
            // Log the error but don't prevent staff creation
            logger()->error('Failed to send staff invitation email: ' . $e->getMessage());
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member added successfully! An invitation email has been sent.');
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);

        return view('staff.show', compact('staff', 'clinic'));
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);

        return view('staff.edit', compact('staff', 'clinic'));
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);

        // Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('staff')->ignore($staff->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['admin', 'doctor', 'receptionist', 'assistant'])],
            'is_active' => ['boolean'],
        ]);

        // Update the staff member
        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully!');
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);
        
        try {
            // Wrap in a transaction for data consistency
            DB::connection('tenant')->beginTransaction();
            
            // Create the deleted_staff table if it doesn't exist
            if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('deleted_staff')) {
                DB::connection('tenant')->statement(
                    'CREATE TABLE IF NOT EXISTS deleted_staff (
                        id INT PRIMARY KEY,
                        email VARCHAR(255) UNIQUE,
                        deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )'
                );
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
                        'deleted_at' => now()
                    ]);
            } else {
                // Store the deleted staff info for session validation
                DB::connection('tenant')->table('deleted_staff')->insert([
                    'id' => (int)$staff->id,
                    'email' => $staff->email,
                ]);
            }
            
            // Delete the staff member
            $staff->delete();
            
            // Commit the transaction
            DB::connection('tenant')->commit();
            
            return redirect()->route('staff.index')
                ->with('success', 'Staff member deleted successfully!');
        } catch (\Exception $e) {
            // Rollback transaction if anything goes wrong
            DB::connection('tenant')->rollBack();
            
            // Log the error
            logger()->error('Failed to delete staff member: ' . $e->getMessage());
            
            return redirect()->route('staff.index')
                ->with('error', 'Failed to delete staff member. Please try again later.');
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);

        // Generate a new password
        $password = Str::random(12);
        $staff->update([
            'password' => Hash::make($password)
        ]);

        // Send invitation email
        try {
            Mail::to($staff->email)->send(new StaffInvitation($staff, $clinic, $password));
            return redirect()->route('staff.index')
                ->with('success', 'Invitation email sent successfully!');
        } catch (\Exception $e) {
            logger()->error('Failed to send staff invitation email: ' . $e->getMessage());
            return redirect()->route('staff.index')
                ->with('error', 'Failed to send invitation email. Please try again later.');
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

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the staff member
        $staff = Staff::findOrFail($id);

        // Set a fixed password for testing
        $password = 'password123';
        $staff->update([
            'password' => Hash::make($password)
        ]);

        // Log the change
        \Illuminate\Support\Facades\Log::info('Staff password reset for debugging', [
            'staff_id' => $staff->id,
            'email' => $staff->email,
            'clinic_id' => $clinic->id,
            'new_password' => $password // Only log in development!
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Password reset to "password123" for debugging purposes.');
    }
}
