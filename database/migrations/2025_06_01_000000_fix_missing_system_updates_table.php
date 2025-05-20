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
        // Check if system_updates table exists, if not create it
        if (!Schema::hasTable('system_updates')) {
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

        // Check if system_versions table exists, if not create it
        if (!Schema::hasTable('system_versions')) {
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
            DB::table('system_versions')->insert([
                'version' => '1.0.0',
                'name' => 'Initial Release',
                'description' => 'The initial release of the VetClinic system',
                'is_current' => true,
                'released_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Check if clinic_updates table has the correct schema and columns
        if (Schema::hasTable('clinic_updates')) {
            if (!Schema::hasColumn('clinic_updates', 'system_update_id')) {
                Schema::table('clinic_updates', function (Blueprint $table) {
                    $table->foreignId('system_update_id')->nullable()->after('clinic_id');
                    $table->boolean('is_applied')->default(false)->after('system_update_id');
                    $table->boolean('is_dismissed')->default(false)->after('is_applied');
                    $table->dropColumn('status');
                });
            }
        } else {
            // Create clinic_updates table to track clinic-specific update status
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive actions in the down method to avoid data loss
    }
}; 