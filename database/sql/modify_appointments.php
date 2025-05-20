<?php

use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;

// Bootstrap the application
require __DIR__.'/../../bootstrap/app.php';

$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the clinic
$clinic = Clinic::find(4);

if (!$clinic) {
    echo "Clinic not found!\n";
    exit(1);
}

echo "Modifying appointments table for clinic: {$clinic->name} (Database: {$clinic->database_name})\n";

// Switch to tenant database
$tenantDbService = new TenantDatabaseService();
$tenantDbService->switchToTenant($clinic);

try {
    // Add client_name column if it doesn't exist
    if (!Schema::connection('tenant')->hasColumn('appointments', 'client_name')) {
        echo "Adding client_name column...\n";
        DB::connection('tenant')->statement('ALTER TABLE appointments ADD COLUMN client_name VARCHAR(255) AFTER id');
        echo "client_name column added successfully!\n";
    } else {
        echo "client_name column already exists.\n";
    }

    // Disable foreign key checks
    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 0');
    
    // Remove foreign keys if they exist (try-catch in case they don't)
    try {
        echo "Dropping foreign key constraints...\n";
        DB::connection('tenant')->statement('ALTER TABLE appointments DROP FOREIGN KEY appointments_pet_id_foreign');
        DB::connection('tenant')->statement('ALTER TABLE appointments DROP FOREIGN KEY appointments_client_id_foreign');
        echo "Foreign key constraints dropped successfully!\n";
    } catch (\Exception $e) {
        echo "Note: Foreign key constraints may not exist or could not be dropped: {$e->getMessage()}\n";
    }
    
    // Drop columns if they exist
    if (Schema::connection('tenant')->hasColumn('appointments', 'pet_id') || 
        Schema::connection('tenant')->hasColumn('appointments', 'client_id')) {
        echo "Dropping pet_id and client_id columns...\n";
        DB::connection('tenant')->statement('ALTER TABLE appointments DROP COLUMN pet_id, DROP COLUMN client_id');
        echo "Columns dropped successfully!\n";
    } else {
        echo "pet_id and/or client_id columns already removed.\n";
    }
    
    // Re-enable foreign key checks
    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "Successfully modified appointments table!\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
} 