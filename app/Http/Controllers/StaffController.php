<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\TenantDatabaseService;
use App\Notifications\StaffCredentials;

class StaffController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        // Ensure we're connected to the tenant database
        $this->ensureTenantConnection();

        $staff = DB::connection('tenant')->table('users')
            ->where('role', '!=', 'owner')
            ->get();

        return view('staff.index', [
            'staff' => $staff,
            'isSidebar' => true
        ]);
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        return view('staff.create', [
            'isSidebar' => true
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        // Ensure we're connected to the tenant database
        $this->ensureTenantConnection();

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('tenant.users'),
            ],
            'phone' => 'required|string|max:20',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'employee_id' => 'required|string|max:50|unique:tenant.users,employee_id',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'role' => 'required|string|in:vet,vet_assistant,receptionist,admin,staff',
            'hire_date' => 'required|date',
            'specialization' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:100',
        ]);

        try {
            Log::info('Beginning staff member registration process', [
                'staff_name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role']
            ]);

            // Generate a secure random password
            $generatedPassword = Str::random(12);

            // Insert the staff member into the database
            $staffId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($generatedPassword),
                'role' => $validatedData['role'],
                'phone' => $validatedData['phone'],
                'dob' => $validatedData['dob'],
                'gender' => $validatedData['gender'],
                'employee_id' => $validatedData['employee_id'],
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'postal_code' => $validatedData['postal_code'],
                'hire_date' => $validatedData['hire_date'],
                'specialization' => $validatedData['specialization'] ?? null,
                'license_number' => $validatedData['license_number'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Staff member record created in tenant database', [
                'staff_id' => $staffId,
                'staff_name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role']
            ]);

            // Get current clinic information from session
            $clinicId = session('current_clinic_id');
            $clinic = \App\Models\Clinic::find($clinicId);
            $clinicName = $clinic ? $clinic->name : 'Our Veterinary Clinic';

            // Prepare credentials for email
            $credentials = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => $generatedPassword,
                'login_url' => route('login')
            ];

            // Send email notification
            $user = new \App\Models\User();
            $user->email = $validatedData['email'];
            $user->notify(new StaffCredentials($credentials, $clinicName));

            Log::info('Staff credentials email sent', [
                'email' => $validatedData['email'],
                'clinic_name' => $clinicName
            ]);

            return redirect()->route('staff.index')
                ->with('success', "Staff member {$validatedData['name']} added successfully. An email with login credentials has been sent to {$validatedData['email']}.");
        } catch (\Exception $e) {
            Log::error('Error creating staff member: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $validatedData
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating staff member: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        // Ensure we're connected to the tenant database
        $this->ensureTenantConnection();

        $staff = DB::connection('tenant')->table('users')->find($id);

        if (!$staff) {
            return redirect()->route('staff.index')
                ->with('error', 'Staff member not found.');
        }

        return view('staff.edit', [
            'staff' => $staff,
            'isSidebar' => true
        ]);
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        // Ensure we're connected to the tenant database
        $this->ensureTenantConnection();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('tenant.users')->ignore($id),
            ],
            'phone' => 'required|string|max:20',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenant.users')->ignore($id),
            ],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'role' => 'required|string|in:vet,vet_assistant,receptionist,admin,staff',
            'hire_date' => 'required|date',
            'specialization' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:100',
        ]);

        try {
            // Update the staff member
            DB::connection('tenant')->table('users')
                ->where('id', $id)
                ->update([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'dob' => $validatedData['dob'],
                    'gender' => $validatedData['gender'],
                    'employee_id' => $validatedData['employee_id'],
                    'address' => $validatedData['address'],
                    'city' => $validatedData['city'],
                    'state' => $validatedData['state'],
                    'postal_code' => $validatedData['postal_code'],
                    'role' => $validatedData['role'],
                    'hire_date' => $validatedData['hire_date'],
                    'specialization' => $validatedData['specialization'] ?? null,
                    'license_number' => $validatedData['license_number'] ?? null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('staff.index')
                ->with('success', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating staff member: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $validatedData,
                'id' => $id
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating staff member: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        // Ensure we're connected to the tenant database
        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->table('users')
                ->where('id', $id)
                ->delete();

            return redirect()->route('staff.index')
                ->with('success', 'Staff member deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting staff member: ' . $e->getMessage(), [
                'exception' => $e,
                'id' => $id
            ]);

            return redirect()->route('staff.index')
                ->with('error', 'Error deleting staff member: ' . $e->getMessage());
        }
    }

    /**
     * Ensure we're connected to the correct tenant database
     */
    private function ensureTenantConnection()
    {
        // Get clinic ID from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            abort(403, 'No clinic selected.');
        }
        
        // Get clinic from database
        $clinic = \App\Models\Clinic::find($clinicId);
        
        if (!$clinic) {
            abort(403, 'Clinic not found.');
        }
        
        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
    }
} 