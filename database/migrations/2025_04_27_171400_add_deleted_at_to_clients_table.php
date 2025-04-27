<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a function to add deleted_at to tenant database
        $addDeletedAtToClientsTable = function($tenantDatabase) {
            // Check if the clients table exists
            $tableExists = DB::connection('mysql')
                ->select("SELECT COUNT(*) as table_exists FROM information_schema.tables 
                         WHERE table_schema = '{$tenantDatabase}' AND table_name = 'clients'");
            
            if ($tableExists[0]->table_exists > 0) {
                // Check if the deleted_at column already exists
                $columnExists = DB::connection('mysql')
                    ->select("SELECT COUNT(*) as column_exists FROM information_schema.columns 
                              WHERE table_schema = '{$tenantDatabase}' 
                              AND table_name = 'clients' 
                              AND column_name = 'deleted_at'");
                
                if ($columnExists[0]->column_exists == 0) {
                    // Add the deleted_at column to the clients table
                    DB::connection('mysql')
                        ->statement("ALTER TABLE `{$tenantDatabase}`.`clients` ADD `deleted_at` TIMESTAMP NULL");
                }
            }
        };
        
        // Get all tenant databases
        $tenantDatabases = DB::connection('mysql')
            ->select("SELECT database_name FROM clinics WHERE database_name IS NOT NULL");
        
        // Apply the schema change to each tenant database
        foreach ($tenantDatabases as $tenant) {
            if (!empty($tenant->database_name)) {
                $addDeletedAtToClientsTable($tenant->database_name);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all tenant databases
        $tenantDatabases = DB::connection('mysql')
            ->select("SELECT database_name FROM clinics WHERE database_name IS NOT NULL");
        
        // Remove the deleted_at column from each tenant database
        foreach ($tenantDatabases as $tenant) {
            if (!empty($tenant->database_name)) {
                // Check if the clients table exists
                $tableExists = DB::connection('mysql')
                    ->select("SELECT COUNT(*) as table_exists FROM information_schema.tables 
                             WHERE table_schema = '{$tenant->database_name}' AND table_name = 'clients'");
                
                if ($tableExists[0]->table_exists > 0) {
                    // Check if the deleted_at column exists
                    $columnExists = DB::connection('mysql')
                        ->select("SELECT COUNT(*) as column_exists FROM information_schema.columns 
                                  WHERE table_schema = '{$tenant->database_name}' 
                                  AND table_name = 'clients' 
                                  AND column_name = 'deleted_at'");
                    
                    if ($columnExists[0]->column_exists > 0) {
                        // Drop the deleted_at column
                        DB::connection('mysql')
                            ->statement("ALTER TABLE `{$tenant->database_name}`.`clients` DROP COLUMN `deleted_at`");
                    }
                }
            }
        }
    }
};
