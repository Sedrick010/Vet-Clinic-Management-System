<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\SystemVersion;
use Illuminate\Support\Facades\Log;

class ClinicRegistrationService
{
    /**
     * Assign the latest system version to a newly registered clinic
     *
     * @param Clinic $clinic
     * @return bool
     */
    public function assignLatestVersion(Clinic $clinic): bool
    {
        try {
            // Get the latest stable version
            $latestVersion = SystemVersion::where('is_current', true)
                ->first();
            
            if (!$latestVersion) {
                // Fallback to the latest version by release date
                $latestVersion = SystemVersion::orderBy('released_at', 'desc')
                    ->first();
            }
            
            if (!$latestVersion) {
                // If no versions exist, use the config value
                $versionValue = config('self-update.version_installed');
            } else {
                $versionValue = ltrim($latestVersion->version, 'v');
            }
            
            // Create the clinic setting
            $clinic->settings()->create([
                'key' => 'installed_version',
                'value' => $versionValue,
                'description' => 'Automatically assigned during clinic registration'
            ]);
            
            Log::info('Assigned version to new clinic', [
                'clinic_id' => $clinic->id,
                'version' => $versionValue
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to assign version to new clinic', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
} 