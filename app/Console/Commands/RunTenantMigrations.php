<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunTenantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle(TenantDatabaseService $tenantDatabaseService)
    {
        $this->info('Starting tenant migrations...');

        $clinics = Clinic::all();
        $count = 0;

        foreach ($clinics as $clinic) {
            try {
                $this->info("Migrating database for clinic: {$clinic->name}");
                
                // Switch to tenant database
                $tenantDatabaseService->switchToTenant($clinic);
                
                // Run migrations
                $tenantDatabaseService->runTenantMigrations($clinic);
                
                $count++;
                $this->info("Successfully migrated database for clinic: {$clinic->name}");
            } catch (\Exception $e) {
                $this->error("Failed to migrate database for clinic {$clinic->name}: {$e->getMessage()}");
                Log::error('Tenant migration failed', [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Completed migrations for {$count} clinics.");
    }
} 