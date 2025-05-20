<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\Staff;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DebugAppointmentRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-appointment-relations {clinic_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug appointment relationship issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id') ?? 4; // Default to clinic ID 4
        
        $clinic = Clinic::find($clinicId);
        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found");
            return 1;
        }
        
        $this->info("Debugging appointment relations for clinic: {$clinic->name} (Database: {$clinic->database_name})");
        
        // Switch to tenant database
        $tenantDbService = new TenantDatabaseService();
        $tenantDbService->switchToTenant($clinic);
        
        // Check if staff table exists
        $this->info("\nChecking database tables...");
        if (Schema::connection('tenant')->hasTable('staff')) {
            $this->info("✓ 'staff' table exists");
            
            // Check columns in staff table
            $columns = Schema::connection('tenant')->getColumnListing('staff');
            $this->info("Columns in staff table: " . implode(', ', $columns));
            
            // Check if appointments table exists
            if (Schema::connection('tenant')->hasTable('appointments')) {
                $this->info("✓ 'appointments' table exists");
                
                // Check columns in appointments table
                $columns = Schema::connection('tenant')->getColumnListing('appointments');
                $this->info("Columns in appointments table: " . implode(', ', $columns));
                
                // Check for staff_id column
                if (in_array('staff_id', $columns)) {
                    $this->info("✓ 'staff_id' column exists in appointments table");
                } else {
                    $this->error("✗ 'staff_id' column is missing from appointments table");
                }
            } else {
                $this->error("✗ 'appointments' table does not exist");
            }
        } else {
            $this->error("✗ 'staff' table does not exist");
        }
        
        // Try to fetch staff data
        $this->info("\nChecking staff data...");
        $staffCount = Staff::count();
        $this->info("Staff count: {$staffCount}");
        if ($staffCount > 0) {
            $firstStaff = Staff::first();
            $this->info("First staff member: ID={$firstStaff->id}, Name={$firstStaff->name}");
        } else {
            $this->warn("No staff records found. This might cause relationship issues.");
        }
        
        // Try to fetch appointment data
        $this->info("\nChecking appointment data...");
        $apptCount = Appointment::count();
        $this->info("Appointment count: {$apptCount}");
        if ($apptCount > 0) {
            $firstAppt = Appointment::first();
            $this->info("First appointment: ID={$firstAppt->id}, client_name={$firstAppt->client_name}");
            
            // Check staff relationship
            try {
                $this->info("Trying to access staff relationship...");
                $staffId = $firstAppt->staff_id;
                $this->info("Staff ID in appointment: {$staffId}");
                
                if ($staffId) {
                    $relatedStaff = Staff::find($staffId);
                    if ($relatedStaff) {
                        $this->info("✓ Related staff found: {$relatedStaff->name}");
                    } else {
                        $this->error("✗ No staff found with ID {$staffId}");
                    }
                } else {
                    $this->warn("Staff ID is null or empty");
                }
                
                // Try to load the relationship
                $staffFromRelation = $firstAppt->staff;
                if ($staffFromRelation) {
                    $this->info("✓ Staff relationship loaded successfully: {$staffFromRelation->name}");
                } else {
                    $this->error("✗ Staff relationship returned null");
                }
            } catch (\Exception $e) {
                $this->error("Error accessing staff relationship: " . $e->getMessage());
            }
        } else {
            $this->warn("No appointment records found.");
        }
        
        // Refresh models to ensure correct state
        $this->info("\nChecking model instances...");
        $this->comment("Staff Model class: " . get_class(new Staff()));
        $this->comment("Appointment Model class: " . get_class(new Appointment()));
        
        return 0;
    }
} 