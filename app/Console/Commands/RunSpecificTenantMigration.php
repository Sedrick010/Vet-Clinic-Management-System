<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RunSpecificTenantMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:run-migration {clinic_id} {migration_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a specific migration file for a tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $migrationPath = $this->argument('migration_path');
        
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found.");
            return 1;
        }
        
        $this->info("Running migration for clinic: {$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})");
        
        // Configure tenant database connection
        config(['database.connections.tenant' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => $clinic->database_name,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]]);
        
        // Clear any existing connections and reconnect
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Run the migration
        $this->call('migrate', [
            '--database' => 'tenant',
            '--path' => $migrationPath,
            '--force' => true,
        ]);
        
        $this->info("Migration completed for clinic: {$clinic->name}");
        
        return 0;
    }
} 