<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;

class RunFixTablesForAllClinics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-fix-tables-for-all-clinics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the fix:tables command for all clinics to ensure all tables have deleted_at columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running fix:tables command for all clinics...');
        
        $clinics = Clinic::all();
        
        if ($clinics->isEmpty()) {
            $this->warn('No clinics found.');
            return 0;
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($clinics as $clinic) {
            try {
                $this->info("Processing clinic: {$clinic->name} (ID: {$clinic->id})");
                
                $this->call('fix:tables', [
                    'clinic_id' => $clinic->id,
                ]);
                
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to fix tables for clinic {$clinic->id} ({$clinic->name}): " . $e->getMessage());
                $failCount++;
            }
        }
        
        $this->info("Fixed tables for {$successCount} clinics. Failed: {$failCount}");
        
        return 0;
    }
} 