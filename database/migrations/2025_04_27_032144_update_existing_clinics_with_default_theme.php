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
        // Update all existing clinics to have the 'default' theme
        DB::table('clinics')->whereNull('theme')->update(['theme' => 'default']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set theme back to null for all clinics that have the 'default' theme
        // This isn't a perfect reversal but it's the best we can do
        DB::table('clinics')->where('theme', 'default')->update(['theme' => null]);
    }
};
