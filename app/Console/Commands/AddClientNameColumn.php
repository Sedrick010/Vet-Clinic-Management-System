<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;

class AddClientNameColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-client-name-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add client_name column to appointments table and remove pet_id and client_id columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantDbService = new TenantDatabaseService();
        
        // Get all clinics
        $clinics = Clinic::all();
        
        if ($clinics->isEmpty()) {
            $this->error('No clinics found!');
            return 1;
        }
        
        foreach ($clinics as $clinic) {
            $this->info("Modifying appointments table for clinic: {$clinic->name} (Database: {$clinic->database_name})");
            
            // Switch to tenant database
            $tenantDbService->switchToTenant($clinic);
            
            try {
                // Check if table exists
                if (!Schema::connection('tenant')->hasTable('appointments')) {
                    $this->warn("Appointments table doesn't exist for clinic: {$clinic->name}");
                    continue;
                }
                
                // Add client_name column if it doesn't exist
                if (!Schema::connection('tenant')->hasColumn('appointments', 'client_name')) {
                    $this->info("Adding client_name column...");
                    DB::connection('tenant')->statement('ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) AFTER id');
                    $this->info("client_name column added successfully!");
                } else {
                    $this->line("client_name column already exists.");
                }
        
                // Disable foreign key checks
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 0');
                
                // Remove foreign keys if they exist (try-catch in case they don't)
                try {
                    if (Schema::connection('tenant')->hasColumn('appointments', 'pet_id')) {
                        $this->info("Dropping pet_id foreign key constraint...");
                        DB::connection('tenant')->statement('ALTER TABLE appointments DROP FOREIGN KEY appointments_pet_id_foreign');
                    }
                    
                    if (Schema::connection('tenant')->hasColumn('appointments', 'client_id')) {
                        $this->info("Dropping client_id foreign key constraint...");
                        DB::connection('tenant')->statement('ALTER TABLE appointments DROP FOREIGN KEY appointments_client_id_foreign');
                    }
                } catch (\Exception $e) {
                    $this->warn("Note: Foreign key constraints may not exist or could not be dropped: {$e->getMessage()}");
                }
                
                // Drop columns if they exist
                if (Schema::connection('tenant')->hasColumn('appointments', 'pet_id')) {
                    $this->info("Dropping pet_id column...");
                    DB::connection('tenant')->statement('ALTER TABLE appointments DROP COLUMN pet_id');
                }
                
                if (Schema::connection('tenant')->hasColumn('appointments', 'client_id')) {
                    $this->info("Dropping client_id column...");
                    DB::connection('tenant')->statement('ALTER TABLE appointments DROP COLUMN client_id');
                }
                
                // Re-enable foreign key checks
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 1');
                
                $this->info("Successfully modified appointments table for clinic: {$clinic->name}");
            } catch (\Exception $e) {
                $this->error("Error for clinic {$clinic->name}: {$e->getMessage()}");
            }
        }
        
        $this->info("Command completed!");
        return 0;
    }
} 