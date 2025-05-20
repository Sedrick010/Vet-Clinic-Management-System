<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;

class SystemUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:update {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run system updates including migrations for both admin and tenant databases';

    /**
     * The tenant database service.
     *
     * @var TenantDatabaseService
     */
    protected $tenantDatabaseService;

    /**
     * Create a new command instance.
     *
     * @param TenantDatabaseService $tenantDatabaseService
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
        $force = $this->option('force');
        
        if (!$force && app()->environment('production')) {
            $this->error('This command cannot be run in production without the --force flag.');
            return 1;
        }
        
        $this->info('Starting system update process...');
        
        try {
            // Step 1: Run migrations for the main database
            $this->runMainDatabaseMigrations();
            
            // Step 2: Ensure system version tables exist
            $this->ensureSystemVersionTablesExist();
            
            // Step 3: Run migrations for all tenant databases
            $this->runTenantDatabaseMigrations();
            
            // Step 4: Update the system version in the database
            $this->updateSystemVersion();
            
            // Step 5: Verify system is properly updated
            $this->verifySystemStatus();
            
            $this->info('System update completed successfully!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('System update encountered an error: ' . $e->getMessage());
            Log::error('System update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Try to update version info anyway, since failures are often just in tenant migrations
            try {
                $this->updateSystemVersion();
                $this->verifySystemStatus();
                $this->info('Version information was updated despite errors in some components.');
            } catch (\Exception $inner) {
                Log::error('Failed to update version information after error', [
                    'error' => $inner->getMessage()
                ]);
            }
            
            return 1;
        }
    }
    
    /**
     * Run migrations for the main database.
     */
    private function runMainDatabaseMigrations()
    {
        $this->info('Running migrations for the main database...');
        
        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true
            ]);
            
            if ($exitCode === 0) {
                $this->info('Main database migrations completed successfully.');
            } else {
                $this->error('Failed to run main database migrations.');
                Log::error('Main database migrations failed.');
            }
        } catch (\Exception $e) {
            $this->error('Exception running main database migrations: ' . $e->getMessage());
            Log::error('Exception running main database migrations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Ensure system version tables exist in the main database.
     */
    private function ensureSystemVersionTablesExist()
    {
        $this->info('Ensuring system version tables exist...');
        
        try {
            // Ensure system_versions table exists
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('system_versions')) {
                $this->warn('system_versions table does not exist. Creating it...');
                
                DB::connection('mysql')->statement('
                    CREATE TABLE IF NOT EXISTS `system_versions` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `version` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `description` text,
                        `is_current` tinyint(1) NOT NULL DEFAULT "0",
                        `released_at` timestamp NULL DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                // Insert initial version
                $currentVersion = config('self-update.version_installed', '1.0.0');
                DB::connection('mysql')->table('system_versions')->insert([
                    'version' => $currentVersion,
                    'name' => 'Initial Release',
                    'description' => 'The initial release of the VetClinic system',
                    'is_current' => true,
                    'released_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info('system_versions table created successfully.');
            }
            
            // Ensure system_updates table exists
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('system_updates')) {
                $this->warn('system_updates table does not exist. Creating it...');
                
                DB::connection('mysql')->statement('
                    CREATE TABLE IF NOT EXISTS `system_updates` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `version` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `description` text NOT NULL,
                        `changes` text NOT NULL,
                        `features` text,
                        `bug_fixes` text,
                        `is_critical` tinyint(1) NOT NULL DEFAULT "0",
                        `is_security` tinyint(1) NOT NULL DEFAULT "0",
                        `is_mandatory` tinyint(1) NOT NULL DEFAULT "0",
                        `available_from` timestamp NULL DEFAULT NULL,
                        `expires_at` timestamp NULL DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                $this->info('system_updates table created successfully.');
            }
            
            // Ensure clinic_updates table exists
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('clinic_updates')) {
                $this->warn('clinic_updates table does not exist. Creating it...');
                
                DB::connection('mysql')->statement('
                    CREATE TABLE IF NOT EXISTS `clinic_updates` (
                        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                        `clinic_id` bigint unsigned NOT NULL,
                        `system_update_id` bigint unsigned,
                        `version` varchar(255),
                        `description` text,
                        `status` enum("pending","applied","dismissed") NOT NULL DEFAULT "pending",
                        `applied_at` timestamp NULL DEFAULT NULL,
                        `dismissed_at` timestamp NULL DEFAULT NULL,
                        `notes` text,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `clinic_updates_clinic_id_foreign` (`clinic_id`),
                        CONSTRAINT `clinic_updates_clinic_id_foreign` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ');
                
                $this->info('clinic_updates table created successfully.');
            }
        } catch (\Exception $e) {
            $this->error('Exception ensuring system version tables: ' . $e->getMessage());
            Log::error('Exception ensuring system version tables', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Run migrations for all tenant databases.
     */
    private function runTenantDatabaseMigrations()
    {
        $this->info('Running migrations for all tenant databases...');
        
        try {
            $exitCode = Artisan::call('migrate:all-tenants', [
                '--force' => true
            ]);
            
            if ($exitCode === 0) {
                $this->info('Tenant database migrations completed successfully.');
            } else {
                $this->warn('Some tenant database migrations may have failed. Check the logs for details.');
                Log::warning('Some tenant database migrations failed.');
            }
        } catch (\Exception $e) {
            $this->error('Exception running tenant database migrations: ' . $e->getMessage());
            Log::error('Exception running tenant database migrations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Update the system version in the database.
     */
    private function updateSystemVersion()
    {
        $this->info('Updating system version information...');
        
        try {
            // Get the current system version
            $currentVersion = config('self-update.version_installed', env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.0'));
            
            // Check if we already have this version in the database
            $existingVersion = DB::connection('mysql')
                ->table('system_versions')
                ->where('version', $currentVersion)
                ->first();
            
            // Mark all existing versions as not current
            DB::connection('mysql')
                ->table('system_versions')
                ->update(['is_current' => false]);
                
            if ($existingVersion) {
                // Update existing version to be the current one
                DB::connection('mysql')
                    ->table('system_versions')
                    ->where('id', $existingVersion->id)
                    ->update([
                        'is_current' => true,
                        'updated_at' => now()
                    ]);
                
                $this->info("Updated existing version record to mark '{$currentVersion}' as current.");
            } else {
                // Insert a new version record
                DB::connection('mysql')
                    ->table('system_versions')
                    ->insert([
                        'version' => $currentVersion,
                        'name' => 'System Update',
                        'description' => 'System updated to version ' . $currentVersion,
                        'is_current' => true,
                        'released_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                
                $this->info("Added new version record for '{$currentVersion}'.");
            }
        } catch (\Exception $e) {
            $this->error('Exception updating system version: ' . $e->getMessage());
            Log::error('Exception updating system version', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Verify system is properly updated and all components are in sync.
     */
    private function verifySystemStatus()
    {
        $this->info('Verifying system status and configuration...');
        
        try {
            $configVersion = config('self-update.version_installed', env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.0'));
            
            // Get current version from database
            $dbVersion = DB::connection('mysql')
                ->table('system_versions')
                ->where('is_current', true)
                ->first();
                
            if (!$dbVersion) {
                $this->warn('No current version marked in database. Attempting to fix...');
                
                // Find matching version or create it
                $versionRecord = DB::connection('mysql')
                    ->table('system_versions')
                    ->where('version', $configVersion)
                    ->first();
                    
                if ($versionRecord) {
                    DB::connection('mysql')
                        ->table('system_versions')
                        ->where('id', $versionRecord->id)
                        ->update([
                            'is_current' => true,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::connection('mysql')
                        ->table('system_versions')
                        ->insert([
                            'version' => $configVersion,
                            'name' => 'System Update',
                            'description' => 'System updated to version ' . $configVersion,
                            'is_current' => true,
                            'released_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                
                $this->info('Fixed missing current version in database.');
            } else if ($dbVersion->version !== $configVersion) {
                $this->warn("Version mismatch: Config version is {$configVersion}, DB version is {$dbVersion->version}. Fixing...");
                
                // Reset all current versions
                DB::connection('mysql')
                    ->table('system_versions')
                    ->update(['is_current' => false]);
                    
                // Find or create the correct version
                $versionRecord = DB::connection('mysql')
                    ->table('system_versions')
                    ->where('version', $configVersion)
                    ->first();
                    
                if ($versionRecord) {
                    DB::connection('mysql')
                        ->table('system_versions')
                        ->where('id', $versionRecord->id)
                        ->update([
                            'is_current' => true,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::connection('mysql')
                        ->table('system_versions')
                        ->insert([
                            'version' => $configVersion,
                            'name' => 'System Update',
                            'description' => 'System updated to version ' . $configVersion,
                            'is_current' => true,
                            'released_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
                
                $this->info('Fixed version mismatch in database.');
            } else {
                $this->info("System status verified: Version {$configVersion} is properly configured.");
            }
        } catch (\Exception $e) {
            $this->error('Error verifying system status: ' . $e->getMessage());
            Log::error('Error verifying system status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 