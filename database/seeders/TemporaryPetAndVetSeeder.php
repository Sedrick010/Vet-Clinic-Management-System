<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;

class TemporaryPetAndVetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @param Clinic|null $targetClinic The specific clinic to seed data for
     */
    public function run(?Clinic $targetClinic = null): void
    {
        // Get a clinic to seed data for
        $clinic = $targetClinic;
        
        if (!$clinic) {
            $clinic = Clinic::where('approval_status', 'approved')->first();
            
            if (!$clinic) {
                echo "No approved clinic found. Please create and approve a clinic first.\n";
                return;
            }
        }
        
        echo "Seeding temporary data for clinic: {$clinic->name} (Database: {$clinic->database_name})\n";
        
        // Connect to the tenant database
        $tenantDatabaseService = app(TenantDatabaseService::class);
        $tenantDatabaseService->switchToTenant($clinic);
        
        $timestamp = time();
        
        // Create some clients (pet owners) in the tenant database
        $client1Id = DB::connection('tenant')->table('clients')->insertGetId([
            'name' => 'John Smith',
            'email' => "john.smith.{$timestamp}@example.com",
            'phone' => '123-456-7890',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'notes' => 'Regular client with multiple pets',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $client2Id = DB::connection('tenant')->table('clients')->insertGetId([
            'name' => 'Mary Johnson',
            'email' => "mary.johnson.{$timestamp}@example.com",
            'phone' => '234-567-8901',
            'address' => '456 Oak Ave',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62702',
            'notes' => 'New client, first visit',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create some pets in the tenant database
        DB::connection('tenant')->table('pets')->insert([
            [
                'name' => 'Max',
                'species' => 'Dog',
                'breed' => 'Golden Retriever',
                'owner_id' => $client1Id,
                'gender' => 'male',
                'birthdate' => now()->subYears(3),
                'notes' => 'Very friendly, no health issues',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Luna',
                'species' => 'Cat',
                'breed' => 'Persian',
                'owner_id' => $client1Id,
                'gender' => 'female',
                'birthdate' => now()->subYears(2),
                'notes' => 'Picky eater, long hair requires regular grooming',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Rocky',
                'species' => 'Dog',
                'breed' => 'German Shepherd',
                'owner_id' => $client2Id,
                'gender' => 'male',
                'birthdate' => now()->subYears(4),
                'notes' => 'Well-trained guard dog, hip issues',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Milo',
                'species' => 'Cat',
                'breed' => 'Siamese',
                'owner_id' => $client2Id,
                'gender' => 'male',
                'birthdate' => now()->subYears(1),
                'notes' => 'Very vocal, follows owner everywhere',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Create some staff (veterinarians) if they don't exist already
        if (DB::connection('tenant')->table('staff')->count() === 0) {
            DB::connection('tenant')->table('staff')->insert([
                [
                    'name' => 'Dr. Sarah Wilson',
                    'email' => "dr.wilson.{$timestamp}@vetclinic.com",
                    'password' => Hash::make('password123'),
                    'role' => 'doctor',
                    'phone' => '345-678-9012',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Dr. Michael Brown',
                    'email' => "dr.brown.{$timestamp}@vetclinic.com",
                    'password' => Hash::make('password123'),
                    'role' => 'doctor',
                    'phone' => '456-789-0123',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Dr. Emily Davis',
                    'email' => "dr.davis.{$timestamp}@vetclinic.com",
                    'password' => Hash::make('password123'),
                    'role' => 'doctor',
                    'phone' => '567-890-1234',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
        
        // Switch back to the main database
        $tenantDatabaseService->switchToMain();
        
        echo "Successfully seeded temporary data for clinic: {$clinic->name}\n";
    }
} 