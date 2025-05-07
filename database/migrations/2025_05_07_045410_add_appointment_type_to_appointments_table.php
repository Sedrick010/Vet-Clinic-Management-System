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
            // Add appointment_type column if it doesn't exist
            if (!Schema::hasColumn('appointments', 'appointment_type')) {
                $table->string('appointment_type')->nullable()->after('status')->comment('Type of appointment (check-up, vaccination, etc.)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('appointment_type');
        });
    }
};
