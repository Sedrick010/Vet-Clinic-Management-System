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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('clinics', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            }
            
            if (!Schema::hasColumn('clinics', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('clinics', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            
            if (Schema::hasColumn('clinics', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
