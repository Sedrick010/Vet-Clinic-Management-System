<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'client_name')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('client_name')->after('id')->nullable();
                });
                
                Log::info('Added client_name column to appointments table');
            }
        } catch (\Exception $e) {
            Log::error('Failed to add client_name column to appointments table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'client_name')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropColumn('client_name');
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to drop client_name column from appointments table: ' . $e->getMessage());
        }
    }
};
