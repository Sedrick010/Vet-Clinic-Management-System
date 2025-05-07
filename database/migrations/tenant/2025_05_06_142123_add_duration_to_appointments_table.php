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
            if (!Schema::hasColumn('appointments', 'duration')) {
                $table->integer('duration')->after('end_time')->comment('Duration in minutes');
            }
            
            if (!Schema::hasColumn('appointments', 'appointment_type')) {
                $table->string('appointment_type')->after('status')->comment('Type of appointment (check-up, vaccination, etc.)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'duration')) {
                $table->dropColumn('duration');
            }
            
            if (Schema::hasColumn('appointments', 'appointment_type')) {
                $table->dropColumn('appointment_type');
            }
        });
    }
};
