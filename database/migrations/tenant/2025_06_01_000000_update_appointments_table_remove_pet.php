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
        Schema::table('appointments', function (Blueprint $table) {
            // Add client_name field
            $table->string('client_name')->after('id');

            // Drop foreign key constraints
            $table->dropForeign(['pet_id']);
            $table->dropForeign(['client_id']);

            // Drop columns
            $table->dropColumn(['pet_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add back the columns
            $table->foreignId('pet_id')->after('id')->nullable();
            $table->foreignId('client_id')->after('pet_id')->nullable();
            
            // Drop client_name field
            $table->dropColumn('client_name');
        });
    }
}; 