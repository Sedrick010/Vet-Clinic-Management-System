<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseService;
use App\Models\Clinic;

class InventoryLimitTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the clinic ID from the command line or use a default value
        $clinicId = $this->command->ask('Enter the clinic ID to seed inventory items:', 1);
        
        // Find the clinic
        $clinic = Clinic::find($clinicId);
        
        if (!$clinic) {
            $this->command->error("Clinic with ID {$clinicId} not found!");
            return;
        }
        
        $this->command->info("Seeding inventory items for clinic: {$clinic->name}");
        
        // Switch to the tenant database
        $tenantDatabaseService = app(TenantDatabaseService::class);
        $tenantDatabaseService->switchToTenant($clinic);
        
        // Check if the inventory_items table exists
        if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('inventory_items')) {
            $this->command->error('The inventory_items table does not exist in the tenant database!');
            return;
        }
        
        // Get current inventory count
        $currentCount = DB::connection('tenant')->table('inventory_items')->count();
        $this->command->info("Current inventory count: {$currentCount}");
        
        // Calculate how many items to add to reach 99 total
        $itemsToAdd = 99 - $currentCount;
        
        if ($itemsToAdd <= 0) {
            $this->command->info("You already have {$currentCount} inventory items. No need to add more for testing the limit.");
            return;
        }
        
        $this->command->info("Adding {$itemsToAdd} inventory items...");
        
        // Prepare batch insert data
        $items = [];
        for ($i = 1; $i <= $itemsToAdd; $i++) {
            $nameIndex = $currentCount + $i;
            $items[] = [
                'name' => "Test Item {$nameIndex}",
                'sku' => "TEST-" . str_pad($nameIndex, 5, '0', STR_PAD_LEFT),
                'description' => "This is a test item created to test subscription limits",
                'category' => 'Test',
                'stock_quantity' => rand(1, 100),
                'cost_price' => rand(10, 50),
                'selling_price' => rand(50, 100),
                'reorder_level' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in batches of 10 to avoid memory issues
            if ($i % 10 === 0 || $i === $itemsToAdd) {
                DB::connection('tenant')->table('inventory_items')->insert($items);
                $items = [];
                $this->command->info("Added items batch " . ceil($i / 10) . " of " . ceil($itemsToAdd / 10));
            }
        }
        
        // Get final count
        $finalCount = DB::connection('tenant')->table('inventory_items')->count();
        $this->command->info("Seeding complete! Final inventory count: {$finalCount}");
    }
} 