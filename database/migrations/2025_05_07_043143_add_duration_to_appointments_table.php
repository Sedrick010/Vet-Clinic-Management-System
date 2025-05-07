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
            // Check if the column doesn't exist before adding it
            if (!Schema::hasColumn('appointments', 'duration')) {
                $table->integer('duration')->nullable()->after('end_time')->comment('Duration in minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Only drop the column if it exists
            if (Schema::hasColumn('appointments', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};
