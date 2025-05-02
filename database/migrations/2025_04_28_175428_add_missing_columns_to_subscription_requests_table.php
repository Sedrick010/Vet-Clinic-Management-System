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
            // Add plan and duration columns
            if (!Schema::hasColumn('subscription_requests', 'plan')) {
                $table->string('plan')->default('basic')->after('clinic_id');
            }
            
            if (!Schema::hasColumn('subscription_requests', 'duration')) {
                $table->integer('duration')->default(1)->after('plan');
            }
            
            // Add client info columns
            if (!Schema::hasColumn('subscription_requests', 'guest_clinic_name')) {
                $table->string('guest_clinic_name')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('subscription_requests', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('guest_clinic_name');
            }
            
            if (!Schema::hasColumn('subscription_requests', 'guest_phone')) {
                $table->string('guest_phone')->nullable()->after('guest_email');
            }
            
            // Add cancellation and rejection columns
            if (!Schema::hasColumn('subscription_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            }
            
            if (!Schema::hasColumn('subscription_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('cancelled_at');
            }
            
            // Add auto-renew flag
            if (!Schema::hasColumn('subscription_requests', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('status');
            }
            
            // Add user_id for relationship with User model
            if (!Schema::hasColumn('subscription_requests', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('clinic_id')->constrained()->nullOnDelete();
            }
            
            // Add payment_details column
            if (!Schema::hasColumn('subscription_requests', 'payment_details')) {
                $table->text('payment_details')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            // Remove all added columns
            $columns = [
                'plan', 'duration', 'guest_clinic_name', 'guest_email', 
                'guest_phone', 'cancelled_at', 'rejection_reason', 
                'auto_renew', 'user_id', 'payment_details'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('subscription_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
