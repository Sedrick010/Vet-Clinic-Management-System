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
            // Add the amount_paid column if it doesn't exist
            if (!Schema::hasColumn('subscription_requests', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0.00)->after('payment_method');
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
            if (Schema::hasColumn('subscription_requests', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
        });
    }
};
