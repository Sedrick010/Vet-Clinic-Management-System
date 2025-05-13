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
                'version' => env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.8'),
                'name' => 'Current Version',
                'description' => 'Current system version.',
                'is_current' => true,
                'released_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_versions');
    }
};
