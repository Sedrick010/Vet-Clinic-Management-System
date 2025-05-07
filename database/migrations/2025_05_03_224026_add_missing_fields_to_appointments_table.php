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
        // Check if the tenant connection is defined in the config
        $connectionName = 'mysql'; // Default fallback
        
        try {
            // Try to use tenant connection
            if (config('database.connections.tenant')) {
                $connectionName = 'tenant';
            }
        } catch (\Exception $e) {
            // Log or handle the exception
        }
        
        Schema::connection($connectionName)->table('appointments', function (Blueprint $table) use ($connectionName) {
            // Check if columns exist before adding them
            if (!Schema::connection($connectionName)->hasColumn('appointments', 'duration')) {
                $table->integer('duration')->nullable()->after('end_time')->comment('Duration in minutes');
            }

            if (!Schema::connection($connectionName)->hasColumn('appointments', 'appointment_type')) {
                $table->string('appointment_type')->nullable()->after('status')->comment('Type of appointment (check-up, vaccination, etc.)');
            }
            
            if (!Schema::connection($connectionName)->hasColumn('appointments', 'client_name')) {
                $table->string('client_name')->nullable()->after('client_id')->comment('Name of the client');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the tenant connection is defined in the config
        $connectionName = 'mysql'; // Default fallback
        
        try {
            // Try to use tenant connection
            if (config('database.connections.tenant')) {
                $connectionName = 'tenant';
            }
        } catch (\Exception $e) {
            // Log or handle the exception
        }
        
        Schema::connection($connectionName)->table('appointments', function (Blueprint $table) use ($connectionName) {
            if (Schema::connection($connectionName)->hasColumn('appointments', 'duration')) {
                $table->dropColumn('duration');
            }

            if (Schema::connection($connectionName)->hasColumn('appointments', 'appointment_type')) {
                $table->dropColumn('appointment_type');
            }
            
            if (Schema::connection($connectionName)->hasColumn('appointments', 'client_name')) {
                $table->dropColumn('client_name');
            }
        });
    }
};
