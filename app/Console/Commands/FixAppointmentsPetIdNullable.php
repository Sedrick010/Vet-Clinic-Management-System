<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAppointmentsPetIdNullable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-pet-id-nullable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make pet_id nullable in appointments table for all tenant databases';

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
        $this->info('Starting to fix appointments tables for all tenant databases (making pet_id nullable)...');
        
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
                if (!Schema::connection('tenant')->hasTable('appointments')) {
                    $this->warn("  - Appointments table doesn't exist in {$clinic->database_name}, skipping");
                    continue;
                }
                
                // Check if pet_id column exists
                if (!Schema::connection('tenant')->hasColumn('appointments', 'pet_id')) {
                    $this->info("  - pet_id column doesn't exist in appointments table, skipping");
                    continue;
                }
                
                // Make pet_id nullable - use raw SQL since Schema builder might not handle constraint updates properly
                $this->info("  - Making pet_id nullable in appointments table");
                
                // Drop the foreign key constraint if it exists
                $foreignKeys = DB::connection('tenant')->select(
                    "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'appointments' 
                     AND COLUMN_NAME = 'pet_id' 
                     AND REFERENCED_TABLE_NAME IS NOT NULL"
                );
                
                if (!empty($foreignKeys)) {
                    $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                    DB::connection('tenant')->statement(
                        "ALTER TABLE appointments DROP FOREIGN KEY {$constraintName}"
                    );
                    $this->info("  - Dropped foreign key constraint: {$constraintName}");
                }
                
                // Modify the column to be nullable
                DB::connection('tenant')->statement(
                    "ALTER TABLE appointments MODIFY pet_id BIGINT UNSIGNED NULL"
                );
                
                // Add back the foreign key with ON DELETE SET NULL
                DB::connection('tenant')->statement(
                    "ALTER TABLE appointments ADD CONSTRAINT fk_appointments_pet_id 
                     FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL"
                );
                
                $this->info("  - Successfully made pet_id nullable in appointments table in {$clinic->database_name}");
                $fixed++;
                
            } catch (\Exception $e) {
                $this->error("  - Error processing {$clinic->database_name}: " . $e->getMessage());
                Log::error("Error fixing appointments table for {$clinic->database_name}", [
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