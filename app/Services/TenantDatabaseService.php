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
                // Use direct SQL with proper quoting to avoid SQL injection
                $query = "CREATE DATABASE `" . str_replace('`', '', $databaseName) . "`";
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
                    'error' => $e->getMessage()
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
                $timestamp = time();
                $randomStr = Str::random(4);
                return config('database.connections.mysql.database') . '_tenant_' . Str::slug($clinicName) . '_' . $timestamp . '_' . $randomStr;
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
            Log::error('Error checking if database exists: ' . $e->getMessage(), ['database' => $databaseName]);
            return false;
        }
    }
    
    /**
     * Setup a tenant's database
     *
     * @param Clinic $clinic
     * @return void
     */
    public function setupTenantDatabase(Clinic $clinic): void
    {
        try {
            // Check if database exists first
            if (!$this->databaseExists($clinic->database_name)) {
                // Create the database if it doesn't exist
                $query = "CREATE DATABASE `" . str_replace('`', '', $clinic->database_name) . "`";
                DB::statement($query);
                Log::info('Created missing tenant database', ['database' => $clinic->database_name]);
            }
            
            // Switch to the tenant database
            $this->switchToTenant($clinic);
            
            // Check if tables already exist to avoid migration errors
            $staffTableExists = false;
            try {
                $staffTableExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('staff');
            } catch (\Exception $e) {
                Log::warning('Error checking if staff table exists: ' . $e->getMessage(), [
                    'database' => $clinic->database_name
                ]);
            }
            
            // Log migration start
            Log::info('Running tenant database migrations', [
                'database' => $clinic->database_name,
                'staff_table_exists' => $staffTableExists
            ]);
            
            if (!$staffTableExists) {
                // Create the staff table directly if migrations are failing
                try {
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
                    
                    Log::info('Created staff tables directly', [
                        'database' => $clinic->database_name
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating staff tables directly: ' . $e->getMessage(), [
                        'database' => $clinic->database_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Then run all tenant migrations
            try {
                // Run migrations with more detailed output and explicit path
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                
                $migrationOutput = trim(Artisan::output());
                
                Log::info('Tenant database migrations completed', [
                    'database' => $clinic->database_name,
                    'migration_output' => $migrationOutput
                ]);
                
                // Double check if staff table exists after migrations
                $staffTableExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('staff');
                if (!$staffTableExists) {
                    Log::error('Staff table still does not exist after migrations', [
                        'database' => $clinic->database_name,
                        'migration_output' => $migrationOutput
                    ]);
                    
                    // Try to recreate it one more time if needed
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
                }
                
                // Run the fix:tables command to ensure deleted_at columns exist for all required tables
                Artisan::call('fix:tables', [
                    'clinic_id' => $clinic->id,
                ]);
                
                $fixTablesOutput = trim(Artisan::output());
                
                Log::info('Fixed tables with missing deleted_at columns', [
                    'database' => $clinic->database_name,
                    'fix_tables_output' => $fixTablesOutput
                ]);
                
                // Ensure appointments table has duration column
                $hasDurationColumn = false;
                try {
                    $hasDurationColumn = DB::connection('tenant')->getSchemaBuilder()->hasColumn('appointments', 'duration');
                    
                    if (!$hasDurationColumn && DB::connection('tenant')->getSchemaBuilder()->hasTable('appointments')) {
                        // Add duration column if it doesn't exist but table does
                        Schema::connection('tenant')->table('appointments', function ($table) {
                            $table->integer('duration')->nullable()->after('end_time')->comment('Duration in minutes');
                        });
                        
                        // Update existing appointments to calculate duration
                        DB::connection('tenant')->statement('
                            UPDATE appointments 
                            SET duration = TIMESTAMPDIFF(MINUTE, start_time, end_time) 
                            WHERE start_time IS NOT NULL AND end_time IS NOT NULL
                        ');
                        
                        Log::info('Added missing duration column to appointments table', [
                            'database' => $clinic->database_name
                        ]);
                    }
                    
                    // Also check for appointment_type column
                    $hasAppointmentTypeColumn = DB::connection('tenant')->getSchemaBuilder()->hasColumn('appointments', 'appointment_type');
                    
                    if (!$hasAppointmentTypeColumn && DB::connection('tenant')->getSchemaBuilder()->hasTable('appointments')) {
                        // Add appointment_type column if it doesn't exist but table does
                        Schema::connection('tenant')->table('appointments', function ($table) {
                            $table->string('appointment_type')->nullable()->after('status')->comment('Type of appointment (check-up, vaccination, etc.)');
                        });
                        
                        // Set a default value for existing appointments
                        DB::connection('tenant')->statement("
                            UPDATE appointments 
                            SET appointment_type = 'check-up' 
                            WHERE appointment_type IS NULL
                        ");
                        
                        Log::info('Added missing appointment_type column to appointments table', [
                            'database' => $clinic->database_name
                        ]);
                    }
                    
                    // Also check for client_name column
                    $hasClientNameColumn = DB::connection('tenant')->getSchemaBuilder()->hasColumn('appointments', 'client_name');
                    
                    if (!$hasClientNameColumn && DB::connection('tenant')->getSchemaBuilder()->hasTable('appointments')) {
                        // Add client_name column if it doesn't exist but table does
                        Schema::connection('tenant')->table('appointments', function ($table) {
                            $table->string('client_name')->nullable()->after('client_id')->comment('Name of the client for the appointment');
                        });
                        
                        Log::info('Added missing client_name column to appointments table', [
                            'database' => $clinic->database_name
                        ]);
                    }
                    
                    // Ensure system_updates tables exist
                    $this->ensureSystemUpdateTablesExist();
                    
                } catch (\Exception $e) {
                    Log::error('Error checking/adding columns to appointments table: ' . $e->getMessage(), [
                        'database' => $clinic->database_name,
                        'error' => $e->getMessage()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error running tenant migrations: ' . $e->getMessage(), [
                    'database' => $clinic->database_name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Switch back to the main database
            $this->switchToMain();
            
            Log::info('Tenant database setup completed successfully', [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
        } catch (Exception $e) {
            Log::error('Error setting up tenant database: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database_name' => $clinic->database_name ?? 'undefined',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Switch to a tenant's database
     *
     * @param Clinic $clinic
     * @return void
     * @throws Exception if the database doesn't exist or connection fails
     */
    public function switchToTenant(Clinic $clinic): void
    {
        // First check if the database exists
        if (!$this->databaseExists($clinic->database_name)) {
            Log::error('Attempted to connect to non-existent tenant database', [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
            throw new Exception("Tenant database does not exist: {$clinic->database_name}");
        }
        
        try {
            // Get the main database credentials to reuse them for the tenant connection
            $mainConnection = config('database.connections.mysql');
            
            // Clear any existing tenant connection first
            DB::purge('tenant');
            
            // Configure the tenant connection
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'url' => env('DATABASE_URL'),
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => $clinic->database_name,
                'username' => env('DB_USERNAME', $mainConnection['username'] ?? 'root'),
                'password' => env('DB_PASSWORD', $mainConnection['password'] ?? ''),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    \PDO::ATTR_PERSISTENT => false, // Disable persistent connections to avoid issues
                    \PDO::ATTR_TIMEOUT => 5, // Set a timeout for connection attempts
                ]) : [],
            ]);
            
            // Connect to the tenant database
            DB::reconnect('tenant');
            
            // Test the connection to make sure it works
            $pdo = DB::connection('tenant')->getPdo();
            
            // Additional check - run a test query
            $result = DB::connection('tenant')->select('SELECT 1 as test');
            
            if (empty($result) || !isset($result[0]->test) || $result[0]->test != 1) {
                throw new Exception("Could not execute test query on tenant database");
            }
            
            Log::debug('Successfully connected to tenant database', [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
        } catch (\Exception $e) {
            // If we failed on the first attempt, try once more with a fresh connection
            try {
                // Clear any existing tenant connection and try again
                DB::purge('tenant');
                
                // Get the main database credentials to reuse them for the tenant connection
                $mainConnection = config('database.connections.mysql');
                
                // Configure the tenant connection again
                Config::set('database.connections.tenant', [
                    'driver' => 'mysql',
                    'url' => env('DATABASE_URL'),
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => $clinic->database_name,
                    'username' => env('DB_USERNAME', $mainConnection['username'] ?? 'root'),
                    'password' => env('DB_PASSWORD', $mainConnection['password'] ?? ''),
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => null,
                    'options' => extension_loaded('pdo_mysql') ? array_filter([
                        \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                        \PDO::ATTR_PERSISTENT => false,
                        \PDO::ATTR_TIMEOUT => 5,
                    ]) : [],
                ]);
                
                DB::reconnect('tenant');
                
                // Test the connection again
                DB::connection('tenant')->getPdo();
                
                Log::info('Successfully reconnected to tenant database after initial failure', [
                    'clinic_id' => $clinic->id,
                    'database' => $clinic->database_name
                ]);
            } catch (\Exception $retryException) {
                // If retry also failed, log and throw the original exception
                Log::error('Error switching to tenant database (retry also failed): ' . $e->getMessage(), [
                    'clinic_id' => $clinic->id,
                    'database' => $clinic->database_name,
                    'original_error' => $e->getMessage(),
                    'retry_error' => $retryException->getMessage()
                ]);
                
                throw $e;
            }
        }
    }
    
    /**
     * Register the tenant database connection configuration without connecting
     *
     * @param Clinic $clinic
     * @return void
     */
    public function registerTenantConnection(Clinic $clinic): void
    {
        // Get the main database credentials to reuse them for the tenant connection
        $mainConnection = config('database.connections.mysql');
        
        // Configure the tenant connection
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => $clinic->database_name,
            'username' => env('DB_USERNAME', $mainConnection['username'] ?? 'root'),
            'password' => env('DB_PASSWORD', $mainConnection['password'] ?? ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                \PDO::ATTR_PERSISTENT => false, // Disable persistent connections to avoid issues
                \PDO::ATTR_TIMEOUT => 5, // Set a timeout for connection attempts
            ]) : [],
        ]);
        
        Log::debug('Tenant database connection configured', [
            'clinic_id' => $clinic->id,
            'database' => $clinic->database_name
        ]);
    }
    
    /**
     * Switch back to the main database
     *
     * @return void
     */
    public function switchToMain(): void
    {
        try {
            DB::purge('tenant');
            DB::reconnect('mysql');
        } catch (\Exception $e) {
            Log::error('Error switching back to main database: ' . $e->getMessage());
            
            if (!app()->environment('local')) {
                throw $e;
            }
        }
    }

    /**
     * Ensure system_updates tables exist
     *
     * @return void
     */
    public function ensureSystemUpdateTablesExist(): void
    {
        try {
            $connection = DB::connection('tenant');
            
            // Check if system_updates table exists
            if (!$connection->getSchemaBuilder()->hasTable('system_updates')) {
                Log::info('Creating system_updates table');
                $connection->statement('
                    CREATE TABLE IF NOT EXISTS `system_updates` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `version` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `description` text NOT NULL,
                        `changes` text NOT NULL,
                        `features` text DEFAULT NULL,
                        `bug_fixes` text DEFAULT NULL,
                        `is_critical` tinyint(1) NOT NULL DEFAULT 0,
                        `is_security` tinyint(1) NOT NULL DEFAULT 0,
                        `is_mandatory` tinyint(1) NOT NULL DEFAULT 0,
                        `available_from` timestamp NULL DEFAULT NULL,
                        `expires_at` timestamp NULL DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
            }
            
            // Check if system_versions table exists
            if (!$connection->getSchemaBuilder()->hasTable('system_versions')) {
                Log::info('Creating system_versions table');
                $connection->statement('
                    CREATE TABLE IF NOT EXISTS `system_versions` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `version` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `description` text DEFAULT NULL,
                        `is_current` tinyint(1) NOT NULL DEFAULT 0,
                        `released_at` timestamp NULL DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                // Insert initial version
                $currentVersion = config('self-update.version_installed', '1.0.0');
                $connection->table('system_versions')->insert([
                    'version' => $currentVersion,
                    'name' => 'Initial Release',
                    'description' => 'The initial release of the VetClinic system',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Check if clinic_updates table exists
            if (!$connection->getSchemaBuilder()->hasTable('clinic_updates')) {
                Log::info('Creating clinic_updates table');
                $connection->statement('
                    CREATE TABLE IF NOT EXISTS `clinic_updates` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `clinic_id` bigint unsigned NOT NULL,
                        `system_update_id` bigint unsigned DEFAULT NULL,
                        `is_applied` tinyint(1) NOT NULL DEFAULT 0,
                        `is_dismissed` tinyint(1) NOT NULL DEFAULT 0,
                        `applied_at` timestamp NULL DEFAULT NULL,
                        `dismissed_at` timestamp NULL DEFAULT NULL,
                        `notes` text DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
            }
            
            Log::info('System update tables verified');
            
        } catch (\Exception $e) {
            Log::error('Error ensuring system update tables exist: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 