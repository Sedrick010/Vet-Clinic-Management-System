<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ClinicController extends Controller
{
    /**
     * Display the clinic details.
     */
    public function show(Clinic $clinic): View
    {
        return view('admin.clinics.show', [
            'clinic' => $clinic,
            'isSidebar' => true,
        ]);
    }

    /**
     * Toggle the active status of a clinic
     */
    public function toggleActive(Clinic $clinic)
    {
        $previousStatus = $clinic->is_active;
        $clinic->is_active = !$previousStatus;
        $clinic->save();

        Log::info('Clinic status changed', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'previous_status' => $previousStatus ? 'active' : 'inactive',
            'new_status' => $clinic->is_active ? 'active' : 'inactive',
            'changed_by' => auth()->user()->id
        ]);

        return redirect()->route('admin.clinics.show', $clinic)
            ->with('success', 'Clinic ' . ($clinic->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }

    /**
     * Toggle the enabled status of a clinic
     */
    public function toggleEnabled(Clinic $clinic, Request $request)
    {
        $previousEnabled = $clinic->is_enabled;
        $clinic->is_enabled = !$previousEnabled;
        
        // If disabling, store the reason
        if (!$clinic->is_enabled) {
            $clinic->disable_reason = $request->input('disable_reason');
        } else {
            $clinic->disable_reason = null; // Clear the reason when enabling
        }
        
        $clinic->save();

        Log::info('Clinic enabled status changed', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'previous_status' => $previousEnabled ? 'enabled' : 'disabled',
            'new_status' => $clinic->is_enabled ? 'enabled' : 'disabled',
            'changed_by' => auth()->user()->id,
            'disable_reason' => $clinic->disable_reason
        ]);

        return redirect()->route('admin.clinics.show', $clinic)
            ->with('success', 'Clinic ' . ($clinic->is_enabled ? 'enabled' : 'disabled') . ' successfully.');
    }
} 