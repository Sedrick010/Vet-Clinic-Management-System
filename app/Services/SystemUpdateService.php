<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use App\Models\ClinicUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SystemUpdateService
{
    /**
     * Check for available updates for a specific clinic
     *
     * @param Clinic $clinic
     * @return array
     */
    public function checkForUpdates(Clinic $clinic)
    {
        try {
            // Get current system version
            $currentVersion = $this->getClinicVersion($clinic);
            
            if (!$currentVersion) {
                Log::error('No current system version found');
                return [
                    'success' => false,
                    'message' => 'No current system version found'
                ];
            }
            
            // Get all available updates
            $availableUpdates = SystemUpdate::getAvailableUpdates();
            
            if ($availableUpdates->isEmpty()) {
                return [
                    'success' => true,
                    'has_updates' => false,
                    'current_version' => $currentVersion,
                    'updates' => []
                ];
            }
            
            // Check which updates have already been applied or dismissed
            $clinicUpdates = ClinicUpdate::where('clinic_id', $clinic->id)
                ->whereIn('system_update_id', $availableUpdates->pluck('id'))
                ->get()
                ->keyBy('system_update_id');
            
            // Filter updates that haven't been applied or dismissed
            $pendingUpdates = $availableUpdates->filter(function($update) use ($clinicUpdates, $currentVersion) {
                // Skip updates that are older than or equal to the current version
                $updateVersion = ltrim($update->version, 'v');
                if (version_compare($updateVersion, $currentVersion, '<=')) {
                    return false;
                }
                
                // Skip if already applied
                if (isset($clinicUpdates[$update->id]) && $clinicUpdates[$update->id]->is_applied) {
                    return false;
                }
                
                // Include if it's mandatory, even if dismissed
                if ($update->is_mandatory) {
                    return true;
                }
                
                // Skip if dismissed
                if (isset($clinicUpdates[$update->id]) && $clinicUpdates[$update->id]->is_dismissed) {
                    return false;
                }
                
                return true;
            });
            
            // Create update records for new updates
            foreach ($pendingUpdates as $update) {
                if (!isset($clinicUpdates[$update->id])) {
                    ClinicUpdate::create([
                        'clinic_id' => $clinic->id,
                        'system_update_id' => $update->id,
                        'is_applied' => false,
                        'is_dismissed' => false
                    ]);
                }
            }
            
            return [
                'success' => true,
                'has_updates' => $pendingUpdates->isNotEmpty(),
                'current_version' => $currentVersion,
                'updates' => $pendingUpdates->values(),
                'clinic_id' => $clinic->id
            ];
            
        } catch (Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while checking for updates: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get the current version for a specific clinic
     *
     * @param Clinic $clinic
     * @return string
     */
    public function getClinicVersion(Clinic $clinic): string
    {
        // Try to get clinic-specific version first
        $clinicSetting = $clinic->settings()->where('key', 'installed_version')->first();
        if ($clinicSetting && !empty($clinicSetting->value)) {
            return $clinicSetting->value;
        }
        
        // Fall back to system version if no clinic-specific version exists
        $systemVersion = SystemVersion::getCurrentVersion();
        if ($systemVersion) {
            return ltrim($systemVersion->version, 'v');
        }
        
        // Last resort: get from config
        return ltrim(config('self-update.version_installed'), 'v');
    }
    
    /**
     * Apply a specific update to a clinic
     *
     * @param Clinic $clinic
     * @param SystemUpdate $update
     * @return array
     */
    public function applyUpdate(Clinic $clinic, SystemUpdate $update)
    {
        try {
            DB::beginTransaction();
            
            // Find or create the clinic update record
            $clinicUpdate = ClinicUpdate::firstOrCreate([
                'clinic_id' => $clinic->id,
                'system_update_id' => $update->id
            ], [
                'is_applied' => false,
                'is_dismissed' => false
            ]);
            
            // If already applied, just return success
            if ($clinicUpdate->is_applied) {
                return [
                    'success' => true,
                    'message' => 'Update has already been applied',
                    'update' => $update
                ];
            }
            
            // Mark as applied
            $clinicUpdate->update([
                'is_applied' => true,
                'is_dismissed' => false, // Reset dismissed flag if it was set
                'applied_at' => Carbon::now(),
                'notes' => 'Applied by clinic user'
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Update applied successfully',
                'update' => $update
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error applying update: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'update_id' => $update->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while applying the update: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Dismiss an update for a clinic
     *
     * @param Clinic $clinic
     * @param SystemUpdate $update
     * @return array
     */
    public function dismissUpdate(Clinic $clinic, SystemUpdate $update)
    {
        try {
            // Can't dismiss mandatory updates
            if ($update->is_mandatory) {
                return [
                    'success' => false,
                    'message' => 'Cannot dismiss mandatory updates'
                ];
            }
            
            // Find or create the clinic update record
            $clinicUpdate = ClinicUpdate::firstOrCreate([
                'clinic_id' => $clinic->id,
                'system_update_id' => $update->id
            ], [
                'is_applied' => false,
                'is_dismissed' => false
            ]);
            
            // If already applied, can't dismiss
            if ($clinicUpdate->is_applied) {
                return [
                    'success' => false,
                    'message' => 'Cannot dismiss an already applied update'
                ];
            }
            
            // Mark as dismissed
            $clinicUpdate->update([
                'is_dismissed' => true,
                'dismissed_at' => Carbon::now(),
                'notes' => 'Dismissed by clinic user'
            ]);
            
            return [
                'success' => true,
                'message' => 'Update dismissed successfully',
                'update' => $update
            ];
            
        } catch (Exception $e) {
            Log::error('Error dismissing update: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'update_id' => $update->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while dismissing the update: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all pending updates for a clinic
     *
     * @param Clinic $clinic
     * @return array
     */
    public function getPendingUpdates(Clinic $clinic)
    {
        try {
            // Get current system version
            $currentVersion = SystemVersion::getCurrentVersion();
            
            if (!$currentVersion) {
                return [
                    'success' => false,
                    'message' => 'No current system version found'
                ];
            }
            
            // Get all available updates
            $availableUpdates = SystemUpdate::getAvailableUpdates();
            
            if ($availableUpdates->isEmpty()) {
                return [
                    'success' => true,
                    'has_updates' => false,
                    'current_version' => $currentVersion->version,
                    'updates' => []
                ];
            }
            
            // Check which updates have already been applied or dismissed
            $clinicUpdates = ClinicUpdate::where('clinic_id', $clinic->id)
                ->whereIn('system_update_id', $availableUpdates->pluck('id'))
                ->get()
                ->keyBy('system_update_id');
            
            // Filter updates that haven't been applied or dismissed
            $pendingUpdates = $availableUpdates->filter(function($update) use ($clinicUpdates) {
                // Skip if already applied
                if (isset($clinicUpdates[$update->id]) && $clinicUpdates[$update->id]->is_applied) {
                    return false;
                }
                
                // Include if it's mandatory, even if dismissed
                if ($update->is_mandatory) {
                    return true;
                }
                
                // Skip if dismissed
                if (isset($clinicUpdates[$update->id]) && $clinicUpdates[$update->id]->is_dismissed) {
                    return false;
                }
                
                return true;
            });
            
            // Sort updates by version (newest first)
            $pendingUpdates = $pendingUpdates->sortByDesc('version');
            
            return [
                'success' => true,
                'has_updates' => $pendingUpdates->isNotEmpty(),
                'current_version' => $currentVersion->version,
                'updates' => $pendingUpdates->values()
            ];
            
        } catch (Exception $e) {
            Log::error('Error getting pending updates: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving pending updates: ' . $e->getMessage()
            ];
        }
    }
} 