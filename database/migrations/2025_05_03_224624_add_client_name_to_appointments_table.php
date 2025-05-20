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
        // Use raw SQL to add column since we might have connection issues
        try {
            // Try the default connection first
            DB::statement('ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) NULL AFTER client_id');
        } catch (\Exception $e) {
            // Log error and try tenant connection if default fails
            \Log::error('Failed to add client_name column using default connection: ' . $e->getMessage());
            
            try {
                DB::connection('tenant')->statement('ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) NULL AFTER client_id');
            } catch (\Exception $e2) {
                \Log::error('Failed to add client_name column using tenant connection: ' . $e2->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to drop column since we might have connection issues
        try {
            // Try the default connection first
            DB::statement('ALTER TABLE appointments DROP COLUMN client_name');
        } catch (\Exception $e) {
            // Log error and try tenant connection if default fails
            \Log::error('Failed to drop client_name column using default connection: ' . $e->getMessage());
            
            try {
                DB::connection('tenant')->statement('ALTER TABLE appointments DROP COLUMN client_name');
            } catch (\Exception $e2) {
                \Log::error('Failed to drop client_name column using tenant connection: ' . $e2->getMessage());
            }
        }
    }
};
