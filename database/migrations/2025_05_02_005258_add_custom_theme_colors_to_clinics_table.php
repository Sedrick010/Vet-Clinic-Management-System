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
        Schema::table('clinics', function (Blueprint $table) {
            $table->json('custom_theme_colors')->nullable()->after('theme');
            $table->string('theme_customization_level', 20)->default('none')->after('custom_theme_colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('custom_theme_colors');
            $table->dropColumn('theme_customization_level');
        });
    }
};
