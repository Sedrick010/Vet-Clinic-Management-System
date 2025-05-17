<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create system admin
        $this->call(AdminUserSeeder::class);
        
        // Seed support tickets for testing
        $this->call(SupportTicketSeeder::class);
    }
}
