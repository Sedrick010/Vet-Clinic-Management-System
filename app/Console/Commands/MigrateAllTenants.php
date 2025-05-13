<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Log;

class MigrateAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:all-tenants {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenant databases';

    /**
     * The tenant database service.
     *
     * @var TenantDatabaseService
     */
    protected $tenantDatabaseService;

    /**
     * Create a new command instance.
     *
     * @param TenantDatabaseService $tenantDatabaseService
     * @return void
     */
    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        parent::__construct();
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $force = $this->option('force');
        
        if (!$force && app()->environment('production')) {
            $this->error('This command cannot be run in production without the --force flag.');
            return 1;
        }
        
        $this->info('Starting migrations for all tenant databases...');

        // Get all clinics
        $clinics = Clinic::all();
        $successCount = 0;
        $failCount = 0;

        $this->output->progressStart(count($clinics));

        foreach ($clinics as $clinic) {
            $this->output->progressAdvance();
            
            try {
                $this->info("Migrating database for clinic: {$clinic->name} (ID: {$clinic->id}, DB: {$clinic->database_name})");
                
                // Switch to tenant database
                $this->tenantDatabaseService->switchToTenant($clinic);
                
                // Run migrations for this tenant
                $exitCode = $this->callSilent('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true
                ]);
                
                if ($exitCode === 0) {
                    $this->info("Successfully migrated database for clinic: {$clinic->name}");
                    $successCount++;
                } else {
                    $this->error("Failed to migrate database for clinic: {$clinic->name}");
                    $failCount++;
                    Log::error("Migration failed for clinic ID {$clinic->id}", [
                        'clinic_name' => $clinic->name,
                        'database_name' => $clinic->database_name
                    ]);
                }
                
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();
            } catch (\Exception $e) {
                $this->error("Exception migrating database for clinic {$clinic->name}: " . $e->getMessage());
                Log::error("Exception migrating database for clinic ID {$clinic->id}", [
                    'clinic_name' => $clinic->name,
                    'database_name' => $clinic->database_name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Ensure we switch back to main database even if there's an error
                $this->tenantDatabaseService->switchToMain();
                $failCount++;
            }
        }

        $this->output->progressFinish();
        
        $this->info("Migration completed for all tenant databases.");
        $this->info("Success: {$successCount}, Failed: {$failCount}");
        
        return $failCount > 0 ? 1 : 0;
    }
} 