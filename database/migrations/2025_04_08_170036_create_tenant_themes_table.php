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
        Schema::create('tenant_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('primary_color')->default('#5e72e4');
            $table->string('secondary_color')->default('#f7fafc');
            $table->string('accent_color')->default('#11cdef');
            $table->string('text_color')->default('#32325d');
            $table->string('background_color')->default('#ffffff');
            $table->boolean('is_dark_mode')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_themes');
    }
};
