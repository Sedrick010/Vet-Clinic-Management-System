<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTenantTableStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:check-table {clinic_id} {table_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the structure of a table in a tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $tableName = $this->argument('table_name');
        
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found.");
            return 1;
        }
        
        $this->info("Checking table structure for clinic: {$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})");
        
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
        
        if (!Schema::connection('tenant')->hasTable($tableName)) {
            $this->error("Table {$tableName} does not exist in the tenant database.");
            return 1;
        }
        
        // Get the table structure
        $columns = DB::connection('tenant')->select("SHOW COLUMNS FROM {$tableName}");
        
        $this->info("Columns in table {$tableName}:");
        $this->table(['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'], collect($columns)->map(function ($column) {
            return [
                'Field' => $column->Field,
                'Type' => $column->Type,
                'Null' => $column->Null,
                'Key' => $column->Key,
                'Default' => $column->Default,
                'Extra' => $column->Extra,
            ];
        }));
        
        return 0;
    }
} 