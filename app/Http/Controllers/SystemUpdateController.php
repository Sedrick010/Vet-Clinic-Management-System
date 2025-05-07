<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SystemUpdateService;
use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use Illuminate\Support\Facades\Log;

class SystemUpdateController extends Controller
{
    protected $systemUpdateService;

    public function __construct(SystemUpdateService $systemUpdateService)
    {
        $this->systemUpdateService = $systemUpdateService;
    }

    /**
     * Show the updates dashboard
     */
    public function index(Request $request)
    {
        // Get the current clinic from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')
                ->with('error', 'No clinic selected. Please login again.');
        }
        
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        
        // Check for updates
        $updateCheck = $this->systemUpdateService->checkForUpdates($clinic);
        
        // Get current version
        $currentVersion = SystemVersion::getCurrentVersion();
        
        return view('system.updates.index', [
            'clinic' => $clinic,
            'updateCheck' => $updateCheck,
            'currentVersion' => $currentVersion,
            'isSidebar' => true
        ]);
    }

    /**
     * Show details for a specific update
     */
    public function show(Request $request, $id)
    {
        // Get the current clinic from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')
                ->with('error', 'No clinic selected. Please login again.');
        }
        
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        
        // Get the update
        $update = SystemUpdate::findOrFail($id);
        
        // Get update status for this clinic
        $clinicUpdate = \App\Models\ClinicUpdate::where('clinic_id', $clinic->id)
            ->where('system_update_id', $update->id)
            ->first();
            
        return view('system.updates.show', [
            'clinic' => $clinic,
            'update' => $update,
            'clinicUpdate' => $clinicUpdate,
            'isSidebar' => true
        ]);
    }

    /**
     * Check for system updates
     */
    public function check(Request $request)
    {
        // Get the current clinic from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic selected. Please login again.'
            ]);
        }
        
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        
        // Check for updates
        $updateCheck = $this->systemUpdateService->checkForUpdates($clinic);
        
        return response()->json($updateCheck);
    }

    /**
     * Apply a system update
     */
    public function apply(Request $request, $id)
    {
        // Get the current clinic from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic selected. Please login again.'
            ]);
        }
        
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        
        // Get the update
        $update = SystemUpdate::findOrFail($id);
        
        // Apply the update
        $result = $this->systemUpdateService->applyUpdate($clinic, $update);
        
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('system.updates.show', $update->id)
                ->with('success', $result['message']);
        } else {
            return redirect()->route('system.updates.show', $update->id)
                ->with('error', $result['message']);
        }
    }

    /**
     * Dismiss a system update
     */
    public function dismiss(Request $request, $id)
    {
        // Get the current clinic from session
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic selected. Please login again.'
            ]);
        }
        
        $clinic = \App\Models\Clinic::findOrFail($clinicId);
        
        // Get the update
        $update = SystemUpdate::findOrFail($id);
        
        // Dismiss the update
        $result = $this->systemUpdateService->dismissUpdate($clinic, $update);
        
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('system.updates.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('system.updates.show', $update->id)
                ->with('error', $result['message']);
        }
    }
} 