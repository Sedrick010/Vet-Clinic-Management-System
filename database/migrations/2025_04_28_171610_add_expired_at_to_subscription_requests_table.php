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
        Schema::table('subscription_requests', function (Blueprint $table) {
            // Add the expired_at column if it doesn't exist
            if (!Schema::hasColumn('subscription_requests', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('subscription_requests', 'expired_at')) {
                $table->dropColumn('expired_at');
            }
        });
    }
};
