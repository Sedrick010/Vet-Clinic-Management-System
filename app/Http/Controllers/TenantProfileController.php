<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TenantProfileController extends Controller
{
    /**
     * Display the tenant user's profile form.
     */
    public function edit(Request $request): View
    {
        if (!session()->has('tenant_user')) {
            abort(403, 'Unauthorized action.');
        }

        $tenantUser = (object)session('tenant_user');
        
        return view('tenant.profile.edit', [
            'user' => $tenantUser,
            'isSidebar' => true,
        ]);
    }

    /**
     * Update the tenant user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        if (!session()->has('tenant_user')) {
            abort(403, 'Unauthorized action.');
        }
        
        $tenantUser = (object)session('tenant_user');
        $clinicId = session('current_clinic_id');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($tenantUser) {
                    // Check if email exists for another staff member
                    $count = DB::connection('tenant')
                        ->table('staff')
                        ->where('email', $value)
                        ->where('id', '!=', $tenantUser->id)
                        ->count();
                    
                    if ($count > 0) {
                        $fail('The email has already been taken by another staff member.');
                    }
                },
            ],
        ]);
        
        // Update in tenant database
        DB::connection('tenant')->table('staff')
            ->where('id', $tenantUser->id)
            ->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'updated_at' => now(),
            ]);
        
        // Update session data
        $updatedStaff = DB::connection('tenant')
            ->table('staff')
            ->where('id', $tenantUser->id)
            ->first();
            
        $sessionData = (array)$updatedStaff;
        session(['tenant_user' => $sessionData]);

        return Redirect::route('tenant.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the tenant user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        if (!session()->has('tenant_user')) {
            abort(403, 'Unauthorized action.');
        }
        
        $tenantUser = (object)session('tenant_user');
        
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($tenantUser) {
                // Verify current password
                $staff = DB::connection('tenant')
                    ->table('staff')
                    ->where('id', $tenantUser->id)
                    ->first();
                
                if (!$staff || !Hash::check($value, $staff->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        
        // Update password in tenant database
        DB::connection('tenant')->table('staff')
            ->where('id', $tenantUser->id)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);
        
        return Redirect::route('tenant.profile.edit')->with('status', 'password-updated');
    }
} 