<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PDO;
use Exception;
use Illuminate\Support\Facades\Schema;

class TenantDatabaseService
{
    /**
     * Create a new database for a tenant.
     *
     * @param string $clinicName
     * @return string
     * @throws \Exception if database creation fails
     */
    public function createDatabase(string $clinicName): string
    {
        // Generate a unique database name
        $databaseName = 'vet_clinic_' . Str::slug($clinicName) . '_' . Str::lower(Str::random(8));
        
        try {
            Log::info('Attempting to create tenant database', [
                'database_name' => $databaseName,
                'clinic_name' => $clinicName
            ]);
            
            // Check if the database already exists
            if ($this->databaseExists($databaseName)) {
                Log::warning('Attempted to create database that already exists', [
                    'database_name' => $databaseName,
                    'clinic_name' => $clinicName
                ]);
                
                // Return the existing database name since it already exists
                return $databaseName;
            }
            
            // Create the database with proper error handling
            try {
                // Get the current MySQL user's privileges
                $privileges = DB::select("SHOW GRANTS FOR CURRENT_USER()");
                Log::info('Current user privileges:', ['privileges' => $privileges]);
                
                // Use direct SQL with proper quoting to avoid SQL injection
                $query = "CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $databaseName) . "`";
                Log::debug('Creating tenant database with query', ['query' => $query]);
                
                DB::statement($query);
                
                // Verify the database was created
                if (!$this->databaseExists($databaseName)) {
                    throw new \Exception("Database creation query executed but database {$databaseName} doesn't exist");
                }
                
                Log::info('Successfully created tenant database', [
                    'database_name' => $databaseName,
                    'clinic_name' => $clinicName
                ]);
                
                return $databaseName;
            } catch (\Exception $e) {
                Log::error('Database creation SQL error: ' . $e->getMessage(), [
                    'database_name' => $databaseName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error creating tenant database: ' . $e->getMessage(), [
                'database_name' => $databaseName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // For development only - fallback to a simulated database name
            if (app()->environment('local')) {
                Log::warning('Using fallback database name in development environment', [
                    'clinic_name' => $clinicName
                ]);
                return config('database.connections.mysql.database');
            }
            
            // In production, rethrow the exception
            throw new \Exception("Failed to create database for clinic {$clinicName}: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Check if a database exists
     *
     * @param string $databaseName
     * @return bool
     */
    public function databaseExists(string $databaseName): bool
    {
        try {
            // Query to check if the database exists
            $results = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            return count($results) > 0;
        } catch (\Exception $e) {
            Log::error('Error checking if database exists: ' . $e->getMessage(), [
                'database' => $databaseName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Setup a tenant's database
     *
     * @param Clinic $clinic
     * @return void
     * @throws \Exception
     */
    public function setupTenantDatabase(Clinic $clinic): void
    {
        try {
            Log::info('Starting tenant database setup', [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name
            ]);

            // Check if database exists first
            if (!$this->databaseExists($clinic->database_name)) {
                Log::info('Database does not exist, creating it', [
                    'database_name' => $clinic->database_name
                ]);
                
                // Create the database if it doesn't exist
                $query = "CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $clinic->database_name) . "`";
                DB::statement($query);
                
                if (!$this->databaseExists($clinic->database_name)) {
                    throw new \Exception("Failed to create database {$clinic->database_name}");
                }
            }
            
            // Switch to the tenant database
            $this->switchToTenant($clinic);
            
            // Create essential tables directly to ensure they exist
            $this->createEssentialTables();
            
            // Run migrations
            $this->runTenantMigrations($clinic);
            
            // Create owner account
            $this->createOwnerAccount($clinic);
            
            // Switch back to the main database
            $this->switchToMain();
            
            Log::info('Tenant database setup completed successfully', [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name
            ]);
        } catch (\Exception $e) {
            Log::error('Error in tenant database setup: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Switch back to main database in case of error
            try {
                $this->switchToMain();
            } catch (\Exception $e2) {
                Log::error('Error switching back to main database: ' . $e2->getMessage());
            }
            
            throw $e;
        }
    }
    
    /**
     * Create essential database tables
     */
    private function createEssentialTables(): void
    {
        try {
            // Create users table if it doesn't exist
            if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('users')) {
                DB::connection('tenant')->statement('
                    CREATE TABLE IF NOT EXISTS `users` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `email_verified_at` timestamp NULL DEFAULT NULL,
                        `password` varchar(255) NOT NULL,
                        `role` varchar(255) NOT NULL DEFAULT "staff",
                        `phone` varchar(20) DEFAULT NULL,
                        `dob` date DEFAULT NULL,
                        `gender` varchar(10) DEFAULT NULL,
                        `employee_id` varchar(50) DEFAULT NULL,
                        `address` text DEFAULT NULL,
                        `city` varchar(100) DEFAULT NULL,
                        `state` varchar(100) DEFAULT NULL,
                        `postal_code` varchar(20) DEFAULT NULL,
                        `hire_date` date DEFAULT NULL,
                        `specialization` varchar(100) DEFAULT NULL,
                        `license_number` varchar(100) DEFAULT NULL,
                        `remember_token` varchar(100) DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `users_email_unique` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
            }
        } catch (\Exception $e) {
            Log::error('Error creating essential tables: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Run tenant migrations
     */
    public function runTenantMigrations(Clinic $clinic): void
    {
        try {
            \Log::info('Starting tenant migrations for clinic', [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name
            ]);

            // Ensure we're on the tenant database
            Config::set('database.connections.tenant.database', $clinic->database_name);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Check if migrations table exists
            if (!Schema::connection('tenant')->hasTable('migrations')) {
                \Log::info('Creating migrations table');
                Schema::connection('tenant')->create('migrations', function ($table) {
                    $table->increments('id');
                    $table->string('migration');
                    $table->integer('batch');
                });
            }

            // Check if subscriptions table exists
            if (!Schema::connection('tenant')->hasTable('subscriptions')) {
                \Log::info('Creating subscriptions table');
                try {
                    DB::connection('tenant')->statement('
                        CREATE TABLE IF NOT EXISTS `subscriptions` (
                            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                            `user_id` bigint unsigned NOT NULL,
                            `clinic_id` bigint unsigned NOT NULL,
                            `plan_name` varchar(255) NOT NULL,
                            `amount` decimal(10,2) NOT NULL,
                            `status` enum("pending","active","cancelled","expired") NOT NULL DEFAULT "pending",
                            `approval_status` enum("pending","approved","rejected") NOT NULL DEFAULT "pending",
                            `start_date` timestamp NULL DEFAULT NULL,
                            `end_date` timestamp NULL DEFAULT NULL,
                            `payment_method` varchar(255) NOT NULL,
                            `card_last_four` varchar(255) DEFAULT NULL,
                            `rejection_reason` varchar(255) DEFAULT NULL,
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `subscriptions_user_id_foreign` (`user_id`),
                            CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ');

                    \Log::info('Successfully created subscriptions table');
                } catch (\Exception $e) {
                    \Log::error('Error creating subscriptions table: ' . $e->getMessage(), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw new \Exception('Failed to create subscriptions table: ' . $e->getMessage());
                }
            } else {
                \Log::info('Subscriptions table already exists');
            }

        } catch (\Exception $e) {
            \Log::error('Error in tenant database setup: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Create the owner account in the tenant database
     */
    private function createOwnerAccount(Clinic $clinic): void
    {
        try {
            if (!DB::connection('tenant')->table('users')->where('role', 'owner')->exists()) {
                DB::connection('tenant')->table('users')->insert([
                    'name' => $clinic->owner_name,
                    'email' => $clinic->owner_email,
                    'password' => bcrypt($clinic->temp_password),
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Owner account created in tenant database', [
                    'clinic_id' => $clinic->id,
                    'owner_email' => $clinic->owner_email
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating owner account: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Switch to tenant database connection
     */
    public function switchToTenant(Clinic $clinic): void
    {
        Config::set('database.connections.tenant.database', $clinic->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        Log::info('Successfully connected to tenant database', [
            'database' => $clinic->database_name,
            'clinic_id' => $clinic->id
        ]);
    }

    /**
     * Switch back to main database connection
     */
    public function switchToMain(): void
    {
        DB::purge('tenant');
        DB::reconnect('mysql');
    }
} 