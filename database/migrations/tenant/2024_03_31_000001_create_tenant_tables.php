<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the tenant database.
     */
    public function up(): void
    {
        // Clients (pet owners) - Create this first as Pets depends on it
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pets - After clients are created
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('clients');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female', 'unknown']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('staff_id')->nullable()->constrained('users');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status'); // scheduled, confirmed, completed, cancelled, no-show
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Medical Records
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained();
            $table->foreignId('appointment_id')->nullable()->constrained();
            $table->foreignId('staff_id')->constrained('users');
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->date('record_date');
            $table->timestamps();
        });

        // Inventory Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->string('category');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock_quantity');
            $table->integer('reorder_level');
            $table->timestamps();
        });

        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('appointment_id')->nullable()->constrained();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status'); // draft, sent, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->foreignId('client_id')->constrained('clients');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // cash, card, bank transfer, etc
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('clients');
    }
}; 