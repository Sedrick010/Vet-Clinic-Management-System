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
        // Add deleted_at to appointments table
        if (Schema::connection('tenant')->hasTable('appointments') && 
            !Schema::connection('tenant')->hasColumn('appointments', 'deleted_at')) {
            Schema::connection('tenant')->table('appointments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop deleted_at from appointments table
        if (Schema::connection('tenant')->hasTable('appointments') && 
            Schema::connection('tenant')->hasColumn('appointments', 'deleted_at')) {
            Schema::connection('tenant')->table('appointments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}; 