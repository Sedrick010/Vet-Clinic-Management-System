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
        Schema::table('inventory_items', function (Blueprint $table) {
            // Add is_active column with default true if it doesn't exist
            if (!Schema::hasColumn('inventory_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('reorder_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('inventory_items', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
