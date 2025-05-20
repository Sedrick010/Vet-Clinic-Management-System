<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class FixAppointmentsTableColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-appointments-client-name {subdomain? : Specific clinic subdomain to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add client_name column to appointments table for all or specific clinic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        
        if ($subdomain) {
            // Fix a specific clinic
            $clinic = Clinic::where('subdomain', $subdomain)->first();
            
            if (!$clinic) {
                $this->error("Clinic with subdomain '{$subdomain}' not found!");
                return 1;
            }
            
            $this->fixClinic($clinic);
        } else {
            // Fix all approved clinics
            $clinics = Clinic::where('approval_status', 'approved')->get();
            
            if ($clinics->isEmpty()) {
                $this->error("No approved clinics found.");
                return 1;
            }
            
            foreach ($clinics as $clinic) {
                $this->fixClinic($clinic);
            }
        }
        
        return 0;
    }
    
    /**
     * Fix the appointments table for a specific clinic
     */
    private function fixClinic(Clinic $clinic)
    {
        $this->info("Checking appointments table for clinic: {$clinic->name}");
        
        if (empty($clinic->database_name)) {
            $this->error("Clinic {$clinic->name} has no database!");
            return;
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
            
            // Check if appointments table exists
            if (!Schema::connection('tenant')->hasTable('appointments')) {
                $this->warn("Appointments table doesn't exist in {$clinic->name}'s database.");
                return;
            }
            
            // Check if client_name column exists
            $hasClientName = Schema::connection('tenant')->hasColumn('appointments', 'client_name');
            
            if ($hasClientName) {
                $this->info("client_name column already exists in appointments table for {$clinic->name}.");
                return;
            }
            
            // Add the client_name column
            DB::connection('tenant')->statement('ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) AFTER id');
            
            // Update existing appointments with client names
            $this->info("Updating existing appointments with client names...");
            
            $appointments = DB::connection('tenant')
                ->table('appointments')
                ->whereNull('client_name')
                ->whereNotNull('client_id')
                ->get();
                
            foreach ($appointments as $appointment) {
                if (isset($appointment->client_id)) {
                    $client = DB::connection('tenant')
                        ->table('clients')
                        ->where('id', $appointment->client_id)
                        ->first();
                        
                    if ($client) {
                        DB::connection('tenant')
                            ->table('appointments')
                            ->where('id', $appointment->id)
                            ->update(['client_name' => $client->name]);
                    }
                }
            }
            
            $this->info("Successfully added client_name column to appointments table for {$clinic->name}!");
            
            // Log the successful fix
            Log::info("Added client_name column to appointments table for clinic {$clinic->name}");
            
        } catch (\Exception $e) {
            $this->error("Error fixing appointments table for {$clinic->name}: " . $e->getMessage());
            Log::error("Error fixing appointments table for clinic {$clinic->name}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
