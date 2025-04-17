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
        Schema::table('subscriptions', function (Blueprint $table) {
            // First modify the columns to be VARCHAR to prevent data truncation
            $table->string('status')->change();
            $table->string('approval_status')->change();
        });

        // Update existing records to use the correct status values
        DB::table('subscriptions')->where('status', 'inactive')->update(['status' => 'inactive']);
        DB::table('subscriptions')->where('status', 'active')->update(['status' => 'active']);
        DB::table('subscriptions')->where('status', 'pending')->update(['status' => 'pending']);

        DB::table('subscriptions')->where('approval_status', 'rejected')->update(['approval_status' => 'rejected']);
        DB::table('subscriptions')->where('approval_status', 'approved')->update(['approval_status' => 'approved']);
        DB::table('subscriptions')->where('approval_status', 'pending')->update(['approval_status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as we're just ensuring correct data format
    }
}; 