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
            // Only add columns if they don't exist
            if (!Schema::hasColumn('clinics', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('clinics', 'disable_reason')) {
                $table->string('disable_reason')->nullable()->after('is_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Only drop columns if they exist
            $columnsToRemove = [];
            
            if (Schema::hasColumn('clinics', 'is_enabled')) {
                $columnsToRemove[] = 'is_enabled';
            }
            
            if (Schema::hasColumn('clinics', 'disable_reason')) {
                $columnsToRemove[] = 'disable_reason';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
