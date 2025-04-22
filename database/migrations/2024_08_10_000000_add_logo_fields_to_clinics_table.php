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
        Schema::table('clinics', function (Blueprint $table) {
            // Check if logo column exists, if not, add it
            if (!Schema::hasColumn('clinics', 'logo')) {
                $table->string('logo')->nullable();
            }
            
            // Add new columns for logo storage
            if (!Schema::hasColumn('clinics', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('logo');
            }
            
            if (!Schema::hasColumn('clinics', 'logo_disk')) {
                $table->string('logo_disk')->nullable()->after('logo_path')->default('public');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Drop the new columns if they exist
            if (Schema::hasColumn('clinics', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
            
            if (Schema::hasColumn('clinics', 'logo_disk')) {
                $table->dropColumn('logo_disk');
            }
        });
    }
}; 