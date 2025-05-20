<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

class ListClinicClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:list-clients {subdomain : The clinic subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all clients and their pets in a clinic';

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
        
        $this->info("Listing clients for clinic: {$clinic->name}");
        
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
            
            // Check if deleted_at column exists in clients table
            $hasDeletedAt = false;
            try {
                $hasDeletedAt = DB::connection('tenant')
                    ->getSchemaBuilder()
                    ->hasColumn('clients', 'deleted_at');
            } catch (\Exception $e) {
                $this->warn("Error checking for deleted_at column: " . $e->getMessage());
            }
            
            // Get all clients
            $clientsQuery = DB::connection('tenant')
                ->table('clients')
                ->select('id', 'name', 'email', 'phone', 'created_at');
                
            if ($hasDeletedAt) {
                $clientsQuery->whereNull('deleted_at');
            }
            
            $clients = $clientsQuery->get();
                
            if ($clients->isEmpty()) {
                $this->warn("No clients found in this clinic.");
                return 0;
            }
            
            // Display clients in a table
            $headers = ['ID', 'Name', 'Email', 'Phone', 'Created At', 'Pets Count'];
            $rows = [];
            
            // Check if pets table has deleted_at column
            $hasPetsDeletedAt = false;
            try {
                $hasPetsDeletedAt = DB::connection('tenant')
                    ->getSchemaBuilder()
                    ->hasColumn('pets', 'deleted_at');
            } catch (\Exception $e) {
                $this->warn("Error checking for pets.deleted_at column: " . $e->getMessage());
            }
            
            // Get all pets data once
            $petsQuery = DB::connection('tenant')
                ->table('pets')
                ->select('id', 'name', 'species', 'breed', 'gender', 'birthdate', 'owner_id');
                
            if ($hasPetsDeletedAt) {
                $petsQuery->whereNull('deleted_at');
            }
            
            $allPets = $petsQuery->get()->groupBy('owner_id');
            
            foreach ($clients as $client) {
                $clientPets = $allPets->get($client->id, collect([]));
                
                $rows[] = [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->phone,
                    $client->created_at,
                    $clientPets->count()
                ];
            }
            
            $this->table($headers, $rows);
            
            // Display pets grouped by clients
            foreach ($clients as $client) {
                $clientPets = $allPets->get($client->id, collect([]));
                
                if ($clientPets->isEmpty()) {
                    $this->line("\n<fg=yellow>Client:</> <fg=green>{$client->name}</> (ID: {$client->id}) - <fg=red>No pets</>");
                    continue;
                }
                
                $this->line("\n<fg=yellow>Client:</> <fg=green>{$client->name}</> (ID: {$client->id})");
                $this->line("<fg=yellow>Pets:</> ({$clientPets->count()})");
                
                // Display pets for this client
                $petHeaders = ['ID', 'Name', 'Species', 'Breed', 'Gender', 'Birthdate'];
                $petRows = [];
                
                foreach ($clientPets as $pet) {
                    $birthdate = $pet->birthdate ?? 'Unknown';
                    
                    $petRows[] = [
                        $pet->id,
                        $pet->name,
                        $pet->species,
                        $pet->breed,
                        $pet->gender,
                        $birthdate
                    ];
                }
                
                $this->table($petHeaders, $petRows);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error listing clients: " . $e->getMessage());
            return 1;
        }
    }
}
