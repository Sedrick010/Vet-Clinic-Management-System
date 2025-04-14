<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthTenantStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access if the user is authenticated with Laravel Auth
        if (auth()->check()) {
            $user = Auth::user();
            // Admin users can manage all clinics
            if ($user->role === 'admin') {
                // Add staff management permission flag
                $request->attributes->add(['can_manage_staff' => true]);
                
                // Log permission assignment (in local environment)
                if (app()->environment('local')) {
                    Log::info('System admin granted staff management permissions', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role
                    ]);
                }
                
                return $next($request);
            }
        }
        
        // Check if user is authenticated as a tenant user through session
        if (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Check if this staff account has been deleted
            try {
                if (DB::connection('tenant')->getSchemaBuilder()->hasTable('deleted_staff')) {
                    $deleted = DB::connection('tenant')
                        ->table('deleted_staff')
                        ->where('id', $tenantUser->id)
                        ->orWhere('email', $tenantUser->email)
                        ->exists();
                    
                    if ($deleted) {
                        // Staff account has been deleted, invalidate session
                        Log::info('Invalidating session for deleted staff member', [
                            'staff_id' => $tenantUser->id,
                            'email' => $tenantUser->email
                        ]);
                        
                        // Clear all session data
                        Session::flush();
                        
                        // Redirect to login with message
                        return redirect()->route('login')
                            ->with('error', 'Your account has been deactivated. Please contact the clinic administrator.');
                    }
                }
            } catch (\Exception $e) {
                // Log the error but allow the request to continue
                Log::error('Error checking for deleted staff: ' . $e->getMessage());
            }
            
            // Only clinic owners can manage staff (role is 'owner' or 'admin')
            $canManageStaff = ($tenantUser->role === 'owner' || $tenantUser->role === 'admin');
            $request->attributes->add([
                'can_manage_staff' => $canManageStaff,
                'current_staff_id' => $tenantUser->id
            ]);
            
            // Log permission assignment (in local environment)
            if (app()->environment('local')) {
                Log::info('Tenant user staff management permissions check', [
                    'staff_id' => $tenantUser->id,
                    'email' => $tenantUser->email,
                    'role' => $tenantUser->role,
                    'can_manage_staff' => $canManageStaff
                ]);
            }
            
            return $next($request);
        }

        return redirect()->route('login');
    }
} 