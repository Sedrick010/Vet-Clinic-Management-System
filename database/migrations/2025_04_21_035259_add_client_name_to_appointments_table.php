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
            $table->string('client_name')->after('id')->nullable();
            
            // Only drop the columns if they exist to avoid errors
            if (Schema::hasColumn('appointments', 'pet_id')) {
                // Remove foreign key first if it exists
                try {
                    $table->dropForeign(['pet_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('pet_id');
            }
            
            if (Schema::hasColumn('appointments', 'client_id')) {
                // Remove foreign key first if it exists
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
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
            if (Schema::hasColumn('appointments', 'client_name')) {
                $table->dropColumn('client_name');
            }
            
            // Add back the columns if they don't exist
            if (!Schema::hasColumn('appointments', 'pet_id')) {
                $table->foreignId('pet_id')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'client_id')) {
                $table->foreignId('client_id')->nullable();
            }
        });
    }
};
