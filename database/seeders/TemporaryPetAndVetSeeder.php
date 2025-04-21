<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pet;
use Illuminate\Support\Facades\Hash;

class TemporaryPetAndVetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = time();
        
        // Create some pet owners
        $owner1 = User::create([
            'name' => 'John Smith',
            'email' => "john.smith.{$timestamp}@example.com",
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '123-456-7890'
        ]);

        $owner2 = User::create([
            'name' => 'Mary Johnson',
            'email' => "mary.johnson.{$timestamp}@example.com",
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '234-567-8901'
        ]);

        // Create some pets
        Pet::create([
            'name' => 'Max',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'owner_id' => $owner1->id,
            'gender' => 'male'
        ]);

        Pet::create([
            'name' => 'Luna',
            'species' => 'Cat',
            'breed' => 'Persian',
            'owner_id' => $owner1->id,
            'gender' => 'female'
        ]);

        Pet::create([
            'name' => 'Rocky',
            'species' => 'Dog',
            'breed' => 'German Shepherd',
            'owner_id' => $owner2->id,
            'gender' => 'male'
        ]);

        Pet::create([
            'name' => 'Milo',
            'species' => 'Cat',
            'breed' => 'Siamese',
            'owner_id' => $owner2->id,
            'gender' => 'male'
        ]);

        // Create some veterinarians
        User::create([
            'name' => 'Dr. Sarah Wilson',
            'email' => "dr.wilson.{$timestamp}@vetclinic.com",
            'password' => Hash::make('password123'),
            'role' => 'veterinarian',
            'phone' => '345-678-9012'
        ]);

        User::create([
            'name' => 'Dr. Michael Brown',
            'email' => "dr.brown.{$timestamp}@vetclinic.com",
            'password' => Hash::make('password123'),
            'role' => 'veterinarian',
            'phone' => '456-789-0123'
        ]);

        User::create([
            'name' => 'Dr. Emily Davis',
            'email' => "dr.davis.{$timestamp}@vetclinic.com",
            'password' => Hash::make('password123'),
            'role' => 'veterinarian',
            'phone' => '567-890-1234'
        ]);
    }
} 