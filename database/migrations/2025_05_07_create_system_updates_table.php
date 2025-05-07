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
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('system_versions')->onDelete('cascade');
            $table->string('summary');
            $table->text('changes')->nullable();
            $table->text('features')->nullable();
            $table->text('fixes')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_security')->default(false);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
}; 