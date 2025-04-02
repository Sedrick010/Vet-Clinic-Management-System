<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TenantDatabaseService
{
    /**
     * Create a new database for a tenant.
     *
     * @param string $clinicName
     * @return string
     */
    public function createDatabase(string $clinicName): string
    {
        try {
            // Generate a unique database name
            $databaseName = 'vet_clinic_' . Str::slug($clinicName) . '_' . Str::lower(Str::random(8));
            
            // Create the database
            DB::statement("CREATE DATABASE {$databaseName}");
            
            return $databaseName;
        } catch (\Exception $e) {
            Log::error('Error creating tenant database: ' . $e->getMessage());
            
            // For development, return a unique database name using the main database + timestamp
            if (app()->environment('local')) {
                $timestamp = time();
                $randomStr = Str::random(4);
                return 'vetclinicv2_' . Str::slug($clinicName) . '_' . $timestamp . '_' . $randomStr;
            }
            
            throw $e;
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
            // Switch to the tenant database
            $this->switchToTenant($clinic);
            
            // Run migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
            
            // Seed with default data if needed
            // Artisan::call('db:seed', ['--class' => 'TenantDatabaseSeeder', '--force' => true]);
            
            // Switch back to the main database
            $this->switchToMain();
        } catch (\Exception $e) {
            Log::error('Error setting up tenant database: ' . $e->getMessage());
            
            if (!app()->environment('local')) {
                throw $e;
            }
        }
    }
    
    /**
     * Switch to a tenant's database
     *
     * @param Clinic $clinic
     * @return void
     */
    public function switchToTenant(Clinic $clinic): void
    {
        try {
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'url' => env('DATABASE_URL'),
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => $clinic->database_name,
                'username' => env('DB_USERNAME', 'forge'),
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
            
            DB::purge('tenant');
            DB::reconnect('tenant');
        } catch (\Exception $e) {
            Log::error('Error switching to tenant database: ' . $e->getMessage());
            
            if (!app()->environment('local')) {
                throw $e;
            }
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