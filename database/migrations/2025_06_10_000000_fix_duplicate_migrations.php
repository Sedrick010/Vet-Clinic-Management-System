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
        // Fix for duplicate clinic_updates table
        if (!Schema::hasTable('clinic_updates')) {
            Schema::create('clinic_updates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('clinic_id');
                $table->string('version');
                $table->enum('status', ['pending', 'applied', 'dismissed'])->default('pending');
                $table->timestamp('applied_at')->nullable();
                $table->timestamp('dismissed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            });
            
            Log::info('Created missing clinic_updates table');
        } else {
            Log::info('clinic_updates table already exists, skipping creation');
        }
        
        // Fix for missing system_updates table
        if (!Schema::hasTable('system_updates')) {
            Schema::create('system_updates', function (Blueprint $table) {
                $table->id();
                $table->string('version');
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_critical')->default(false);
                $table->timestamp('released_at')->nullable();
                $table->timestamps();
            });
            
            Log::info('Created missing system_updates table');
        } else {
            Log::info('system_updates table already exists, skipping creation');
        }
        
        // Make sure system_versions table exists
        if (!Schema::hasTable('system_versions')) {
            Schema::create('system_versions', function (Blueprint $table) {
                $table->id();
                $table->string('version');
                $table->string('name');
                $table->text('description');
                $table->boolean('is_current')->default(false);
                $table->timestamp('released_at');
                $table->timestamps();
            });
            
            // Insert current version data
            DB::table('system_versions')->insert([
                [
                    'version' => env('SELF_UPDATER_VERSION_INSTALLED', 'v2.0.1-test'),
                    'name' => 'Current Version',
                    'description' => 'Current system version.',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            
            Log::info('Created missing system_versions table');
        } else {
            Log::info('system_versions table already exists, skipping creation');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migrations needed as these are idempotent fixes
    }
}; 