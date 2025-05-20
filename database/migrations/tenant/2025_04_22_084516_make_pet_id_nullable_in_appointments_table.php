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
            if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'pet_id')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->foreignId('pet_id')->nullable()->change();
                });
                
                Log::info('Made pet_id nullable in appointments table');
            }
        } catch (\Exception $e) {
            Log::error('Failed to make pet_id nullable in appointments table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'pet_id')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->foreignId('pet_id')->nullable(false)->change();
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to make pet_id required in appointments table: ' . $e->getMessage());
        }
    }
};
