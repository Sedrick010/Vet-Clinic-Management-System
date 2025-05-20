<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CheckAppointmentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-appointments-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the structure of appointments table in tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Configure tenant connection
        $dbName = 'vet_clinic_animalandia_3jjjgmxj';
        
        // Check if the database exists
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
        if (empty($exists)) {
            $this->error("Database {$dbName} does not exist!");
            return 1;
        }
        
        // Set up the tenant connection with the proper database name
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
        
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Get columns from appointments table
        $this->info("Checking appointments table structure in {$dbName}...");
        
        $columns = DB::connection('tenant')
            ->select("SHOW COLUMNS FROM appointments");
            
        $this->info("Appointments table has the following columns:");
        foreach ($columns as $column) {
            $this->line("- " . $column->Field . " (" . $column->Type . ")");
        }
        
        // Check if client_name column exists
        $hasClientName = false;
        foreach ($columns as $column) {
            if ($column->Field === 'client_name') {
                $hasClientName = true;
                break;
            }
        }
        
        if (!$hasClientName) {
            $this->error("The 'client_name' column is missing from the appointments table!");
            $this->info("Let's add it now...");
            
            try {
                DB::connection('tenant')->statement("ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) AFTER id");
                $this->info("Successfully added 'client_name' column to the appointments table.");
            } catch (\Exception $e) {
                $this->error("Failed to add 'client_name' column: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("The 'client_name' column exists in the appointments table.");
        }
        
        return 0;
    }
} 