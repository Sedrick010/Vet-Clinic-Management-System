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
        Schema::table('users', function (Blueprint $table) {
            // Add new staff fields
            $table->string('phone')->nullable()->after('email');
            $table->date('dob')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('dob');
            $table->string('employee_id')->nullable()->unique()->after('gender');
            $table->string('address')->nullable()->after('employee_id');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->date('hire_date')->nullable()->after('postal_code');
            $table->string('specialization')->nullable()->after('hire_date');
            $table->string('license_number')->nullable()->after('specialization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'dob',
                'gender',
                'employee_id',
                'address',
                'city',
                'state',
                'postal_code',
                'hire_date',
                'specialization',
                'license_number',
            ]);
        });
    }
}; 