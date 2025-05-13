<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinic_updates', function (Blueprint $table) {
            // Add system_update_id column
            $table->unsignedBigInteger('system_update_id')->nullable()->after('clinic_id');
            
            // Add foreign key constraint to system_versions table
            $table->foreign('system_update_id')
                ->references('id')
                ->on('system_versions')
                ->onDelete('cascade');
        });
        
        // Update existing records to map from version to system_update_id
        $clinicUpdates = DB::table('clinic_updates')->get();
        foreach ($clinicUpdates as $update) {
            $systemVersion = DB::table('system_versions')
                ->where('version', $update->version)
                ->first();
                
            if ($systemVersion) {
                DB::table('clinic_updates')
                    ->where('id', $update->id)
                    ->update(['system_update_id' => $systemVersion->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_updates', function (Blueprint $table) {
            $table->dropForeign(['system_update_id']);
            $table->dropColumn('system_update_id');
        });
    }
};
