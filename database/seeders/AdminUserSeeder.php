<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (User::where('email', 'admin@vetclinic.com')->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        // Create admin user
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@vetclinic.com',
            'password' => Hash::make('admin123'), // Change this to a secure password in production
            'clinic_id' => null, // Admin is not associated with any specific clinic
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created successfully.');
    }
}
