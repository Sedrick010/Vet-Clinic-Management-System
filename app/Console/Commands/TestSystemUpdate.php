<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TestSystemUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-system-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the system update process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test clinic...');
        
        // First add a test system update
        $updateId = DB::table('system_updates')->insertGetId([
            'version' => 'v3.0.3',
            'name' => 'Test System Update',
            'description' => 'Test system update for new clinics',
            'changes' => 'Test changes',
            'is_critical' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info("Created test system update with ID: {$updateId}");
        
        // Create a new clinic
        $clinic = new Clinic();
        $clinic->name = 'TestClinic' . Str::random(4);
        $clinic->email = 'test' . Str::random(4) . '@example.com';
        $clinic->subdomain = 'test' . Str::lower(Str::random(4));
        $clinic->database_name = 'vet_clinic_test_' . Str::lower(Str::random(8));
        $clinic->save();
        
        $this->info("Created test clinic with ID: {$clinic->id}");
        
        // Set up the tenant database
        $this->info('Setting up tenant database...');
        app(TenantDatabaseService::class)->setupTenantDatabase($clinic);
        
        // Check if the clinic is registered for the update
        $updates = DB::table('clinic_updates')
            ->where('clinic_id', $clinic->id)
            ->get();
        
        $this->info('Clinic update registrations:');
        foreach ($updates as $update) {
            $this->info("- System Update ID: {$update->system_update_id}, Status: {$update->status}");
        }
        
        $this->info('Test completed!');
    }
} 