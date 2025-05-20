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
        Schema::table('appointments', function (Blueprint $table) {
            // Check if veterinarian_id exists and staff_id doesn't exist
            if (Schema::hasColumn('appointments', 'veterinarian_id') && !Schema::hasColumn('appointments', 'staff_id')) {
                // If we have old foreign key, drop it first
                DB::statement('ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_veterinarian_id_foreign');
                
                // Rename the column
                $table->renameColumn('veterinarian_id', 'staff_id');
                
                // Add new foreign key to staff table if needed
                if (!DB::select("SHOW KEYS FROM appointments WHERE Key_name = 'appointments_staff_id_foreign'")) {
                    $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Check if staff_id exists and veterinarian_id doesn't exist
            if (Schema::hasColumn('appointments', 'staff_id') && !Schema::hasColumn('appointments', 'veterinarian_id')) {
                // If we have foreign key, drop it first
                DB::statement('ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_staff_id_foreign');
                
                // Rename the column back
                $table->renameColumn('staff_id', 'veterinarian_id');
                
                // Add original foreign key back if needed
                if (!DB::select("SHOW KEYS FROM appointments WHERE Key_name = 'appointments_veterinarian_id_foreign'")) {
                    $table->foreign('veterinarian_id')->references('id')->on('users')->onDelete('cascade');
                }
            }
        });
    }
}; 