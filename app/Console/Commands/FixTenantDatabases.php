<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class FixTenantDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix {clinic_id? : The ID of the clinic to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix tenant database tables by creating essential tables directly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');

        if ($clinicId) {
            // Fix a specific clinic
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found!");
                return 1;
            }
            
            $this->info("Fixing database for {$clinic->name} (ID: {$clinic->id}, DB: {$clinic->database_name})");
            $this->fixTenantDatabase($clinic);
        } else {
            // Fix all approved clinics
            $this->info("Fixing databases for all clinics...");
            
            $clinics = Clinic::where('approval_status', 'approved')->get();
            $this->withProgressBar($clinics, function ($clinic) {
                $this->fixTenantDatabase($clinic, false);
            });
            
            $this->newLine(2);
            $this->info("All tenant databases fixed!");
        }

        return 0;
    }
    
    /**
     * Fix a tenant database by creating essential tables directly
     */
    private function fixTenantDatabase(Clinic $clinic, bool $showOutput = true)
    {
        try {
            // First check if the database exists
            $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$clinic->database_name]);
            
            if (empty($exists)) {
                if ($showOutput) {
                    $this->error("Database {$clinic->database_name} does not exist!");
                }
                return;
            }
            
            // Configure the tenant database connection
            Config::set('database.connections.tenant', [
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
            ]);
            
            // Clear any existing connections
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Disable foreign key checks
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Create staff table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('staff')) {
                if ($showOutput) {
                    $this->info("Creating staff table...");
                }
                
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `staff` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `phone` varchar(255) DEFAULT NULL,
                        `role` enum("admin","doctor","receptionist","assistant") DEFAULT "assistant",
                        `is_active` tinyint(1) DEFAULT 1,
                        `email_verified_at` timestamp NULL DEFAULT NULL,
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `staff_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                if ($showOutput) {
                    $this->info("Staff table created!");
                }
            } else if ($showOutput) {
                $this->info("Staff table already exists.");
            }
            
            // Create deleted_staff table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('deleted_staff')) {
                if ($showOutput) {
                    $this->info("Creating deleted_staff table...");
                }
                
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `deleted_staff` (
                        `id` bigint unsigned NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `phone` varchar(255) DEFAULT NULL,
                        `role` varchar(255) NOT NULL,
                        `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `deleted_by` bigint unsigned DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                if ($showOutput) {
                    $this->info("Deleted_staff table created!");
                }
            } else if ($showOutput) {
                $this->info("Deleted_staff table already exists.");
            }
            
            // Create clients table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('clients')) {
                if ($showOutput) {
                    $this->info("Creating clients table...");
                }
                
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `clients` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `phone` varchar(255) NOT NULL,
                        `address` varchar(255) DEFAULT NULL,
                        `city` varchar(255) DEFAULT NULL,
                        `state` varchar(255) DEFAULT NULL,
                        `postal_code` varchar(255) DEFAULT NULL,
                        `notes` text DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `clients_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                if ($showOutput) {
                    $this->info("Clients table created!");
                }
            } else if ($showOutput) {
                $this->info("Clients table already exists.");
            }
            
            // Create pets table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('pets')) {
                if ($showOutput) {
                    $this->info("Creating pets table...");
                }
                
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `pets` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `owner_id` bigint unsigned NOT NULL,
                        `species` varchar(255) NOT NULL,
                        `breed` varchar(255) DEFAULT NULL,
                        `birthdate` date DEFAULT NULL,
                        `gender` enum("male","female","unknown") NOT NULL,
                        `notes` text DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `pets_owner_id_foreign` (`owner_id`),
                        CONSTRAINT `pets_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `clients` (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                if ($showOutput) {
                    $this->info("Pets table created!");
                }
            } else if ($showOutput) {
                $this->info("Pets table already exists.");
            }
            
            // Create appointments table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('appointments')) {
                if ($showOutput) {
                    $this->info("Creating appointments table...");
                }
                
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `appointments` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `pet_id` bigint unsigned NOT NULL,
                        `client_id` bigint unsigned NOT NULL,
                        `staff_id` bigint unsigned NULL,
                        `start_time` datetime NOT NULL,
                        `end_time` datetime NOT NULL,
                        `status` varchar(255) NOT NULL,
                        `reason` text NOT NULL,
                        `notes` text DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `appointments_pet_id_foreign` (`pet_id`),
                        KEY `appointments_client_id_foreign` (`client_id`),
                        KEY `appointments_staff_id_foreign` (`staff_id`),
                        CONSTRAINT `appointments_pet_id_foreign` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`),
                        CONSTRAINT `appointments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
                        CONSTRAINT `appointments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                if ($showOutput) {
                    $this->info("Appointments table created!");
                }
            } else if ($showOutput) {
                $this->info("Appointments table already exists.");
            }
            
            // Re-enable foreign key checks
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
            
            if ($showOutput) {
                $this->info("Database fixed for {$clinic->name} successfully!");
            }
        } catch (\Exception $e) {
            if ($showOutput) {
                $this->error("Error fixing database for {$clinic->name}: " . $e->getMessage());
            }
            Log::error("Error fixing tenant database: " . $e->getMessage());
        }
    }
} 