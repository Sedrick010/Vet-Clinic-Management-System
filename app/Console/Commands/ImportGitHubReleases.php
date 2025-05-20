<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomUpdaterService;
use App\Models\SystemVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportGitHubReleases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'versions:import {--limit=10 : Number of releases to import} {--force : Import even if they exist already}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import GitHub releases into the SystemVersion table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CustomUpdaterService $updater)
    {
        $this->info('Checking GitHub releases...');
        
        // Get limit from options
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');
        
        // Current version from config
        $currentVersion = config('self-update.version_installed', '1.0.0');
        $this->info("Current version: $currentVersion");
        
        // Get all releases from GitHub
        $this->info("Getting up to $limit releases from GitHub...");
        $allReleases = $updater->getAllReleases($limit);
        
        if (empty($allReleases)) {
            $this->error('No releases found on GitHub or an error occurred.');
            return 1;
        }
        
        $this->info("Found " . count($allReleases) . " releases on GitHub.");
        
        // Display all GitHub releases
        $this->table(
            ['Index', 'Version', 'Name', 'Published At'],
            collect($allReleases)->map(function ($release, $index) {
                return [
                    'index' => $index + 1,
                    'version' => $release['version'] ?? 'Unknown',
                    'name' => $release['name'] ?? 'Unnamed',
                    'published_at' => $release['published_at'] ?? 'Unknown date',
                ];
            })->toArray()
        );
        
        // Find which versions need to be imported
        $missingVersions = [];
        $existingVersions = SystemVersion::pluck('version')->toArray();
        
        foreach ($allReleases as $release) {
            $version = $release['version'] ?? null;
            if ($version && (!in_array($version, $existingVersions) || $force)) {
                $missingVersions[] = $release;
            }
        }
        
        if (empty($missingVersions) && !$force) {
            $this->info('All GitHub releases are already in the database.');
            return 0;
        }
        
        // Confirm import
        if (!$this->confirm('Import ' . count($missingVersions) . ' releases into the database?')) {
            $this->info('Import cancelled.');
            return 0;
        }
        
        // Import missing versions
        $importCount = 0;
        $this->output->progressStart(count($missingVersions));
        
        foreach ($missingVersions as $releaseDetails) {
            $version = $releaseDetails['version'] ?? null;
            
            if ($version) {
                try {
                    // If forcing import, delete existing version first
                    if ($force) {
                        SystemVersion::where('version', $version)->delete();
                    }
                    
                    // Create the version record
                    SystemVersion::create([
                        'version' => $version,
                        'name' => $releaseDetails['name'] ?? "Version {$version}",
                        'description' => $releaseDetails['description'] ?? "Update to version {$version}",
                        'is_current' => $version === $currentVersion,
                        'released_at' => isset($releaseDetails['published_at']) 
                            ? new Carbon($releaseDetails['published_at']) 
                            : Carbon::now(),
                    ]);
                    
                    $importCount++;
                } catch (\Exception $e) {
                    $this->error("Error importing {$version}: " . $e->getMessage());
                    Log::error("Error importing version {$version}: " . $e->getMessage());
                }
            }
            
            $this->output->progressAdvance();
        }
        
        $this->output->progressFinish();
        
        $this->info("Imported {$importCount} version(s) successfully.");
        
        // Make sure current version is properly marked
        $currentVersionRecord = SystemVersion::where('version', $currentVersion)->first();
        
        if ($currentVersionRecord) {
            // Check if the record is already marked as current
            if (!$currentVersionRecord->is_current) {
                // Mark all versions as not current
                SystemVersion::where('is_current', true)->update(['is_current' => false]);
                
                // Set this version as current
                $currentVersionRecord->is_current = true;
                $currentVersionRecord->save();
                
                $this->info("Marked version {$currentVersion} as current.");
            } else {
                $this->info("Version {$currentVersion} is already marked as current.");
            }
        } else {
            $this->warn("Current version {$currentVersion} not found in database.");
        }
        
        return 0;
    }
} 