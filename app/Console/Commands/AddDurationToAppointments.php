<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AddDurationToAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:add-duration-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add duration column to appointments table in all tenant databases';

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
        
        $this->info('Found ' . $clinics->count() . ' approved clinics to update.');
        
        $bar = $this->output->createProgressBar($clinics->count());
        $bar->start();
        
        $success = 0;
        $skipped = 0;
        $failed = 0;
        $failedClinics = [];
        
        foreach ($clinics as $clinic) {
            try {
                $this->info("\nProcessing: " . $clinic->name . ' (ID: ' . $clinic->id . ', DB: ' . $clinic->database_name . ')');
                
                // Ensure the database exists
                if (!$tenantDatabaseService->databaseExists($clinic->database_name)) {
                    $this->warn('Database does not exist for clinic: ' . $clinic->name);
                    $failedClinics[] = $clinic->name . ' (DB missing)';
                    $failed++;
                    continue;
                }
                
                // Switch to tenant database
                $tenantDatabaseService->switchToTenant($clinic);
                
                // Check if the appointments table exists
                if (!Schema::connection('tenant')->hasTable('appointments')) {
                    $this->warn('Appointments table does not exist for clinic: ' . $clinic->name);
                    $skipped++;
                    continue;
                }
                
                // Check if the duration column already exists
                if (Schema::connection('tenant')->hasColumn('appointments', 'duration')) {
                    $this->info('Duration column already exists for clinic: ' . $clinic->name);
                    $skipped++;
                    continue;
                }
                
                // Add the duration column
                Schema::connection('tenant')->table('appointments', function ($table) {
                    $table->integer('duration')->nullable()->after('end_time')->comment('Duration in minutes');
                });
                
                // Update existing appointments to calculate duration
                DB::connection('tenant')->statement('
                    UPDATE appointments 
                    SET duration = TIMESTAMPDIFF(MINUTE, start_time, end_time) 
                    WHERE start_time IS NOT NULL AND end_time IS NOT NULL
                ');
                
                $this->info('Successfully added duration column to appointments table for: ' . $clinic->name);
                $success++;
            } catch (\Exception $e) {
                $this->error('Failed to update for clinic: ' . $clinic->name . ' - ' . $e->getMessage());
                Log::error('Adding duration column failed: ' . $e->getMessage(), [
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
        $this->info('Operation Summary:');
        $this->info('Total Clinics: ' . $clinics->count());
        $this->info('Successfully Updated: ' . $success);
        $this->info('Skipped (Already exists or no appointments table): ' . $skipped);
        
        if ($failed > 0) {
            $this->error('Failed Updates: ' . $failed);
            $this->error('Failed Clinics:');
            foreach ($failedClinics as $failedClinic) {
                $this->error('- ' . $failedClinic);
            }
        }
        
        return 0;
    }
} 