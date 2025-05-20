<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;
use Database\Seeders\TemporaryPetAndVetSeeder;

class SeedClinicTemporaryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:seed-temp-data {clinic_id? : The ID of the clinic to seed data for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds temporary pet, client, and vet data for a specific clinic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        
        if ($clinicId) {
            // Find specific clinic
            $clinic = Clinic::find($clinicId);
            
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found.");
                return 1;
            }
            
            if ($clinic->approval_status !== 'approved') {
                $this->error("Clinic {$clinic->name} (ID: {$clinic->id}) is not approved. Only approved clinics can be seeded.");
                return 1;
            }
            
            $this->info("Found clinic: {$clinic->name} (ID: {$clinic->id})");
            $this->seedClinic($clinic);
        } else {
            // List all approved clinics and let user choose
            $clinics = Clinic::where('approval_status', 'approved')->get();
            
            if ($clinics->isEmpty()) {
                $this->error("No approved clinics found. Please approve a clinic first.");
                return 1;
            }
            
            $this->info("Available clinics:");
            $options = [];
            foreach ($clinics as $index => $clinic) {
                $options[$clinic->id] = "{$clinic->name} (ID: {$clinic->id}, Database: {$clinic->database_name})";
                $this->line(($index + 1) . ". {$options[$clinic->id]}");
            }
            
            $chosenId = $this->choice("Select a clinic to seed data for:", $options);
            $chosenClinic = $clinics->firstWhere('id', $chosenId);
            
            $this->seedClinic($chosenClinic);
        }
        
        return 0;
    }
    
    /**
     * Seed the given clinic with temporary data
     */
    private function seedClinic(Clinic $clinic)
    {
        $this->info("Seeding temporary data for clinic: {$clinic->name} (Database: {$clinic->database_name})");
        
        // Verify database exists
        $tenantDatabaseService = app(TenantDatabaseService::class);
        if (!$tenantDatabaseService->databaseExists($clinic->database_name)) {
            $this->error("Database {$clinic->database_name} does not exist!");
            return;
        }
        
        // Run the seeder
        try {
            $seeder = new TemporaryPetAndVetSeeder();
            $seeder->run($clinic);
            
            $this->info("Successfully seeded temporary data for clinic: {$clinic->name}");
        } catch (\Exception $e) {
            $this->error("Error seeding data: " . $e->getMessage());
        }
    }
} 