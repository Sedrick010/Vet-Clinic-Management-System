<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Log;

class FixTenantDatabase extends Command
{
    protected $signature = 'tenant:fix {clinic_id : The ID of the clinic to fix}';
    protected $description = 'Fix a tenant database by running migrations';

    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        
        try {
            $clinic = Clinic::findOrFail($clinicId);
            
            if ($clinic->approval_status !== 'approved') {
                $this->error("Clinic {$clinic->name} is not approved. Only approved clinics can have their databases fixed.");
                return 1;
            }
            
            $this->info("Fixing database for clinic: {$clinic->name}");
            
            $tenantDatabaseService = app(TenantDatabaseService::class);
            
            // Check if database exists
            if (!$tenantDatabaseService->databaseExists($clinic->database_name)) {
                $this->error("Database {$clinic->database_name} does not exist.");
                return 1;
            }
            
            // Run the setup process which includes migrations
            $tenantDatabaseService->setupTenantDatabase($clinic);
            
            $this->info("Successfully fixed database for clinic: {$clinic->name}");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error fixing database: " . $e->getMessage());
            Log::error("Error fixing tenant database", [
                'clinic_id' => $clinicId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
} 