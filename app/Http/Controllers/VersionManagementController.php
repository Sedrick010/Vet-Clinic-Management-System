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
     *
     * @param int $versionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($versionId)
    {
        try {
            $version = SystemVersion::findOrFail($versionId);
            $currentVersion = config('self-update.version_installed', '1.0.0');
            
            // Check if trying to update to current version
            if ($version->version === $currentVersion) {
                return redirect()->route('version.manage')
                    ->with('info', 'You are already on version ' . $version->version);
            }
            
            // Determine if this is an upgrade or downgrade
            $isUpgrade = version_compare($version->version, $currentVersion, '>');
            $actionType = $isUpgrade ? 'upgrade' : 'downgrade';
            
            // Store previous version for success page
            session(['previous_version' => $currentVersion]);
            session(['update_type' => $actionType]);
            
            // Download the specific version ZIP file
            Log::info("Starting {$actionType} process from version {$currentVersion} to {$version->version}");
            $zipPath = $this->versionDeployment->downloadVersion($version->version);
            
            if (!$zipPath) {
                Log::error("Failed to download version {$version->version} for {$actionType}");
                return redirect()->route('version.manage')
                    ->with('error', "Failed to download version {$version->version}. Please check internet connection and GitHub access.");
            }
            
            // Deploy the version from the zip file
            $deployed = $this->versionDeployment->deployVersion($version->version);
            
            if (!$deployed) {
                Log::error("Failed to deploy version {$version->version} for {$actionType}");
                return redirect()->route('version.manage')
                    ->with('error', "Failed to {$actionType} to version {$version->version}. Please check logs for details.");
            }
            
            // Set success indicators for the success page
            session(['update_success' => true]);
            session(['tenant_migration_success' => true]); // Assume success unless we know otherwise
            
            Log::info("Successfully completed {$actionType} to version {$version->version}");
            return redirect()->route('version.update.success');
        } catch (\Exception $e) {
            Log::error("Failed to process version {$actionType}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('version.manage')
                ->with('error', 'Failed to process version change: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the update success page
     * 
     * @return \Illuminate\View\View
     */
    public function updateSuccess()
    {
        if (!session('update_success')) {
            return redirect()->route('version.manage');
        }
        
        $version = config('self-update.version_installed', '1.0.0');
        $previousVersion = session('previous_version', 'Unknown');
        $updateType = session('update_type', 'update');
        $tenantMigrationSuccess = session('tenant_migration_success', true);
        
        // Clear the session data
        session()->forget(['update_success', 'previous_version', 'update_type', 'tenant_migration_success']);
        
        return view('system.versions.update-success', [
            'version' => $version,
            'previousVersion' => $previousVersion,
            'updateType' => $updateType,
            'tenantMigrationSuccess' => $tenantMigrationSuccess
        ]);
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
                    Log::info("Imported {$importedCount} new version(s) from GitHub");
                    return redirect()->route('version.manage')->with('success', "Imported {$importedCount} new version(s) from GitHub!");
                } else if ($hasUpdates) {
                    // We have updates but they're already imported
                    return redirect()->route('version.manage')->with('info', 'Found updates but they are already in the system.');
                } else {
                    // No new versions found
                    return redirect()->route('version.manage')->with('info', 'You are already on the latest version. No new updates available.');
                }
            } else {
                Log::warning("No releases found on GitHub");
                return redirect()->route('version.manage')->with('info', 'No releases found on GitHub.');
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
     * 
     * @param Request $request
     * @param int $versionId
     * @return \Illuminate\Http\RedirectResponse
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
            
            // This is now just a redirect to the update method, since both update and rollback use the same process
            return $this->update($versionId);
            
        } catch (\Exception $e) {
            Log::error('Failed to rollback version: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreFromBackup(Request $request)
    {
        // Prevent timeout for long-running restoration
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
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
            $currentVersion = config('self-update.version_installed', '1.0.0');
            
            // Create a unique temporary extraction directory
            $extractionId = time() . '_' . rand(1000, 9999);
            $extractPath = storage_path('app/self-updater/extract_backup_' . $extractionId);
            
            Log::info("Starting restoration from backup to version {$version} (current: {$currentVersion})");
            
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0775, true);
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
                
                Log::info("Extracting backup file to {$extractPath}");
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Copy files to base directory, respecting exclusions
                Log::info("Copying files from backup to base directory");
                $this->versionDeployment->copyFilesEnhanced($extractPath, base_path());
                
                // Update the installed version in the .env file
                Log::info("Updating installed version in .env file to {$version}");
                $this->versionDeployment->updateInstalledVersion($version);
                
                // Clear caches
                Log::info("Clearing application caches");
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                
                // Run main database migrations
                Log::info("Running migrations for main database");
                Artisan::call('migrate', ['--force' => true]);
                
                // Run migrations for all tenant databases
                Log::info("Running migrations for all tenant databases");
                try {
                    Artisan::call('migrate:all-tenants', ['--force' => true]);
                    Log::info("Tenant migrations completed successfully");
                } catch (\Exception $e) {
                    Log::error("Error running tenant migrations: " . $e->getMessage());
                    // Continue with the restoration even if tenant migrations fail
                }
                
                // Clean up extraction directory
                Log::info("Cleaning up temporary extraction directory");
                $this->cleanupExtractionDirectories($extractPath);
                
                // Mark the version as current in the database
                Log::info("Marking version {$version} as current in the database");
                $this->versionDeployment->markVersionAsCurrent($version);
                
                // Bring the application back online
                Log::info("Bringing application back online");
                Artisan::call('up');
                
                Log::info("Successfully restored system from backup to version {$version}");
                return redirect()->route('version.manage')
                    ->with('success', 'System successfully restored from backup to version ' . $version);
                
            } catch (\Exception $e) {
                // Ensure the app is brought back online
                Artisan::call('up');
                Log::error("Error during backup restoration: " . $e->getMessage(), [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error restoring from backup: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('version.backups')
                ->with('error', 'Failed to restore from backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Clean up extraction directories to prevent storage build-up
     * 
     * @param string $currentExtractPath The current extraction path to exclude from cleanup
     * @return void
     */
    protected function cleanupExtractionDirectories(?string $currentExtractPath = null): void
    {
        try {
            $basePath = storage_path('app/self-updater');
            
            // Clean up the current extract path if provided
            if ($currentExtractPath && File::exists($currentExtractPath)) {
                Log::info("Cleaning up current extraction directory: {$currentExtractPath}");
                File::deleteDirectory($currentExtractPath);
            }
            
            // Find all extraction directories
            $extractDirs = File::glob($basePath . '/extract_*');
            $backupExtractDirs = File::glob($basePath . '/extract_backup_*');
            $allExtractDirs = array_merge($extractDirs, $backupExtractDirs);
            
            $currentTime = time();
            $cleanedCount = 0;
            
            foreach ($allExtractDirs as $dir) {
                // Skip the current extraction directory if it's the same
                if ($currentExtractPath && $dir === $currentExtractPath) {
                    continue;
                }
                
                // If directory is more than 6 hours old, delete it
                if (is_dir($dir)) {
                    $modifiedTime = filemtime($dir);
                    if (($currentTime - $modifiedTime) > 21600) { // 6 hours in seconds
                        Log::info("Cleaning up old extraction directory: {$dir}");
                        File::deleteDirectory($dir);
                        $cleanedCount++;
                    }
                }
            }
            
            if ($cleanedCount > 0) {
                Log::info("Cleaned up {$cleanedCount} old extraction directories");
            }
        } catch (\Exception $e) {
            // Log but don't throw, as this is not critical
            Log::warning("Error cleaning up extraction directories: " . $e->getMessage());
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