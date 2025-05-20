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
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->string('payment_reference')->nullable()->comment('Reference number from payment');
            $table->string('payment_method')->nullable()->comment('Method of payment (bank transfer, online, etc.)');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->text('notes')->nullable()->comment('Additional information from clinic');
            $table->text('admin_notes')->nullable()->comment('Notes from admin');
            $table->date('payment_date')->nullable();
            $table->string('receipt_image_path')->nullable()->comment('Path to uploaded payment receipt');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
