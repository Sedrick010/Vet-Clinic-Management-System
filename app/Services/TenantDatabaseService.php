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
            
            // Check if users table already exists to avoid migration errors
            $tableExists = false;
            try {
                $tableExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('users');
            } catch (\Exception $e) {
                Log::warning('Error checking if users table exists: ' . $e->getMessage(), [
                    'database' => $clinic->database_name
                ]);
            }
            
            // Log migration start
            Log::info('Running tenant database migrations', [
                'database' => $clinic->database_name,
                'users_table_exists' => $tableExists
            ]);
            
            if (!$tableExists) {
                // Create the users table directly if migrations are failing
                try {
                    DB::connection('tenant')->statement('
                        CREATE TABLE IF NOT EXISTS `users` (
                            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(255) NOT NULL,
                            `email` varchar(255) NOT NULL,
                            `email_verified_at` timestamp NULL DEFAULT NULL,
                            `password` varchar(255) NOT NULL,
                            `role` varchar(255) NOT NULL DEFAULT "staff",
                            `remember_token` varchar(100) DEFAULT NULL,
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `users_email_unique` (`email`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ');
                    
                    Log::info('Created users table directly', [
                        'database' => $clinic->database_name
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating users table directly: ' . $e->getMessage(), [
                        'database' => $clinic->database_name
                    ]);
                }
            }
            
            // Then run all tenant migrations
            try {
                // Run migrations
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                
                Log::info('Tenant database migrations completed', [
                    'database' => $clinic->database_name,
                    'migration_output' => trim(Artisan::output())
                ]);
            } catch (\Exception $e) {
                Log::error('Error running tenant migrations: ' . $e->getMessage(), [
                    'database' => $clinic->database_name,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // We'll continue if there was an error with migrations since we created the users table directly
            }
            
            // Switch back to the main database
            $this->switchToMain();
            
            Log::info('Tenant database setup completed successfully', [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting up tenant database: ' . $e->getMessage(), [
                'database' => $clinic->database_name,
                'clinic_id' => $clinic->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Always throw the exception so the caller can handle it
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
                ]) : [],
            ]);
            
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Test the connection to make sure it works
            DB::connection('tenant')->getPdo();
            
            Log::debug('Successfully connected to tenant database', [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
        } catch (\Exception $e) {
            Log::error('Error switching to tenant database: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'database' => $clinic->database_name
            ]);
            
            // Always throw the exception - we need ResolveTenant to catch it
            throw $e;
        }
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
} 