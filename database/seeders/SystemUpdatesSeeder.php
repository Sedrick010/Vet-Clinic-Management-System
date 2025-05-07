<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemUpdatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing updates
        DB::table('clinic_updates')->truncate();
        DB::table('system_updates')->truncate();
        DB::table('system_versions')->truncate();

        // Define current version update
        $updates = [
            [
                'version' => config('self-update.version_installed'),
                'name' => 'Current System Version',
                'description' => 'Initial system version with self-update capabilities.',
                'changes' => 'Initial version with update system',
                'features' => "Update notifications\nAutomatic update checking\nUpdate history tracking",
                'bug_fixes' => null,
                'is_critical' => false,
                'is_security' => false,
                'is_mandatory' => false,
                'available_from' => Carbon::now(),
                'expires_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Insert the update
        foreach ($updates as $update) {
            $updateId = DB::table('system_updates')->insertGetId($update);
            
            // Create version record
            DB::table('system_versions')->insert([
                'version' => $update['version'],
                'name' => $update['name'],
                'description' => $update['description'],
                'is_current' => true,
                'released_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            // Create clinic update records for all clinics
            $clinics = DB::table('clinics')->get();
            foreach ($clinics as $clinic) {
                DB::table('clinic_updates')->insert([
                    'clinic_id' => $clinic->id,
                    'system_update_id' => $updateId,
                    'is_applied' => true,
                    'is_dismissed' => false,
                    'applied_at' => Carbon::now(),
                    'dismissed_at' => null,
                    'notes' => 'Initial version',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
    }
} 