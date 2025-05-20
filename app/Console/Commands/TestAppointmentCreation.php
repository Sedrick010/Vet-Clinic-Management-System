<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TestAppointmentCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test-appointment-creation {subdomain : The clinic subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test appointment creation in a clinic database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        
        // Find the clinic
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        if (!$clinic) {
            $this->error("Clinic with subdomain '$subdomain' not found!");
            return 1;
        }
        
        $this->info("Testing appointment creation for clinic: {$clinic->name}");
        
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
            
            // Verify appointments table structure
            $this->info("Verifying appointments table structure...");
            $columns = Schema::connection('tenant')->getColumnListing('appointments');
            $this->info("Available columns: " . implode(', ', $columns));
            
            // Check if the client_name column exists
            if (!in_array('client_name', $columns)) {
                $this->error("client_name column is missing in appointments table!");
                return 1;
            }
            
            // Get a client and pet for testing
            $client = DB::connection('tenant')->table('clients')->first();
            
            if (!$client) {
                $this->error("No clients found in this clinic.");
                return 1;
            }
            
            $pet = DB::connection('tenant')
                ->table('pets')
                ->where('owner_id', $client->id)
                ->first();
                
            if (!$pet) {
                $this->error("No pets found for client {$client->name}.");
                return 1;
            }
            
            // Get a staff member for testing
            $staff = DB::connection('tenant')->table('staff')->first();
            
            if (!$staff) {
                $this->error("No staff found in this clinic.");
                return 1;
            }
            
            // Create a test appointment
            $this->info("Creating test appointment...");
            
            $startTime = Carbon::now()->addHours(1);
            $endTime = Carbon::now()->addHours(2);
            
            $appointmentData = [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'pet_id' => $pet->id,
                'staff_id' => $staff->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'reason' => 'Command test appointment',
                'notes' => 'Created by test command',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $appointmentId = DB::connection('tenant')
                ->table('appointments')
                ->insertGetId($appointmentData);
                
            $this->info("Test appointment created successfully with ID: {$appointmentId}");
            
            // Retrieve the appointment to verify
            $appointment = DB::connection('tenant')
                ->table('appointments')
                ->where('id', $appointmentId)
                ->first();
                
            $this->info("Appointment details:");
            $this->line("Client: {$appointment->client_name}");
            $this->line("Start time: {$appointment->start_time}");
            $this->line("End time: {$appointment->end_time}");
            $this->line("Reason: {$appointment->reason}");
            $this->line("Status: {$appointment->status}");
            
            $this->info("Test completed successfully!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error testing appointment creation: " . $e->getMessage());
            $this->error("Line: " . $e->getLine() . " in " . $e->getFile());
            return 1;
        }
    }
}
