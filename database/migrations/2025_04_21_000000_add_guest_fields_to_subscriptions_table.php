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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('guest_clinic_name')->nullable()->after('user_id');
            $table->string('guest_email')->nullable()->after('guest_clinic_name');
            $table->string('guest_phone')->nullable()->after('guest_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('guest_clinic_name');
            $table->dropColumn('guest_email');
            $table->dropColumn('guest_phone');
        });
    }
}; 