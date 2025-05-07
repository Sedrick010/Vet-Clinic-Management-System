<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomUpdaterService;
use Illuminate\Support\Facades\Log;
use App\Models\SystemVersion;

class SyncApplicationVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-version {--force : Force update to latest version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize the application version with the latest GitHub release';

    /**
     * The custom updater service.
     *
     * @var CustomUpdaterService
     */
    protected $updaterService;

    /**
     * Create a new command instance.
     *
     * @param CustomUpdaterService $updaterService
     * @return void
     */
    public function __construct(CustomUpdaterService $updaterService)
    {
        parent::__construct();
        $this->updaterService = $updaterService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking current version...');
        
        $currentVersion = config('self-update.version_installed');
        $this->info("Current version: $currentVersion");
        
        $this->info('Fetching latest version from GitHub...');
        $latestVersion = $this->updaterService->getLatestVersion();
        
        if (!$latestVersion) {
            $this->error('Failed to fetch latest version from GitHub');
            return 1;
        }
        
        $this->info("Latest version from GitHub: $latestVersion");
        
        // Clean the versions for comparison
        $cleanCurrentVersion = ltrim($currentVersion, 'v');
        $cleanLatestVersion = ltrim($latestVersion, 'v');
        
        if (version_compare($cleanCurrentVersion, $cleanLatestVersion, '<') || $this->option('force')) {
            $this->info("Updating version from $currentVersion to $latestVersion");
            
            try {
                // Update .env file
                $this->updateVersionInEnvFile($cleanLatestVersion);
                
                // Update SystemVersion record
                $this->updateSystemVersionRecord($cleanLatestVersion);
                
                // Clear config cache
                $this->call('config:clear');
                
                $this->info('Version has been updated successfully');
                return 0;
            } catch (\Exception $e) {
                $this->error('Failed to update version: ' . $e->getMessage());
                Log::error('Failed to update version: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("Application is already at the latest version ($currentVersion)");
            return 0;
        }
    }
    
    /**
     * Update the version in the .env file
     *
     * @param string $newVersion
     * @return bool
     */
    private function updateVersionInEnvFile(string $newVersion): bool
    {
        $envFile = base_path('.env');
        
        if (file_exists($envFile)) {
            $envContents = file_get_contents($envFile);
            
            $updatedContents = preg_replace(
                '/SELF_UPDATER_VERSION_INSTALLED=([^\n]+)/',
                'SELF_UPDATER_VERSION_INSTALLED=' . $newVersion,
                $envContents
            );
            
            file_put_contents($envFile, $updatedContents);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update the system version record
     *
     * @param string $newVersion
     * @return void
     */
    private function updateSystemVersionRecord(string $newVersion): void
    {
        // Reset all version records to not current
        SystemVersion::where('is_current', true)->update(['is_current' => false]);
        
        // Get or create version record
        $versionRecord = SystemVersion::firstOrCreate(
            ['version' => $newVersion],
            [
                'name' => "Version $newVersion",
                'description' => "Version $newVersion",
                'released_at' => now(),
            ]
        );
        
        // Set as current version
        $versionRecord->is_current = true;
        $versionRecord->save();
    }
} 