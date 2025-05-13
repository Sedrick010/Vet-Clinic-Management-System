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
        Log::info('Running fix_system_updates_schema migration');
        
        // First, disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Handle system_updates table
            if (!Schema::hasTable('system_updates')) {
                Log::info('Creating system_updates table');
                Schema::create('system_updates', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description');
                    $table->text('changes');
                    $table->text('features')->nullable();
                    $table->text('bug_fixes')->nullable();
                    $table->boolean('is_critical')->default(false);
                    $table->boolean('is_security')->default(false);
                    $table->boolean('is_mandatory')->default(false);
                    $table->timestamp('available_from')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
            }
            
            // Handle system_versions table
            if (!Schema::hasTable('system_versions')) {
                Log::info('Creating system_versions table');
                Schema::create('system_versions', function (Blueprint $table) {
                    $table->id();
                    $table->string('version');
                    $table->string('name');
                    $table->text('description')->nullable();
                    $table->boolean('is_current')->default(false);
                    $table->timestamp('released_at')->nullable();
                    $table->timestamps();
                });
                
                // Insert initial version
                $currentVersion = config('self-update.version_installed', '1.0.0');
                DB::table('system_versions')->insert([
                    'version' => $currentVersion,
                    'name' => 'Initial Release',
                    'description' => 'The initial release of the VetClinic system',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Check if we have at least one version record
                $versionsCount = DB::table('system_versions')->count();
                if ($versionsCount === 0) {
                    // Insert initial version if there are no versions in the table
                    $currentVersion = config('self-update.version_installed', '1.0.0');
                    DB::table('system_versions')->insert([
                        'version' => $currentVersion,
                        'name' => 'Initial Release',
                        'description' => 'The initial release of the VetClinic system',
                        'is_current' => true,
                        'released_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Handle clinic_updates table
            if (!Schema::hasTable('clinic_updates')) {
                Log::info('Creating clinic_updates table');
                Schema::create('clinic_updates', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
                    $table->foreignId('system_update_id')->nullable();
                    $table->boolean('is_applied')->default(false);
                    $table->boolean('is_dismissed')->default(false);
                    $table->timestamp('applied_at')->nullable();
                    $table->timestamp('dismissed_at')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            } else {
                // Check if we need to update the schema of clinic_updates
                if (!Schema::hasColumn('clinic_updates', 'system_update_id')) {
                    Schema::table('clinic_updates', function (Blueprint $table) {
                        $table->foreignId('system_update_id')->nullable()->after('clinic_id');
                        
                        // Only add these columns if they don't exist
                        if (!Schema::hasColumn('clinic_updates', 'is_applied')) {
                            $table->boolean('is_applied')->default(false)->after('system_update_id');
                        }
                        
                        if (!Schema::hasColumn('clinic_updates', 'is_dismissed')) {
                            $table->boolean('is_dismissed')->default(false)->after('is_applied');
                        }
                        
                        // Try to drop status column if it exists
                        if (Schema::hasColumn('clinic_updates', 'status')) {
                            $table->dropColumn('status');
                        }
                    });
                }
            }
            
            Log::info('Completed fix_system_updates_schema migration successfully');
        } catch (\Exception $e) {
            Log::error('Error in fix_system_updates_schema migration: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive actions in the down method to avoid data loss
    }
}; 