<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add client_name column if it doesn't exist
            if (!Schema::hasColumn('appointments', 'client_name')) {
                $table->string('client_name')->after('id')->nullable();
            }
            
            // Remove pet_id and client_id columns if they exist
            if (Schema::hasColumn('appointments', 'pet_id')) {
                // Try to drop foreign key first
                try {
                    $table->dropForeign(['pet_id']);
                } catch (\Exception $e) {
                    // Foreign key constraint might not exist
                }
                $table->dropColumn('pet_id');
            }
            
            if (Schema::hasColumn('appointments', 'client_id')) {
                // Try to drop foreign key first
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Foreign key constraint might not exist
                }
                $table->dropColumn('client_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop client_name column if it exists
            if (Schema::hasColumn('appointments', 'client_name')) {
                $table->dropColumn('client_name');
            }
            
            // Add back pet_id and client_id columns if they don't exist
            if (!Schema::hasColumn('appointments', 'pet_id')) {
                $table->foreignId('pet_id')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'client_id')) {
                $table->foreignId('client_id')->nullable();
            }
        });
    }
}; 