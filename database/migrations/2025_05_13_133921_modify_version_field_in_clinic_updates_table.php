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
        Schema::table('clinic_updates', function (Blueprint $table) {
            // Make version field nullable
            $table->string('version')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_updates', function (Blueprint $table) {
            // Return version field to required
            $table->string('version')->nullable(false)->change();
        });
    }
};
