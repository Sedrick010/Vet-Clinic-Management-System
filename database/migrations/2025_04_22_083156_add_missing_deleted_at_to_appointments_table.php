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
        $connection = DB::connection()->getName();
        
        // We want to ensure this works for both the main database and tenant databases
        try {
            Schema::connection($connection)->table('appointments', function (Blueprint $table) {
                if (!Schema::connection(DB::connection()->getName())->hasColumn('appointments', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        } catch (\Exception $e) {
            \Log::error('Error adding deleted_at column: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        } catch (\Exception $e) {
            \Log::error('Error dropping deleted_at column: ' . $e->getMessage());
        }
    }
};
