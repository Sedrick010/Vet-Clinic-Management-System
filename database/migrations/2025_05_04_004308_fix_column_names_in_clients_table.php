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
        // Try both default and tenant connections for multi-tenant support
        foreach (['mysql', 'tenant'] as $connectionName) {
            try {
                if (!config("database.connections.{$connectionName}")) {
                    continue;
                }
                
                if (Schema::connection($connectionName)->hasTable('clients')) {
                    Schema::connection($connectionName)->table('clients', function (Blueprint $table) use ($connectionName) {
                        // Check if zip exists but postal_code doesn't
                        $hasZip = Schema::connection($connectionName)->hasColumn('clients', 'zip');
                        $hasPostalCode = Schema::connection($connectionName)->hasColumn('clients', 'postal_code');
                        
                        if ($hasZip && !$hasPostalCode) {
                            // Rename zip to postal_code to match the model
                            DB::connection($connectionName)->statement('ALTER TABLE clients CHANGE zip postal_code VARCHAR(20) NULL');
                            \Log::info("Renamed 'zip' to 'postal_code' in clients table on {$connectionName} connection");
                        } elseif (!$hasZip && !$hasPostalCode) {
                            // Add postal_code if neither exists
                            $table->string('postal_code')->nullable()->after('state');
                            \Log::info("Added 'postal_code' column to clients table on {$connectionName} connection");
                        }
                        
                        // Check for other required fields and add them if missing
                        if (!Schema::connection($connectionName)->hasColumn('clients', 'deleted_at')) {
                            $table->softDeletes();
                            \Log::info("Added 'deleted_at' column to clients table on {$connectionName} connection");
                        }
                    });
                }
            } catch (\Exception $e) {
                \Log::error("Error updating clients table on {$connectionName} connection: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse these changes as they're meant to fix inconsistencies
        // But we could rename postal_code back to zip if needed
        /*
        foreach (['mysql', 'tenant'] as $connectionName) {
            try {
                if (!config("database.connections.{$connectionName}")) {
                    continue;
                }
                
                if (Schema::connection($connectionName)->hasTable('clients') && 
                    Schema::connection($connectionName)->hasColumn('clients', 'postal_code')) {
                    
                    Schema::connection($connectionName)->table('clients', function (Blueprint $table) {
                        $table->renameColumn('postal_code', 'zip');
                    });
                }
            } catch (\Exception $e) {
                \Log::error("Error in down migration for clients table: " . $e->getMessage());
            }
        }
        */
    }
};
