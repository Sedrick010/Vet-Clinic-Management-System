<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixTenantAppointmentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-appointments-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds missing client_name column to appointments table for all tenant databases';

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
        $this->info('Starting to fix appointments tables for all tenant databases (adding client_name column)...');
        
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
                
                // Check if client_name column exists
                if (Schema::connection('tenant')->hasColumn('appointments', 'client_name')) {
                    $this->info("  - client_name column already exists in appointments table, skipping");
                    continue;
                }
                
                // Add the client_name column
                $this->info("  - Adding client_name column to appointments table");
                Schema::connection('tenant')->table('appointments', function ($table) {
                    $table->string('client_name')->nullable()->after('id');
                });
                
                $this->info("  - Successfully added client_name column to appointments table in {$clinic->database_name}");
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