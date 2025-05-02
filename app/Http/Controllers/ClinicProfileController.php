<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SubscriptionService;

class ClinicProfileController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the clinic profile settings form.
     */
    public function edit(Request $request)
    {
        // Get the current clinic ID from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')
                ->with('error', 'No clinic selected. Please login again.');
        }
        
        $clinic = Clinic::findOrFail($clinicId);
        
        // Check if user has edit permissions (must be owner or admin)
        $canEdit = false;
        
        if (auth()->check() && auth()->user()->role === 'admin') {
            $canEdit = true;
        } elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $canEdit = ($tenantUser->role === 'owner' || $tenantUser->role === 'admin');
            
            // Log permission check
            Log::info('Clinic profile edit permission check', [
                'user_role' => $tenantUser->role,
                'can_edit' => $canEdit,
                'clinic_id' => $clinicId
            ]);
        }

        // Get the theme customization level based on subscription
        $themeCustomizationLevel = $this->subscriptionService->getThemeCustomizationLevel($clinic);
        
        return view('clinics.profile', [
            'clinic' => $clinic,
            'isSidebar' => true,
            'readOnly' => !$canEdit,
            'themeCustomizationLevel' => $themeCustomizationLevel
        ]);
    }
    
    /**
     * Update the clinic profile.
     */
    public function update(Request $request)
    {
        // Get the current clinic ID from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')
                ->with('error', 'No clinic selected. Please login again.');
        }
        
        $clinic = Clinic::findOrFail($clinicId);
        
        // Check if user has edit permissions (must be owner or admin)
        $canEdit = false;
        
        if (auth()->check() && auth()->user()->role === 'admin') {
            $canEdit = true;
        } elseif (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $canEdit = ($tenantUser->role === 'owner' || $tenantUser->role === 'admin');
        }
        
        if (!$canEdit) {
            return redirect()->route('clinic.profile')
                ->with('error', 'You do not have permission to edit clinic settings.');
        }
        
        // Get the theme customization level based on subscription
        $themeCustomizationLevel = $this->subscriptionService->getThemeCustomizationLevel($clinic);
        
        // Define allowed themes based on subscription
        $allowedThemes = ['default'];
        
        if ($themeCustomizationLevel === 'basic') {
            $allowedThemes = ['default', 'dark'];
        } elseif ($themeCustomizationLevel === 'full' || $themeCustomizationLevel === 'advanced') {
            $allowedThemes = ['default', 'dark', 'forest', 'sunset', 'vintage', 'blossom', 'lagoon', 'amber'];
        }
        
        // Define validation rules
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('clinics')->ignore($clinic->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
        
        // Only validate theme if customization is allowed
        if ($themeCustomizationLevel !== 'none') {
            $validationRules['theme'] = [
                'required', 
                'string', 
                Rule::in($allowedThemes)
            ];
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Prepare data for update
        $updateData = [
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'description' => $request->description,
        ];
        
        // Only update theme if customization is allowed
        if ($themeCustomizationLevel !== 'none' && in_array($request->theme, $allowedThemes)) {
            $updateData['theme'] = $request->theme;
            
            // Reset custom colors when selecting a predefined theme
            // This ensures only one theme system (either custom or predefined) is active at any time
            $updateData['custom_theme_colors'] = null;
        }
        
        // Update the clinic profile
        $clinic->update($updateData);
        
        // Handle logo upload
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $clinic->updateLogo($request->file('logo'));
        }
        
        // If this is a tenant user (clinic owner), update the clinic name in the tenant database
        if (session()->has('tenant_user')) {
            try {
                DB::connection('tenant')->table('clinic_settings')
                    ->where('id', 1)
                    ->update([
                        'clinic_name' => $request->name,
                        'clinic_address' => $request->address,
                        'clinic_phone' => $request->phone,
                        'clinic_email' => $request->email,
                        'updated_at' => now()
                    ]);
            } catch (\Exception $e) {
                // Log error but continue
                Log::error('Failed to update clinic settings in tenant database: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('clinic.profile')
            ->with('success', 'Clinic profile updated successfully.');
    }
} 