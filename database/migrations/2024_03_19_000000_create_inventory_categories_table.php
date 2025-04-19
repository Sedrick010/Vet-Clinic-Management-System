<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert default categories
        DB::table('inventory_categories')->insert([
            ['name' => 'Medication', 'description' => 'Veterinary medications and drugs'],
            ['name' => 'Equipment', 'description' => 'Medical equipment and tools'],
            ['name' => 'Supplies', 'description' => 'Medical supplies and consumables'],
            ['name' => 'Food', 'description' => 'Pet food and dietary supplements'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('inventory_categories');
    }
}; 