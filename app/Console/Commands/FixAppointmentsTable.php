<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class FixAppointmentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-appointments {clinic_id? : Specific clinic ID to fix} {--all : Fix all clinics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add deleted_at column to appointments table for soft deletes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $fixAll = $this->option('all');

        if (!$clinicId && !$fixAll) {
            $this->error('Please provide a clinic ID or use --all option');
            return 1;
        }

        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found");
                return 1;
            }
            $this->fixAppointmentsTable($clinic);
        } else {
            $clinics = Clinic::where('approval_status', 'approved')->get();
            $this->info("Fixing appointments table for {$clinics->count()} clinics...");
            
            $this->withProgressBar($clinics, function ($clinic) {
                $this->fixAppointmentsTable($clinic, false);
            });
            
            $this->newLine(2);
            $this->info("All clinics processed!");
        }

        return 0;
    }

    /**
     * Fix the appointments table for a specific clinic
     */
    private function fixAppointmentsTable(Clinic $clinic, bool $verbose = true)
    {
        if ($verbose) {
            $this->info("Processing clinic: {$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})");
        }

        try {
            // Check if database exists
            $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$clinic->database_name]);
            
            if (empty($dbExists)) {
                if ($verbose) {
                    $this->warn("Database {$clinic->database_name} does not exist. Skipping.");
                }
                return;
            }

            // Configure tenant database connection
            config(['database.connections.tenant' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => $clinic->database_name,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]]);

            // Clear any existing connections
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Check if appointments table exists
            if (!Schema::connection('tenant')->hasTable('appointments')) {
                if ($verbose) {
                    $this->warn("Appointments table does not exist in {$clinic->database_name}. Skipping.");
                }
                return;
            }

            // Check if deleted_at column already exists
            if (Schema::connection('tenant')->hasColumn('appointments', 'deleted_at')) {
                if ($verbose) {
                    $this->info("deleted_at column already exists in appointments table. Skipping.");
                }
                return;
            }

            // Add deleted_at column
            DB::connection('tenant')->statement('ALTER TABLE appointments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL');
            DB::connection('tenant')->statement('ALTER TABLE appointments ADD INDEX appointments_deleted_at_index (deleted_at)');

            if ($verbose) {
                $this->info("Successfully added deleted_at column to appointments table!");
            }

            Log::info("Fixed appointments table for clinic", [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name,
            ]);

        } catch (\Exception $e) {
            if ($verbose) {
                $this->error("Error fixing appointments table: " . $e->getMessage());
            }
            
            Log::error("Error fixing appointments table", [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name,
                'error' => $e->getMessage(),
            ]);
        }
    }
} 