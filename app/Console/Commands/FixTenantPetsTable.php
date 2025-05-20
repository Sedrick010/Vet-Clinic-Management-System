<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixTenantPetsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-pets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds missing deleted_at column to pets table for all tenant databases';

    /**
     * The tenant database service
     */
    protected $tenantDatabaseService;

    /**
     * Create a new command instance.
     */
    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        parent::__construct();
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix pets tables for all tenant databases...');
        
        $clinics = Clinic::all();
        $totalClinics = $clinics->count();
        $this->info("Found {$totalClinics} clinics to process");
        
        $fixed = 0;
        $errors = 0;
        
        foreach ($clinics as $index => $clinic) {
            $this->info("Processing clinic ".($index+1)."/{$totalClinics}: {$clinic->name} ({$clinic->database_name})");
            
            try {
                // Switch to the tenant database
                $this->tenantDatabaseService->switchToTenant($clinic);
                
                // Check if the table exists
                if (!Schema::connection('tenant')->hasTable('pets')) {
                    $this->warn("  - Pets table doesn't exist in {$clinic->database_name}, skipping");
                    continue;
                }
                
                // Check if deleted_at column exists
                if (Schema::connection('tenant')->hasColumn('pets', 'deleted_at')) {
                    $this->info("  - deleted_at column already exists in pets table, skipping");
                    continue;
                }
                
                // Add the deleted_at column
                $this->info("  - Adding deleted_at column to pets table");
                Schema::connection('tenant')->table('pets', function ($table) {
                    $table->softDeletes();
                });
                
                $this->info("  - Successfully added deleted_at column to pets table in {$clinic->database_name}");
                $fixed++;
                
            } catch (\Exception $e) {
                $this->error("  - Error processing {$clinic->database_name}: " . $e->getMessage());
                Log::error("Error fixing pets table for {$clinic->database_name}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errors++;
            } finally {
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();
            }
        }
        
        $this->info("Completed fixing tenant databases:");
        $this->info("  - Total clinics processed: {$totalClinics}");
        $this->info("  - Databases fixed: {$fixed}");
        $this->info("  - Errors encountered: {$errors}");
        
        return Command::SUCCESS;
    }
} 