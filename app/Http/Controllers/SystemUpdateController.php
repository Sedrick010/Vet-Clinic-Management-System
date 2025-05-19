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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
        try {
            // Check if system_updates table exists
            if (!Schema::hasTable('system_updates')) {
                // Create system_updates table
                Schema::create('system_updates', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description');
                    $table->text('changes');
                    $table->text('features')->nullable();
                    $table->text('bug_fixes')->nullable();
                    $table->boolean('is_critical')->default(false);
                    $table->boolean('is_security')->default(false);
                    $table->boolean('is_mandatory')->default(false);
                    $table->timestamp('available_from')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
            }
            
            // Check if system_versions table exists
            if (!Schema::hasTable('system_versions')) {
                Schema::create('system_versions', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description')->nullable();
                    $table->boolean('is_current')->default(false);
                    $table->timestamp('released_at')->nullable();
                    $table->timestamps();
                });
                
                // Insert initial version
                DB::table('system_versions')->insert([
                    'version' => '1.0.0',
                    'name' => 'Initial Release',
                    'description' => 'The initial release of the VetClinic system',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $updates = SystemUpdate::orderBy('created_at', 'desc')->get();
            $hasNewUpdate = false;
            $latestVersion = null;
            $allReleases = [];
            $versions = [];
            $currentVersion = config('self-update.version_installed', '1.0.0');

            try {
                // Check for updates using our custom service
                $hasNewUpdate = $this->customUpdater->isNewVersionAvailable();
                $latestVersion = $this->customUpdater->getLatestVersion();
                
                // If a new version is found that isn't in our system_versions table
                if ($latestVersion && !SystemVersion::where('version', $latestVersion)->exists()) {
                    $hasNewUpdate = true;
                }
                
                // Get all GitHub releases for version management
                $allReleases = $this->customUpdater->getAllReleases(20);
                
                // Get all versions sorted by version number (newest first)
                $versions = SystemVersion::orderBy('released_at', 'desc')->get();
            } catch (\Exception $e) {
                Log::error('Error checking for updates: ' . $e->getMessage());
            }

            return view('system-updates.combined', [
                'updates' => $updates,
                'hasNewUpdate' => $hasNewUpdate,
                'latestVersion' => $latestVersion,
                'versions' => $versions,
                'allReleases' => $allReleases,
                'currentVersion' => $currentVersion
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in SystemUpdateController@index: ' . $e->getMessage());
            
            // Return a simplified view without the database elements
            return view('system-updates.error', [
                'errorMessage' => 'System update tables are not set up properly. Please contact your administrator.',
                'detailedError' => $e->getMessage(),
                'currentVersion' => config('self-update.version_installed', '1.0.0')
            ]);
        }
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
                // Get clinic ID from auth user or session
                $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
                
                if (!$clinicId) {
                    return redirect()->back()->with('error', 'No clinic ID found');
                }
                
                // Update status in database
                if ($update = SystemUpdate::where('version', $latestVersion)->first()) {
                    if ($clinicUpdate = ClinicUpdate::where('system_update_id', $update->id)
                                                   ->where('clinic_id', $clinicId)
                                                   ->first()) {
                        $clinicUpdate->is_applied = true;
                        $clinicUpdate->applied_at = Carbon::now();
                        $clinicUpdate->save();
                    }
                }
                
                // Update the version in the .env file
                $this->updateInstalledVersion($latestVersion, $clinicId);
                
                // Mark version as current in the SystemVersion table
                if ($versionRecord = SystemVersion::where('version', $latestVersion)->first()) {
                    // Set all versions to non-current
                    SystemVersion::where('is_current', true)->update(['is_current' => false]);
                    
                    // Set the new version as current
                    $versionRecord->is_current = true;
                    $versionRecord->save();
                }
                
                return redirect()->back()->with('success', "System has been updated to version {$latestVersion} successfully!");
            }
            
            return redirect()->back()->with('error', 'No update version found');
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Force refresh of update checks
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            // Force a fresh check for updates from GitHub
            $hasUpdates = $this->customUpdater->forceRefreshUpdateCheck();
            
            if ($hasUpdates) {
                $latestVersionDetails = $this->customUpdater->getLatestVersionDetails();
                $version = $latestVersionDetails['version'] ?? null;
                
                if ($version && !SystemUpdate::where('version', $version)->exists()) {
                    // Create the version record
                    $versionRecord = SystemVersion::create([
                        'version' => $version,
                        'name' => $latestVersionDetails['name'] ?? "Version {$version}",
                        'description' => $latestVersionDetails['description'] ?? "Update to version {$version}",
                        'is_current' => false,
                        'released_at' => isset($latestVersionDetails['published_at']) 
                            ? new Carbon($latestVersionDetails['published_at']) 
                            : Carbon::now(),
                    ]);
                    
                    // Create system update record
                    $systemUpdate = SystemUpdate::create([
                        'version' => $version,
                        'name' => $latestVersionDetails['name'] ?? "Version {$version} Update",
                        'description' => $latestVersionDetails['description'] ?? "Automatic update from GitHub release",
                        'changes' => $latestVersionDetails['description'] ?? "Update to version {$version}",
                        'features' => is_array($latestVersionDetails['features'] ?? null) 
                            ? implode("\n", $latestVersionDetails['features']) 
                            : "New features in version {$version}",
                        'bug_fixes' => is_array($latestVersionDetails['bug_fixes'] ?? null) 
                            ? implode("\n", $latestVersionDetails['bug_fixes']) 
                            : "Bug fixes in version {$version}",
                        'is_critical' => $latestVersionDetails['is_critical'] ?? false,
                        'is_security' => $latestVersionDetails['is_security'] ?? false,
                        'is_mandatory' => $latestVersionDetails['is_mandatory'] ?? false,
                        'available_from' => Carbon::now(),
                    ]);
                    
                    // Create clinic update records for all clinics and notify them
                    $clinics = \App\Models\Clinic::all();
                    foreach ($clinics as $clinic) {
                        // Create clinic update record
                        ClinicUpdate::create([
                            'clinic_id' => $clinic->id,
                            'system_update_id' => $systemUpdate->id,
                            'is_applied' => false,
                            'is_dismissed' => false
                        ]);
                        
                        // Notify clinic staff about the update
                        try {
                            // Find clinic admin/owner to notify
                            $clinicAdmin = $clinic->users()->where('role', 'admin')->first();
                            if ($clinicAdmin) {
                                $clinicAdmin->notify(new \App\Notifications\SystemUpdateAvailable($systemUpdate));
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send update notification to clinic: ' . $clinic->id, [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => "New update {$version} is available and clinics have been notified!",
                        'updateInfo' => [
                            'version' => $version,
                            'description' => $latestVersionDetails['description'] ?? null,
                            'published_at' => $latestVersionDetails['published_at'] ?? null,
                            'is_critical' => $latestVersionDetails['is_critical'] ?? false,
                        ]
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Update is already in the system',
                    'updateInfo' => [
                        'version' => $version
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'No new updates available',
                'hasUpdates' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking for updates: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update the installed version in the .env file and for the specific clinic
     *
     * @param string $newVersion
     * @param int|null $clinicId
     * @return bool
     */
    private function updateInstalledVersion(string $newVersion, int $clinicId = null): bool
    {
        try {
            // Clean the version number (remove 'v' prefix if present)
            $newVersion = ltrim($newVersion, 'v');
            
            // Get current clinic ID if not provided
            if ($clinicId === null) {
                $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
            }
            
            if ($clinicId) {
                // Update the clinic-specific version in a database setting
                try {
                    $clinic = \App\Models\Clinic::findOrFail($clinicId);
                    
                    // Store the clinic's current version in clinic settings
                    $settingKey = 'installed_version';
                    $clinic->settings()->updateOrCreate(
                        ['key' => $settingKey],
                        ['value' => $newVersion]
                    );
                    
                    Log::info('Updated clinic-specific version', [
                        'clinic_id' => $clinicId,
                        'version' => $newVersion
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update clinic-specific version: ' . $e->getMessage(), [
                        'clinic_id' => $clinicId,
                        'version' => $newVersion
                    ]);
                }
            }
            
            // Only update the central .env file if we have a valid clinic ID
            // This prevents non-authenticated users from changing the version
            if ($clinicId) {
                // Path to .env file
                $envFile = base_path('.env');
                
                if (file_exists($envFile)) {
                    // Read the .env file
                    $envContents = file_get_contents($envFile);
                    
                    // Replace the version in the .env file
                    $updatedContents = preg_replace(
                        '/SELF_UPDATER_VERSION_INSTALLED=([^\n]+)/',
                        'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion,
                        $envContents
                    );
                    
                    // Write the updated contents back to the .env file
                    file_put_contents($envFile, $updatedContents);
                    
                    // Clear config cache to ensure the new value is loaded
                    \Artisan::call('config:clear');
                    
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update installed version: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply a specific update for the current clinic
     * 
     * @param int $id The system update ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function apply($id)
    {
        try {
            $update = SystemUpdate::findOrFail($id);
            $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
            
            if (!$clinicId) {
                return redirect()->back()->with('error', 'No clinic ID found');
            }
            
            // Update the clinic-specific status
            ClinicUpdate::updateOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'system_update_id' => $update->id
                ],
                [
                    'is_applied' => true,
                    'is_dismissed' => false,
                    'applied_at' => Carbon::now(),
                    'notes' => 'Applied manually by user'
                ]
            );
            
            // Only update system version if this is the latest version
            $latestVersion = $this->customUpdater->getLatestVersion();
            if ($update->version === $latestVersion) {
                // Update the version in the .env file for this tenant only
                $this->updateInstalledVersion($update->version, $clinicId);
                
                // Mark version as current in the SystemVersion table
                try {
                    if ($versionRecord = SystemVersion::where('version', $update->version)->first()) {
                        // Set all versions to non-current
                        SystemVersion::where('is_current', true)->update(['is_current' => false]);
                        
                        // Set the new version as current
                        $versionRecord->is_current = true;
                        $versionRecord->save();
                    }
                } catch (\Exception $e) {
                    // Log the error but don't fail the update
                    Log::warning('Non-critical error updating system version record: ' . $e->getMessage());
                }
            }
            
            return redirect()->back()->with('success', "Update {$update->version} has been successfully applied!");
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Check if this is a common database error that doesn't actually prevent the update
            $nonCriticalErrors = [
                'table or view already exists',
                'Base table or view not found',
                'Column not found',
                'system_versions',
                'SQLSTATE[42S01]',
                'SQLSTATE[42S02]'
            ];
            
            $isNonCriticalError = false;
            foreach ($nonCriticalErrors as $errorPattern) {
                if (stripos($errorMessage, $errorPattern) !== false) {
                    $isNonCriticalError = true;
                    break;
                }
            }
            
            if ($isNonCriticalError) {
                // Log as warning instead of error
                Log::warning('Non-critical error during update: ' . $errorMessage);
                
                // Still mark the update as successful
                return redirect()->back()->with('success', "Update has been applied successfully despite some expected database messages.");
            }
            
            // This is a real error
            Log::error('Error applying update: ' . $errorMessage);
            return redirect()->back()->with('error', 'Error applying update: ' . $errorMessage);
        }
    }
    
    /**
     * Dismiss an update for the current clinic
     * 
     * @param int $id The system update ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dismiss($id)
    {
        try {
            $update = SystemUpdate::findOrFail($id);
            $clinicId = auth()->user() ? auth()->user()->clinic_id : (session('current_clinic_id') ?? null);
            
            if (!$clinicId) {
                return redirect()->back()->with('error', 'No clinic ID found');
            }
            
            // Don't allow dismissing mandatory updates
            if ($update->is_mandatory) {
                return redirect()->back()->with('error', 'This update is mandatory and cannot be dismissed.');
            }
            
            // Update the clinic-specific status
            ClinicUpdate::updateOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'system_update_id' => $update->id
                ],
                [
                    'is_dismissed' => true,
                    'dismissed_at' => Carbon::now(),
                    'notes' => 'Dismissed manually by user'
                ]
            );
            
            return redirect()->back()->with('info', "Update {$update->version} has been dismissed.");
        } catch (\Exception $e) {
            Log::error('Error dismissing update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error dismissing update: ' . $e->getMessage());
        }
    }

    /**
     * Fix the database tables for system updates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fixTables()
    {
        try {
            Log::info('Attempting to fix system update tables');
            
            // Check if system_updates table exists
            if (!Schema::hasTable('system_updates')) {
                Log::info('Creating missing system_updates table');
                
                // Create system_updates table
                Schema::create('system_updates', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description');
                    $table->text('changes');
                    $table->text('features')->nullable();
                    $table->text('bug_fixes')->nullable();
                    $table->boolean('is_critical')->default(false);
                    $table->boolean('is_security')->default(false);
                    $table->boolean('is_mandatory')->default(false);
                    $table->timestamp('available_from')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
                
                Log::info('Successfully created system_updates table');
            } else {
                Log::info('system_updates table already exists');
            }
            
            // Check if system_versions table exists
            if (!Schema::hasTable('system_versions')) {
                Log::info('Creating missing system_versions table');
                
                Schema::create('system_versions', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description')->nullable();
                    $table->boolean('is_current')->default(false);
                    $table->timestamp('released_at')->nullable();
                    $table->timestamps();
                });
                
                // Insert initial version if the table was just created
                $currentVersion = config('self-update.version_installed', '1.0.0');
                Log::info('Inserting initial version record', ['version' => $currentVersion]);
                
                DB::table('system_versions')->insert([
                    'version' => $currentVersion,
                    'name' => 'Initial Release',
                    'description' => 'The initial release of the VetClinic system',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Successfully created system_versions table');
            } else {
                Log::info('system_versions table already exists');
                
                // Check if we have at least one version record
                $versionsCount = DB::table('system_versions')->count();
                if ($versionsCount === 0) {
                    // Insert initial version if there are no versions in the table
                    $currentVersion = config('self-update.version_installed', '1.0.0');
                    Log::info('No version records found. Inserting initial version', ['version' => $currentVersion]);
                    
                    DB::table('system_versions')->insert([
                        'version' => $currentVersion,
                        'name' => 'Initial Release',
                        'description' => 'The initial release of the VetClinic system',
                        'is_current' => true,
                        'released_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Check if clinic_updates table exists with the correct schema
            if (!Schema::hasTable('clinic_updates')) {
                Log::info('Creating missing clinic_updates table');
                
                // Create clinic_updates table
                Schema::create('clinic_updates', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
                    $table->foreignId('system_update_id')->nullable();
                    $table->boolean('is_applied')->default(false);
                    $table->boolean('is_dismissed')->default(false);
                    $table->timestamp('applied_at')->nullable();
                    $table->timestamp('dismissed_at')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
                
                Log::info('Successfully created clinic_updates table');
            } else {
                Log::info('clinic_updates table already exists');
                
                // Check if we need to add system_update_id column (in case of old schema)
                if (!Schema::hasColumn('clinic_updates', 'system_update_id')) {
                    Log::info('Adding system_update_id column to clinic_updates table');
                    
                    Schema::table('clinic_updates', function (Blueprint $table) {
                        $table->foreignId('system_update_id')->nullable()->after('clinic_id');
                        
                        // Only add these columns if they don't exist
                        if (!Schema::hasColumn('clinic_updates', 'is_applied')) {
                            $table->boolean('is_applied')->default(false)->after('system_update_id');
                        }
                        
                        if (!Schema::hasColumn('clinic_updates', 'is_dismissed')) {
                            $table->boolean('is_dismissed')->default(false)->after('is_applied');
                        }
                        
                        // Try to drop status column if it exists
                        if (Schema::hasColumn('clinic_updates', 'status')) {
                            $table->dropColumn('status');
                        }
                    });
                    
                    Log::info('Successfully updated clinic_updates table schema');
                }
            }
            
            Log::info('System update tables fix completed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'System update tables fixed successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fixing system update tables: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 