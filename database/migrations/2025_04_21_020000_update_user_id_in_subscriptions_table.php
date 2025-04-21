<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to update the column constraint
        DB::statement('ALTER TABLE subscriptions MODIFY user_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the change
        DB::statement('ALTER TABLE subscriptions MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
}; 