<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomUpdaterService;
use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use App\Models\ClinicUpdate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckSystemUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:check-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for system updates and create records in the database';

    /**
     * The custom updater service.
     */
    protected $updater;

    /**
     * Create a new command instance.
     */
    public function __construct(CustomUpdaterService $updater)
    {
        parent::__construct();
        $this->updater = $updater;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for system updates...');
        
        try {
            $isAvailable = $this->updater->isNewVersionAvailable();
            
            if ($isAvailable) {
                $latestVersionDetails = $this->updater->getLatestVersionDetails();
                $version = $latestVersionDetails['version'] ?? null;
                
                if ($version && !SystemVersion::where('versi', $version)->exists()) {
                    // Create the version record
                    $versionRecord = SystemVersion::create([
                        'versi' => $version,
                        'release_date' => Carbon::now(),
                    ]);
                    
                    $this->info("Created new version record: $version");
                    
                    // Create system update record
                    $systemUpdate = SystemUpdate::create([
                        'version_id' => $versionRecord->id,
                        'summary' => "New version $version available",
                        'changes' => $latestVersionDetails['description'] ?? "Automatic update from GitHub release",
                        'features' => "New features in version $version",
                        'fixes' => "Bug fixes in version $version",
                        'is_critical' => false,
                        'is_security' => false,
                        'is_mandatory' => false
                    ]);
                    
                    $this->info("Created system update record");
                    
                    // Create clinic update records for all clinics
                    $clinicIds = DB::table('clinics')->pluck('id');
                    foreach ($clinicIds as $clinicId) {
                        ClinicUpdate::create([
                            'clinic_id' => $clinicId,
                            'update_id' => $systemUpdate->id,
                            'status' => 'pending'
                        ]);
                    }
                    
                    $this->info("Created clinic update records for " . count($clinicIds) . " clinics");
                    
                    return 0;
                } else {
                    $this->info("Version $version already exists in the database.");
                    return 0;
                }
            }
            
            $this->info("No new updates available.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error checking for updates: " . $e->getMessage());
            return 1;
        }
    }
} 