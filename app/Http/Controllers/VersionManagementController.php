<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use App\Models\ClinicUpdate;
use App\Services\SystemUpdateService;
use Codedge\Updater\UpdaterManager;
use App\Services\CustomUpdaterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\VersionDeploymentService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class VersionManagementController extends Controller
{
    protected $systemUpdateService;
    protected $updater;
    protected $customUpdater;
    protected $versionDeployment;
    
    public function __construct(
        SystemUpdateService $systemUpdateService,
        UpdaterManager $updater,
        CustomUpdaterService $customUpdater,
        VersionDeploymentService $versionDeployment
    )
    {
        $this->systemUpdateService = $systemUpdateService;
        $this->updater = $updater;
        $this->customUpdater = $customUpdater;
        $this->versionDeployment = $versionDeployment;
    }
    
    /**
     * Display version management interface
     */
    public function index(Request $request)
    {
        // Get current version
        $currentVersion = config('self-update.version_installed', '1.0.0');
        
        // Check for new updates
        $hasNewUpdate = false;
        $latestVersion = null;
        $allReleases = [];
        
        try {
            // Get all GitHub releases
            $allReleases = $this->customUpdater->getAllReleases(20);
            
            // Check for updates using our custom service
            $hasNewUpdate = $this->customUpdater->isNewVersionAvailable();
            $latestVersion = $this->customUpdater->getLatestVersion();
            
            // Make sure the current version is marked as current in the database
            $currentVersionRecord = SystemVersion::where('version', $currentVersion)->first();
            
            if ($currentVersionRecord) {
                // If the current version exists in the database but isn't marked as current
                if (!$currentVersionRecord->is_current) {
                    // Mark all versions as not current
                    SystemVersion::where('is_current', true)->update(['is_current' => false]);
                    // Mark this version as current
                    $currentVersionRecord->is_current = true;
                    $currentVersionRecord->save();
                }
            } else {
                // If the current version isn't in the database, create it
                SystemVersion::create([
                    'version' => $currentVersion,
                    'name' => "Version {$currentVersion}",
                    'description' => "Current installed version",
                    'is_current' => true,
                    'released_at' => Carbon::now(),
                ]);
            }
            
            // If a new version is found that isn't in our system_versions table
            if ($latestVersion && !SystemVersion::where('version', $latestVersion)->exists()) {
                $hasNewUpdate = true;
                
                // Create the version record
                SystemVersion::create([
                    'version' => $latestVersion,
                    'name' => "Version {$latestVersion}",
                    'description' => "Latest version from GitHub",
                    'is_current' => false,
                    'released_at' => Carbon::now(),
                ]);
            }
            
            // Update all existing versions with GitHub details if available
            foreach ($allReleases as $release) {
                $version = $release['version'] ?? null;
                if (!$version) continue;
                
                $versionRecord = SystemVersion::where('version', $version)->first();
                if ($versionRecord) {
                    // Update with GitHub information if not already set
                    if (!$versionRecord->description || $versionRecord->description === "Latest version from GitHub" || $versionRecord->description === "Update to version {$version}") {
                        $versionRecord->description = $release['description'] ?? "Version {$version}";
                    }
                    
                    if (!$versionRecord->released_at && isset($release['published_at'])) {
                        $versionRecord->released_at = new Carbon($release['published_at']);
                    }
                    
                    if (!$versionRecord->name || $versionRecord->name === "Version {$version}") {
                        $versionRecord->name = $release['name'] ?? "Version {$version}";
                    }
                    
                    $versionRecord->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
        }
        
        // Get all available versions, sorted by version number (newest first)
        $versions = SystemVersion::orderBy('released_at', 'desc')->get();
        
        return view('system.versions.manage', [
            'currentVersion' => $currentVersion,
            'versions' => $versions,
            'hasNewUpdate' => $hasNewUpdate,
            'latestVersion' => $latestVersion,
            'allReleases' => $allReleases
        ]);
    }
    
    /**
     * Apply update to a specific version
     */
    public function update($versionId)
    {
        try {
            $version = SystemVersion::findOrFail($versionId);
            
            // Download the specific version ZIP file
            $zipPath = $this->versionDeployment->downloadVersion($version->version);
            
            if (!$zipPath) {
                return redirect()->route('version.manage')
                    ->with('error', "Failed to download version {$version->version}. Check logs for details.");
            }
            
            // Deploy the version from the zip file
            $deployed = $this->versionDeployment->deployVersion($version->version);
            
            if (!$deployed) {
                return redirect()->route('version.manage')
                    ->with('error', "Failed to deploy version {$version->version}. Check logs for details.");
            }
            
            return redirect()->route('version.manage')
                ->with('success', "Successfully updated to version {$version->version}");
        } catch (\Exception $e) {
            Log::error('Failed to update version: ' . $e->getMessage());
            return redirect()->route('version.manage')
                ->with('error', 'Failed to update version: ' . $e->getMessage());
        }
    }
    
    /**
     * Check for updates from GitHub
     */
    public function checkForUpdates()
    {
        try {
            // Force a fresh check for updates from GitHub
            $hasUpdates = $this->customUpdater->forceRefreshUpdateCheck();
            
            // Get all releases from GitHub, not just the newest one
            $allReleases = $this->customUpdater->getAllReleases(10); // Get top 10 releases
            $importedCount = 0;
            
            if (!empty($allReleases)) {
                foreach ($allReleases as $releaseDetails) {
                    $version = $releaseDetails['version'] ?? null;
                    
                    if ($version && !SystemVersion::where('version', $version)->exists()) {
                        // Create the version record
                        SystemVersion::create([
                            'version' => $version,
                            'name' => $releaseDetails['name'] ?? "Version {$version}",
                            'description' => $releaseDetails['description'] ?? "Update to version {$version}",
                            'is_current' => false,
                            'released_at' => isset($releaseDetails['published_at']) 
                                ? new Carbon($releaseDetails['published_at']) 
                                : Carbon::now(),
                        ]);
                        $importedCount++;
                    }
                }
                
                if ($importedCount > 0) {
                    return redirect()->route('version.manage')->with('success', "Imported {$importedCount} new version(s) from GitHub!");
                } else if ($hasUpdates) {
                    // We have updates but they're already imported
                    return redirect()->route('version.manage')->with('info', 'Found updates but they are already in the system.');
                } else {
                    // No new versions found
                    return redirect()->route('version.manage')->with('info', 'You are already on the latest version. No new updates available.');
                }
            } else {
                return redirect()->route('version.manage')->with('info', 'No releases found on GitHub.');
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
            return redirect()->route('version.manage')->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }
    
    /**
     * Update the installed version in the .env file
     */
    private function updateInstalledVersion(string $newVersion): bool
    {
        try {
            // Update the .env file
            $envFilePath = base_path('.env');
            $envFileContent = file_get_contents($envFilePath);
            
            if (preg_match('/SELF_UPDATER_VERSION_INSTALLED=(.*)/', $envFileContent)) {
                // If the version variable exists, update it
                $envFileContent = preg_replace(
                    '/SELF_UPDATER_VERSION_INSTALLED=(.*)/', 
                    'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion, 
                    $envFileContent
                );
            } else {
                // If the version variable doesn't exist, add it
                $envFileContent .= "\nSELF_UPDATER_VERSION_INSTALLED={$newVersion}\n";
            }
            
            file_put_contents($envFilePath, $envFileContent);
            
            // Update the config value for the current request
            config(['self-update.version_installed' => $newVersion]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update installed version in .env file: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Roll back to a previous version
     */
    public function rollback(Request $request, $versionId)
    {
        try {
            $version = SystemVersion::findOrFail($versionId);
            $currentVersion = config('self-update.version_installed', '1.0.0');
            
            // Check if trying to rollback to current version
            if ($version->version === $currentVersion) {
                return redirect()->route('version.manage')
                    ->with('info', 'You are already on this version.');
            }
            
            // Download the specific version ZIP file
            $zipPath = $this->versionDeployment->downloadVersion($version->version);
            
            if (!$zipPath) {
                return redirect()->route('version.manage')
                    ->with('error', "Failed to download version {$version->version} for rollback. Check logs for details.");
            }
            
            // Deploy the version from the zip file
            $deployed = $this->versionDeployment->deployVersion($version->version);
            
            if (!$deployed) {
                return redirect()->route('version.manage')
                    ->with('error', "Failed to rollback to version {$version->version}. Check logs for details.");
            }
            
            return redirect()->route('version.manage')
                ->with('success', "Successfully rolled back to version {$version->version}");
            
        } catch (\Exception $e) {
            Log::error('Failed to rollback version: ' . $e->getMessage());
            return redirect()->route('version.manage')
                ->with('error', 'Failed to roll back: ' . $e->getMessage());
        }
    }
    
    /**
     * List all available version backups
     */
    public function listBackups()
    {
        try {
            $backupPath = storage_path('app/version-backups');
            
            // Ensure the backup directory exists
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            // Get all backup files
            $files = File::files($backupPath);
            $backups = [];
            
            foreach ($files as $file) {
                if (str_ends_with($file->getFilename(), '.zip')) {
                    // Extract version and date from filename (format: backup_v1.0.0_2023-01-01_12-30-45.zip)
                    $filename = $file->getFilename();
                    preg_match('/backup_(.+?)_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.zip/', $filename, $matches);
                    
                    $version = $matches[1] ?? 'Unknown';
                    $dateStr = $matches[2] ?? '';
                    
                    // Convert date string to a readable format
                    $date = $dateStr ? str_replace('_', ' ', $dateStr) : date('Y-m-d H:i:s', $file->getMTime());
                    
                    $backups[] = [
                        'filename' => $filename,
                        'path' => $file->getPathname(),
                        'version' => $version,
                        'date' => $date,
                        'size' => $file->getSize(),
                    ];
                }
            }
            
            // Sort backups by date, newest first
            usort($backups, function($a, $b) {
                return strtotime(str_replace('_', ' ', $b['date'])) - strtotime(str_replace('_', ' ', $a['date']));
            });
            
            // Get all versions for context
            $versions = SystemVersion::orderBy('released_at', 'desc')->get();
            $currentVersion = config('self-update.version_installed', '1.0.0');
            
            return view('system.versions.backups', [
                'backups' => $backups,
                'versions' => $versions,
                'currentVersion' => $currentVersion,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing backups: ' . $e->getMessage());
            return redirect()->route('version.manage')
                ->with('error', 'Failed to list backups: ' . $e->getMessage());
        }
    }
    
    /**
     * Restore the application from a backup file
     */
    public function restoreFromBackup(Request $request)
    {
        try {
            $backupFilename = $request->input('backup_filename');
            $backupPath = storage_path('app/version-backups/' . $backupFilename);
            
            if (!File::exists($backupPath)) {
                return redirect()->route('version.backups')
                    ->with('error', 'Backup file not found.');
            }
            
            // Extract the version from filename to update database after restore
            preg_match('/backup_(.+?)_\d{4}-\d{2}-\d{2}/', $backupFilename, $matches);
            $version = $matches[1] ?? config('self-update.version_installed', '1.0.0');
            
            // Create a temporary extraction directory
            $extractPath = storage_path('app/self-updater/extract_backup_' . time());
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }
            
            // Put the application into maintenance mode
            Artisan::call('down', ['--render' => 'Restoring from backup']);
            
            try {
                // Extract the zip file
                $zip = new ZipArchive;
                $openResult = $zip->open($backupPath);
                
                if ($openResult !== true) {
                    Log::error("Failed to open backup file: Error code {$openResult}");
                    Artisan::call('up');
                    return redirect()->route('version.backups')
                        ->with('error', 'Failed to open backup file.');
                }
                
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Copy files to base directory, respecting exclusions
                $this->versionDeployment->copyFiles($extractPath, base_path());
                
                // Update the installed version in the .env file
                $this->versionDeployment->updateInstalledVersion($version);
                
                // Clear caches
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                
                // Clean up extraction directory
                File::deleteDirectory($extractPath);
                
                // Mark the version as current in the database
                $this->versionDeployment->markVersionAsCurrent($version);
                
                // Bring the application back online
                Artisan::call('up');
                
                return redirect()->route('version.manage')
                    ->with('success', 'System successfully restored from backup to version ' . $version);
                
            } catch (\Exception $e) {
                // Ensure the app is brought back online
                Artisan::call('up');
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error restoring from backup: ' . $e->getMessage());
            return redirect()->route('version.backups')
                ->with('error', 'Failed to restore from backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a backup file
     */
    public function deleteBackup(Request $request)
    {
        try {
            $backupFilename = $request->input('backup_filename');
            $backupPath = storage_path('app/version-backups/' . $backupFilename);
            
            if (!File::exists($backupPath)) {
                return redirect()->route('version.backups')
                    ->with('error', 'Backup file not found.');
            }
            
            File::delete($backupPath);
            
            return redirect()->route('version.backups')
                ->with('success', 'Backup file deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            return redirect()->route('version.backups')
                ->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Dismiss a system update
     */
    public function dismissUpdate($id)
    {
        try {
            // Find the version
            $version = SystemVersion::findOrFail($id);
            
            // Get the current clinic
            $clinic = null;
            if (session()->has('current_clinic_id')) {
                $clinic = Clinic::find(session('current_clinic_id'));
            }
            
            if (!$clinic) {
                return redirect()->route('version.manage')
                    ->with('error', 'No active clinic found.');
            }
            
            // Find or create a ClinicUpdate record
            $clinicUpdate = ClinicUpdate::firstOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'system_update_id' => $version->id
                ],
                [
                    'version' => $version->version,
                    'status' => 'pending'
                ]
            );
            
            // Mark as dismissed
            $clinicUpdate->update([
                'status' => 'dismissed',
                'dismissed_at' => now(),
                'notes' => 'Dismissed by user'
            ]);
            
            return redirect()->back()
                ->with('success', "Update {$version->version} has been dismissed.");
                
        } catch (\Exception $e) {
            Log::error('Error dismissing update: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to dismiss update: ' . $e->getMessage());
        }
    }
} 