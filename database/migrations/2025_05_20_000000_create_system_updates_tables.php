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
        // Create system_versions table to track the current system version
        Schema::create('system_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        // Create system_updates table to track available updates
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

        // Create clinic_updates table to track clinic-specific update status
        Schema::create('clinic_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('system_update_id')->constrained('system_updates')->onDelete('cascade');
            $table->boolean('is_applied')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->text('notes')->nullable();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_updates');
        Schema::dropIfExists('system_updates');
        Schema::dropIfExists('system_versions');
    }
}; 