<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupTenantDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:setup {clinic_id? : The ID of the clinic to setup} {--all : Setup all clinics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup tenant database and run migrations for a specific clinic or all clinics';

    /**
     * Execute the console command.
     */
    public function handle(TenantDatabaseService $tenantDatabaseService)
    {
        $clinicId = $this->argument('clinic_id');
        $setupAll = $this->option('all');
        
        if (!$clinicId && !$setupAll) {
            $this->error('Please provide a clinic ID or use the --all option');
            return 1;
        }
        
        if ($setupAll) {
            $this->info('Setting up databases for all approved clinics...');
            $clinics = Clinic::where('approval_status', 'approved')->get();
            
            if ($clinics->isEmpty()) {
                $this->warn('No approved clinics found.');
                return 0;
            }
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($clinics as $clinic) {
                try {
                    $this->setupTenantDatabase($clinic, $tenantDatabaseService);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to setup database for clinic {$clinic->id} ({$clinic->name}): " . $e->getMessage());
                    $failCount++;
                }
            }
            
            $this->info("Database setup completed for {$successCount} clinics. Failed: {$failCount}");
            
        } else {
            // Setup for a specific clinic
            $clinic = Clinic::findOrFail($clinicId);
            
            try {
                $this->setupTenantDatabase($clinic, $tenantDatabaseService);
                $this->info("Database setup completed for clinic {$clinic->id} ({$clinic->name})");
            } catch (\Exception $e) {
                $this->error("Failed to setup database for clinic {$clinic->id} ({$clinic->name}): " . $e->getMessage());
                return 1;
            }
        }
        
        return 0;
    }
    
    /**
     * Setup database for a specific clinic
     */
    protected function setupTenantDatabase(Clinic $clinic, TenantDatabaseService $tenantDatabaseService) 
    {
        $this->info("Setting up database for clinic {$clinic->id} ({$clinic->name})...");
        
        // Create or verify database exists
        if (!$tenantDatabaseService->databaseExists($clinic->database_name)) {
            $this->info("Database {$clinic->database_name} doesn't exist, creating...");
            try {
                DB::statement("CREATE DATABASE `" . str_replace('`', '', $clinic->database_name) . "`");
            } catch (\Exception $e) {
                throw new \Exception("Failed to create database: " . $e->getMessage());
            }
        }
        
        // Switch to tenant database
        $tenantDatabaseService->switchToTenant($clinic);
        
        // Check if staff table exists
        $hasStaffTable = Schema::connection('tenant')->hasTable('staff');
        
        if (!$hasStaffTable) {
            $this->info("Creating staff table in {$clinic->database_name}...");
            
            try {
                // Run migrations
                $this->call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                
                // Verify the table was created
                if (!Schema::connection('tenant')->hasTable('staff')) {
                    throw new \Exception("Migration ran but staff table not created");
                }
                
                $this->info("Staff table created successfully.");
            } catch (\Exception $e) {
                throw new \Exception("Failed to run migrations: " . $e->getMessage());
            }
        } else {
            $this->info("Staff table already exists.");
        }
        
        // Switch back to main database
        $tenantDatabaseService->switchToMain();
    }
}
