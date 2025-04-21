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
        // Check if cost_price column exists
        if (Schema::hasColumn('inventory_items', 'cost_price')) {
            // Execute raw SQL to modify existing column with default value
            DB::statement('ALTER TABLE inventory_items MODIFY cost_price DECIMAL(10,2) DEFAULT 0');
        } else {
            // Add the column if it doesn't exist
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to do anything in the down method
        // as we're just setting a default value, not removing the column
    }
};
