<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAppointmentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:tables {clinic_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the deleted_at column to tables that need soft deletes';

    /**
     * The tenant database service instance.
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
        $clinicId = $this->argument('clinic_id');

        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found.");
                return 1;
            }

            $this->fixTables($clinic);
        } else {
            $clinics = Clinic::all();
            $this->info("Adding deleted_at column to tables for all clinics...");
            
            foreach ($clinics as $clinic) {
                $this->fixTables($clinic);
            }
        }

        $this->info("Command completed successfully!");
        return 0;
    }

    /**
     * Fix tables for a specific clinic.
     */
    private function fixTables(Clinic $clinic)
    {
        try {
            $this->info("Processing clinic: {$clinic->name} (ID: {$clinic->id})");
            
            // Switch to tenant connection
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Fix the appointments table
            $this->fixTable($clinic, 'appointments');
            
            // Fix the pets table
            $this->fixTable($clinic, 'pets');
            
        } catch (\Exception $e) {
            $this->error("Error processing clinic {$clinic->name}: " . $e->getMessage());
        }
    }
    
    /**
     * Fix a specific table by adding the deleted_at column if it doesn't exist.
     */
    private function fixTable(Clinic $clinic, string $tableName)
    {
        try {
            // Check if table exists
            if (!Schema::connection('tenant')->hasTable($tableName)) {
                $this->warn("{$tableName} table does not exist for clinic: {$clinic->name}");
                return;
            }
            
            // Check if deleted_at column already exists
            if (Schema::connection('tenant')->hasColumn($tableName, 'deleted_at')) {
                $this->info("deleted_at column already exists for {$tableName} table in clinic: {$clinic->name}");
                return;
            }
            
            // Add deleted_at column
            Schema::connection('tenant')->table($tableName, function ($table) {
                $table->softDeletes();
            });
            
            $this->info("Successfully added deleted_at column to {$tableName} table for clinic: {$clinic->name}");
        } catch (\Exception $e) {
            $this->error("Error processing {$tableName} table for clinic {$clinic->name}: " . $e->getMessage());
        }
    }
} 