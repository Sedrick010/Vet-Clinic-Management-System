<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseService;
use App\Models\Clinic;
use Faker\Factory as FakerFactory;

class ClientLimitTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the clinic ID from the command line or use a default value
        $clinicId = $this->command->ask('Enter the clinic ID to seed clients:', 1);
        
        // Find the clinic
        $clinic = Clinic::find($clinicId);
        
        if (!$clinic) {
            $this->command->error("Clinic with ID {$clinicId} not found!");
            return;
        }
        
        $this->command->info("Seeding clients for clinic: {$clinic->name}");
        
        // Switch to the tenant database
        $tenantDatabaseService = app(TenantDatabaseService::class);
        $tenantDatabaseService->switchToTenant($clinic);
        
        // Check if the clients table exists
        if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('clients')) {
            $this->command->error('The clients table does not exist in the tenant database!');
            return;
        }
        
        // Get current client count
        $currentCount = DB::connection('tenant')->table('clients')->count();
        $this->command->info("Current client count: {$currentCount}");
        
        // Calculate how many clients to add to reach 99 total
        $clientsToAdd = 99 - $currentCount;
        
        if ($clientsToAdd <= 0) {
            $this->command->info("You already have {$currentCount} clients. No need to add more for testing the limit.");
            return;
        }
        
        $this->command->info("Adding {$clientsToAdd} clients...");
        
        // Create a Faker instance for generating realistic test data
        $faker = FakerFactory::create();
        
        // Prepare batch insert data
        $clients = [];
        for ($i = 1; $i <= $clientsToAdd; $i++) {
            $clients[] = [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'postal_code' => $faker->postcode,
                'notes' => $faker->optional(0.3)->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in batches of 10 to avoid memory issues
            if ($i % 10 === 0 || $i === $clientsToAdd) {
                DB::connection('tenant')->table('clients')->insert($clients);
                $clients = [];
                $this->command->info("Added clients batch " . ceil($i / 10) . " of " . ceil($clientsToAdd / 10));
            }
        }
        
        // Get final count
        $finalCount = DB::connection('tenant')->table('clients')->count();
        $this->command->info("Seeding complete! Final client count: {$finalCount}");
    }
} 