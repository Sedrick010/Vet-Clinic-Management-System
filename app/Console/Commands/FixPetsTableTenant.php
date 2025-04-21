<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixPetsTableTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-pets-table {clinic_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the pets table in a tenant database by adding missing timestamp columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found.");
            return 1;
        }
        
        $this->info("Fixing pets table for clinic: {$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})");
        
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
            // Check if columns exist
            $columns = DB::connection('tenant')->select("SHOW COLUMNS FROM pets");
            $columnNames = array_map(function($column) {
                return $column->Field;
            }, $columns);
            
            // Add created_at if missing
            if (!in_array('created_at', $columnNames)) {
                $this->info("Adding created_at column to pets table...");
                DB::connection('tenant')->statement('ALTER TABLE pets ADD COLUMN created_at TIMESTAMP NULL');
            } else {
                $this->info("created_at column already exists in pets table.");
            }
            
            // Add updated_at if missing
            if (!in_array('updated_at', $columnNames)) {
                $this->info("Adding updated_at column to pets table...");
                DB::connection('tenant')->statement('ALTER TABLE pets ADD COLUMN updated_at TIMESTAMP NULL');
            } else {
                $this->info("updated_at column already exists in pets table.");
            }
            
            // Set default values for timestamps
            DB::connection('tenant')->statement('UPDATE pets SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL OR updated_at IS NULL');
            
            $this->info("Pets table fixed successfully!");
        } catch (\Exception $e) {
            $this->error("Error fixing pets table: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 