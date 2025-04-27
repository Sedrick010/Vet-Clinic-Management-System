<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            Schema::connection($connection)->table('pets', function (Blueprint $table) {
                if (!Schema::connection(DB::connection()->getName())->hasColumn('pets', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
            
            Log::info('Added deleted_at column to pets table', [
                'connection' => $connection
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding deleted_at column to pets table: ' . $e->getMessage(), [
                'connection' => $connection,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('pets', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        } catch (\Exception $e) {
            Log::error('Error dropping deleted_at column from pets table: ' . $e->getMessage());
        }
    }
};
