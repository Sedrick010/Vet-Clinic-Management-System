<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class DirectDoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assume we're working with the AnimalLandia clinic database
        // Known to be: vet_clinic_animalandia_3jjjgmxj
        
        // First, validate the database exists
        $dbName = 'vet_clinic_animalandia_3jjjgmxj';
        
        // Check if the database exists
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);
        if (empty($exists)) {
            $this->command->error("Database {$dbName} does not exist!");
            return;
        }
        
        // Set up the tenant connection with the proper database name
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
        
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        $timestamp = time();
        $this->command->info("Adding doctors to the {$dbName} database");
        
        // Create doctors in the tenant database
        $doctors = [
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
        ];
        
        foreach ($doctors as $doctor) {
            DB::connection('tenant')->table('staff')->insert($doctor);
            $this->command->info("Added doctor: {$doctor['name']}");
        }
        
        $this->command->info("Successfully added doctors to the tenant database");
    }
} 