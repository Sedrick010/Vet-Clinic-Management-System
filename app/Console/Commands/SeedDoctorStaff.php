<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\TenantDatabaseService;

class SeedDoctorStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-doctor {subdomain : The clinic subdomain} {--name=Doctor : The doctor name} {--email= : The doctor email} {--password= : The doctor password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a doctor staff member to a clinic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $name = $this->option('name');
        $email = $this->option('email') ?: 'doctor@example.com';
        $password = $this->option('password') ?: Str::random(10);
        
        // Find the clinic
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        if (!$clinic) {
            $this->error("Clinic with subdomain '$subdomain' not found!");
            return 1;
        }
        
        $this->info("Found clinic: {$clinic->name}");
        
        if (empty($clinic->database_name)) {
            $this->error("Clinic {$clinic->name} has no database!");
            return 1;
        }
        
        try {
            // Configure the tenant database connection
            config(['database.connections.tenant' => [
                'driver' => 'mysql',
                'url' => env('DATABASE_URL'),
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => $clinic->database_name,
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
            ]]);
            
            // Clear any existing connections
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Check if a doctor already exists
            $doctorExists = DB::connection('tenant')
                ->table('staff')
                ->where('role', 'doctor')
                ->exists();
                
            if ($doctorExists) {
                $this->warn("Doctor already exists in this clinic. Adding another one.");
            }
            
            // Check if staff with this email already exists
            $emailExists = DB::connection('tenant')
                ->table('staff')
                ->where('email', $email)
                ->exists();
                
            if ($emailExists) {
                $this->error("Staff with email {$email} already exists in this clinic.");
                return 1;
            }
            
            // Create the doctor staff
            $hashedPassword = Hash::make($password);
            
            $doctor = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'doctor',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $doctorId = DB::connection('tenant')
                ->table('staff')
                ->insertGetId($doctor);
                
            $this->info("Doctor created successfully!");
            $this->line("Name: {$name}");
            $this->line("Email: {$email}");
            $this->line("Password: {$password}");
            $this->line("Role: doctor");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error creating doctor: " . $e->getMessage());
            $this->error("Line: " . $e->getLine() . " in " . $e->getFile());
            return 1;
        }
    }
}
