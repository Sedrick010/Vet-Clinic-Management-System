<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--fresh} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations on all tenant databases';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantDatabaseService = app(TenantDatabaseService::class);
        $clinics = Clinic::where('approval_status', 'approved')->get();
        
        if ($clinics->isEmpty()) {
            $this->info('No approved clinics found.');
            return;
        }
        
        $this->info('Found ' . $clinics->count() . ' approved clinics to migrate.');
        
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        
        $bar = $this->output->createProgressBar($clinics->count());
        $bar->start();
        
        $success = 0;
        $failed = 0;
        $failedClinics = [];
        
        foreach ($clinics as $clinic) {
            try {
                $this->info("\nMigrating: " . $clinic->name . ' (ID: ' . $clinic->id . ', DB: ' . $clinic->database_name . ')');
                
                // Ensure the database exists
                if (!$tenantDatabaseService->databaseExists($clinic->database_name)) {
                    $this->warn('Database does not exist for clinic: ' . $clinic->name);
                    $failedClinics[] = $clinic->name . ' (DB missing)';
                    $failed++;
                    continue;
                }
                
                // Switch to tenant database
                $tenantDatabaseService->switchToTenant($clinic);
                
                // Run migrations
                if ($fresh) {
                    $this->info('Running fresh migrations...');
                    Artisan::call('migrate:fresh', [
                        '--force' => true,
                        '--database' => 'tenant',
                        '--path' => 'database/migrations',
                    ]);
                    
                    if ($seed) {
                        $this->info('Seeding database...');
                        Artisan::call('db:seed', [
                            '--force' => true,
                            '--database' => 'tenant',
                        ]);
                    }
                } else {
                    $this->info('Running migrations...');
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--database' => 'tenant',
                        '--path' => 'database/migrations',
                    ]);
                }
                
                $this->info('Migration completed successfully for: ' . $clinic->name);
                $success++;
            } catch (\Exception $e) {
                $this->error('Failed to migrate for clinic: ' . $clinic->name . ' - ' . $e->getMessage());
                Log::error('Tenant migration failed: ' . $e->getMessage(), [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'database' => $clinic->database_name,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                $failedClinics[] = $clinic->name . ' (Error: ' . $e->getMessage() . ')';
                $failed++;
            } finally {
                // Switch back to main database
                DB::disconnect('tenant');
                $tenantDatabaseService->switchToMain();
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('Migration Summary:');
        $this->info('Total Clinics: ' . $clinics->count());
        $this->info('Successful Migrations: ' . $success);
        
        if ($failed > 0) {
            $this->error('Failed Migrations: ' . $failed);
            $this->error('Failed Clinics:');
            foreach ($failedClinics as $failedClinic) {
                $this->error('- ' . $failedClinic);
            }
        }
        
        return 0;
    }
} 