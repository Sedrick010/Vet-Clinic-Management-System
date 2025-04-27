<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\Client;
use App\Models\Pet;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AddTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:add-test-data {subdomain : The clinic subdomain} {--force : Force add data even if records exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add test data (client and pet) to a clinic database';

    protected $tenantDatabaseService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        parent::__construct();
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $force = $this->option('force');
        
        $this->info("Looking for clinic with subdomain: $subdomain");
        
        // Find the clinic
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        if (!$clinic) {
            $this->error("Clinic with subdomain '$subdomain' not found!");
            return 1;
        }
        
        $this->info("Found clinic: {$clinic->name}");
        
        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Check if we already have clients
        $clientsCount = DB::connection('tenant')->table('clients')->count();
        
        if ($clientsCount > 0 && !$force) {
            $this->warn("Clients already exist in this clinic database. Use --force to add test data anyway.");
            return 1;
        }
        
        $this->info("Adding test data to clinic: {$clinic->name}");
        
        try {
            DB::connection('tenant')->beginTransaction();
            
            // Create test client
            $client = new Client();
            $client->name = "Test Client";
            $client->email = "testclient@example.com";
            $client->phone = "555-1234";
            $client->address = "123 Test Street";
            $client->city = "Test City";
            $client->state = "TS";
            $client->postal_code = "12345";
            $client->notes = "This is a test client created by the add-test-data command.";
            $client->save();
            
            $this->info("Created client: {$client->name} with ID: {$client->id}");
            
            // Create test pet
            $pet = new Pet();
            $pet->owner_id = $client->id;
            $pet->name = "Test Pet";
            $pet->species = "Dog";
            $pet->breed = "Mixed";
            $pet->gender = "male";
            $pet->birthdate = now()->subYears(3);
            $pet->notes = "This is a test pet created by the add-test-data command.";
            $pet->save();
            
            $this->info("Created pet: {$pet->name} with ID: {$pet->id}");
            
            // Verify the relationship
            $this->info("Verifying client-pet relationship...");
            
            // Query using relationship
            $clientPets = $client->pets()->get();
            $this->info("Client's pets count via relationship: " . $clientPets->count());
            
            // Direct query
            $petsCount = DB::connection('tenant')->table('pets')
                ->where('owner_id', $client->id)
                ->count();
            $this->info("Client's pets count via direct query: $petsCount");
            
            // Check if pet is soft-deleted
            if (Schema::connection('tenant')->hasColumn('pets', 'deleted_at')) {
                $this->info("Pet has soft delete column. Checking if it's deleted...");
                $deletedPet = DB::connection('tenant')->table('pets')
                    ->where('id', $pet->id)
                    ->whereNotNull('deleted_at')
                    ->exists();
                    
                $this->info("Pet is " . ($deletedPet ? "soft-deleted" : "not deleted"));
            }
            
            DB::connection('tenant')->commit();
            
            $this->info("Test data added successfully!");
            return 0;
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->error("Error adding test data: " . $e->getMessage());
            $this->line("File: " . $e->getFile() . ":" . $e->getLine());
            return 1;
        }
    }
}
