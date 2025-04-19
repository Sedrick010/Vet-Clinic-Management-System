<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('staff');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert a default user
        DB::table('users')->insert([
            'name' => 'Default User',
            'email' => 'default@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}; 