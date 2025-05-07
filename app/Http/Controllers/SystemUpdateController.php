<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use App\Models\ClinicUpdate;
use App\Services\CustomUpdaterService;
use Carbon\Carbon;

class SystemUpdateController extends Controller
{
    protected $updater;
    protected $customUpdater;

    public function __construct(UpdaterManager $updater, CustomUpdaterService $customUpdater)
    {
        $this->updater = $updater;
        $this->customUpdater = $customUpdater;
    }

    /**
     * Display available updates
     */
    public function index()
    {
        $updates = SystemUpdate::orderBy('created_at', 'desc')->get();
        $hasNewUpdate = false;
        $latestVersion = null;

        try {
            // Check for updates using our custom service
            $hasNewUpdate = $this->customUpdater->isNewVersionAvailable();
            $latestVersion = $this->customUpdater->getLatestVersion();
            
            // If a new version is found that isn't in our system_versions table
            if ($latestVersion && !SystemVersion::where('version', $latestVersion)->exists()) {
                $hasNewUpdate = true;
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
        }

        return view('system-updates.index', [
            'updates' => $updates,
            'hasNewUpdate' => $hasNewUpdate,
            'latestVersion' => $latestVersion
        ]);
    }

    /**
     * Check for updates and create records
     */
    public function checkForUpdates()
    {
        try {
            $isAvailable = $this->customUpdater->isNewVersionAvailable();
            
            if ($isAvailable) {
                $latestVersionDetails = $this->customUpdater->getLatestVersionDetails();
                $version = $latestVersionDetails['version'] ?? null;
                
                if ($version && !SystemVersion::where('version', $version)->exists()) {
                    // Create the version record
                    $versionRecord = SystemVersion::create([
                        'version' => $version,
                        'name' => "Version {$version}",
                        'description' => $latestVersionDetails['description'] ?? "Update to version {$version}",
                        'is_current' => false,
                        'released_at' => Carbon::now(),
                    ]);
                    
                    // Create system update record
                    $systemUpdate = SystemUpdate::create([
                        'version' => $version,
                        'name' => "Version {$version} Update",
                        'description' => $latestVersionDetails['description'] ?? "Automatic update from GitHub release",
                        'changes' => $latestVersionDetails['description'] ?? "Update to version {$version}",
                        'features' => "New features in version {$version}",
                        'bug_fixes' => "Bug fixes in version {$version}",
                        'is_critical' => false,
                        'is_security' => false,
                        'is_mandatory' => false,
                        'available_from' => Carbon::now(),
                    ]);
                    
                    // Create clinic update records for all clinics
                    $clinicIds = DB::table('clinics')->pluck('id');
                    foreach ($clinicIds as $clinicId) {
                        ClinicUpdate::create([
                            'clinic_id' => $clinicId,
                            'system_update_id' => $systemUpdate->id,
                            'is_applied' => false,
                            'is_dismissed' => false
                        ]);
                    }
                    
                    return redirect()->back()->with('success', "New update {$version} is available!");
                } else {
                    return redirect()->back()->with('info', 'This version is already in the system.');
                }
            }
            
            return redirect()->back()->with('info', 'No new updates available');
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }

    /**
     * Apply the update
     */
    public function update()
    {
        try {
            // Since we're just using the self-updater to track versions, we'll display success
            $latestVersionDetails = $this->customUpdater->getLatestVersionDetails();
            $latestVersion = $latestVersionDetails['version'] ?? null;
            
            if ($latestVersion) {
                // Update status in database
                if ($update = SystemUpdate::where('version', $latestVersion)->first()) {
                    // Get clinic ID from auth user or session
                    $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
                    
                    if ($clinicId) {
                        if ($clinicUpdate = ClinicUpdate::where('system_update_id', $update->id)
                                                       ->where('clinic_id', $clinicId)
                                                       ->first()) {
                            $clinicUpdate->is_applied = true;
                            $clinicUpdate->applied_at = Carbon::now();
                            $clinicUpdate->save();
                        }
                    }
                }
                
                return redirect()->back()->with('success', "System has been updated to version {$latestVersion} successfully!");
            }
            
            return redirect()->back()->with('error', 'No update version found');
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
} 