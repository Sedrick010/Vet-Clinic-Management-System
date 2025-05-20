<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExecuteSqlForTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:execute-sql {clinic_id} {sql_file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute a SQL script for a tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $sqlFile = $this->argument('sql_file');
        
        if (!File::exists($sqlFile)) {
            $this->error("SQL file {$sqlFile} does not exist.");
            return 1;
        }
        
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found.");
            return 1;
        }
        
        $this->info("Executing SQL script for clinic: {$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})");
        
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
        
        try {
            // Read SQL file
            $sql = File::get($sqlFile);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)), function($statement) {
                return !empty($statement);
            });
            
            // Execute each statement
            foreach ($statements as $statement) {
                $this->info("Executing: " . $statement);
                DB::connection('tenant')->statement($statement);
            }
            
            $this->info("SQL script executed successfully!");
        } catch (\Exception $e) {
            $this->error("Error executing SQL script: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 