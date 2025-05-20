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
            if (!Schema::hasColumn('clinics', 'subscription_plan')) {
                $table->string('subscription_plan')->default('free')->after('is_active'); // free, basic, premium, etc.
            }
            if (!Schema::hasColumn('clinics', 'subscription_ends_at')) {
                $table->dateTime('subscription_ends_at')->nullable()->after('subscription_plan');
            }
            if (!Schema::hasColumn('clinics', 'is_subscription_active')) {
                $table->boolean('is_subscription_active')->default(true)->after('subscription_ends_at');
            }
            if (!Schema::hasColumn('clinics', 'deactivation_reason')) {
                $table->string('deactivation_reason')->nullable()->after('is_subscription_active');
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
            $columns = [
                'subscription_plan',
                'subscription_ends_at',
                'is_subscription_active',
                'deactivation_reason'
            ];
            
            $columnsToRemove = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('clinics', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
