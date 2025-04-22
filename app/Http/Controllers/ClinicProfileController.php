<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClinicProfileController extends Controller
{
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
        
        return view('clinics.profile', [
            'clinic' => $clinic,
            'isSidebar' => true
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
        
        // Validate the request
        $validator = Validator::make($request->all(), [
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
        ]);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update the clinic profile
        $clinic->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'description' => $request->description,
        ]);
        
        // Handle logo upload
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $clinic->updateLogo($request->file('logo'));
        }
        
        return redirect()->route('clinic.profile')
            ->with('success', 'Clinic profile updated successfully.');
    }
} 