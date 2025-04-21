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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan'); // basic, standard, premium
            $table->integer('duration'); // in months
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->text('payment_details')->nullable();
            $table->string('payment_proof')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->text('notes')->nullable();
            $table->string('status'); // pending, active, expired, cancelled, rejected
            $table->boolean('auto_renew')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
