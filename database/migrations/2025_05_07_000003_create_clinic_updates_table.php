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
        Schema::create('clinic_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('version');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'applied', 'dismissed'])->default('pending');
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_updates');
    }
};
