<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomUpdaterService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncGitHubReleases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:sync-releases {--force : Force update even if releases already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync GitHub releases with system update tables';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(CustomUpdaterService $updater)
    {
        $this->info('Syncing GitHub releases with system update tables...');

        // Check if we should force update
        $force = $this->option('force');
        
        // Get all releases from GitHub
        $releases = $updater->getAllReleases(10);
        
        if (empty($releases)) {
            $this->error('No releases found on GitHub.');
            return 1;
        }
        
        $this->info('Found ' . count($releases) . ' releases on GitHub.');
        
        // Get current installed version
        $currentVersion = config('self-update.version_installed');
        $this->info('Current installed version: ' . $currentVersion);
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            if ($force) {
                // Clear existing data
                $this->info('Force option enabled. Truncating existing update data...');
                DB::table('clinic_updates')->truncate();
                DB::table('system_updates')->truncate();
                DB::table('system_versions')->truncate();
            }
            
            $processedCount = 0;
            
            foreach ($releases as $release) {
                $version = $release['version'];
                
                // Check if version already exists
                if (!$force && DB::table('system_versions')->where('version', $version)->exists()) {
                    $this->info("Version {$version} already exists. Skipping.");
                    continue;
                }
                
                // Determine update type (critical, security, etc.)
                $isCritical = false;
                $isSecurity = false;
                $isMandatory = false;
                $description = $release['description'] ?? '';
                
                // Parse description to determine update type
                if (strpos(strtolower($description), 'critical') !== false || 
                    strpos(strtolower($release['name'] ?? ''), 'critical') !== false) {
                    $isCritical = true;
                    $isMandatory = true;
                }
                
                if (strpos(strtolower($description), 'security') !== false || 
                    strpos(strtolower($release['name'] ?? ''), 'security') !== false) {
                    $isSecurity = true;
                    $isMandatory = true;
                }
                
                // Create system version record
                $versionId = DB::table('system_versions')->insertGetId([
                    'version' => $version,
                    'name' => $release['name'] ?? "Version {$version}",
                    'description' => $description,
                    'is_current' => ($version === $currentVersion),
                    'released_at' => new Carbon($release['published_at'] ?? 'now'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                // Extract features and bug fixes from description
                $features = [];
                $bugFixes = [];
                
                $lines = explode("\n", $description);
                $currentSection = null;
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    if (empty($line)) continue;
                    
                    // Check for section headers
                    if (preg_match('/(feature|enhancement|new|added|improvement)/i', $line)) {
                        $currentSection = 'features';
                        continue;
                    } elseif (preg_match('/(bug|fix|issue|patch)/i', $line)) {
                        $currentSection = 'bugfixes';
                        continue;
                    }
                    
                    // Add line to appropriate section
                    if ($currentSection === 'features' && strpos($line, '- ') === 0) {
                        $features[] = substr($line, 2);
                    } elseif ($currentSection === 'bugfixes' && strpos($line, '- ') === 0) {
                        $bugFixes[] = substr($line, 2);
                    }
                }
                
                // Create system update record
                $updateId = DB::table('system_updates')->insertGetId([
                    'version' => $version,
                    'name' => $release['name'] ?? "Version {$version}",
                    'description' => $description,
                    'changes' => $description,
                    'features' => !empty($features) ? implode("\n", $features) : null,
                    'bug_fixes' => !empty($bugFixes) ? implode("\n", $bugFixes) : null,
                    'is_critical' => $isCritical,
                    'is_security' => $isSecurity,
                    'is_mandatory' => $isMandatory,
                    'available_from' => Carbon::now(),
                    'expires_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                // Create clinic update records for all clinics
                $clinics = DB::table('clinics')->get();
                
                foreach ($clinics as $clinic) {
                    // Current version and older versions are automatically applied
                    $isApplied = version_compare(ltrim($version, 'v'), ltrim($currentVersion, 'v'), '<=');
                    
                    DB::table('clinic_updates')->insert([
                        'clinic_id' => $clinic->id,
                        'system_update_id' => $updateId,
                        'is_applied' => $isApplied,
                        'is_dismissed' => false,
                        'applied_at' => $isApplied ? Carbon::now() : null,
                        'dismissed_at' => null,
                        'notes' => $isApplied ? 'Automatically applied during sync' : null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
                
                $processedCount++;
                $this->info("Processed release {$version}");
            }
            
            // Commit transaction
            DB::commit();
            
            $this->info("Successfully processed {$processedCount} releases.");
            return 0;
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            
            $this->error('Error syncing releases: ' . $e->getMessage());
            return 1;
        }
    }
} 