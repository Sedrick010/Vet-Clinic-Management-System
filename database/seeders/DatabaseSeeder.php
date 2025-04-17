<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use App\Services\TenantDatabaseService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Create test clinics with subscriptions
        $clinics = [
            [
                'name' => 'Pet Haven Clinic',
                'subdomain' => 'pet-haven',
                'email' => 'pethaven@example.com',
                'phone' => '123-456-7890',
                'address' => '123 Pet Street',
                'database_name' => 'vet_clinic_pet_haven',
                'subscription_status' => 'pending',
                'approval_status' => 'approved',
                'is_active' => true
            ],
            [
                'name' => 'Animal Care Center',
                'subdomain' => 'animal-care',
                'email' => 'animalcare@example.com',
                'phone' => '123-456-7891',
                'address' => '456 Animal Avenue',
                'database_name' => 'vet_clinic_animal_care',
                'subscription_status' => 'pending',
                'approval_status' => 'approved',
                'is_active' => true
            ],
            [
                'name' => 'Paws & Claws Clinic',
                'subdomain' => 'paws-claws',
                'email' => 'pawsclaws@example.com',
                'phone' => '123-456-7892',
                'address' => '789 Paws Road',
                'database_name' => 'vet_clinic_paws_claws',
                'subscription_status' => 'pending',
                'approval_status' => 'approved',
                'is_active' => true
            ]
        ];

        $tenantDatabaseService = app(TenantDatabaseService::class);

        foreach ($clinics as $clinicData) {
            $clinic = Clinic::create($clinicData);

            // Create and setup tenant database
            try {
                $tenantDatabaseService->createDatabase($clinic);
                $tenantDatabaseService->switchToTenant($clinic);

                // Create clinic owner/user
                $user = \DB::connection('tenant')->table('users')->insert([
                    'name' => 'Clinic Owner',
                    'email' => $clinic->email,
                    'password' => Hash::make('password'),
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Create test subscriptions
                \DB::connection('tenant')->table('subscriptions')->insert([
                    [
                        'user_id' => 1,
                        'clinic_id' => $clinic->id,
                        'plan_name' => 'BASIC PLAN',
                        'amount' => 29.00,
                        'status' => 'pending',
                        'approval_status' => 'pending',
                        'start_date' => now(),
                        'end_date' => now()->addMonth(),
                        'payment_method' => 'card',
                        'card_last_four' => '4242',
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'user_id' => 1,
                        'clinic_id' => $clinic->id,
                        'plan_name' => 'STANDARD PLAN',
                        'amount' => 49.00,
                        'status' => 'pending',
                        'approval_status' => 'pending',
                        'start_date' => now()->subDays(2),
                        'end_date' => now()->subDays(2)->addMonth(),
                        'payment_method' => 'card',
                        'card_last_four' => '4242',
                        'created_at' => now()->subDays(2),
                        'updated_at' => now()->subDays(2)
                    ]
                ]);

                // Switch back to main database
                $tenantDatabaseService->switchToMain();

            } catch (\Exception $e) {
                \Log::error('Error seeding clinic: ' . $e->getMessage());
                continue;
            }
        }
    }
}
