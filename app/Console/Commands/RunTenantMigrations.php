<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RunTenantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {clinic_id? : The ID of the clinic to migrate} {--fresh : Whether to run a fresh migration} {--force-staff : Force create staff table directly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $fresh = $this->option('fresh');
        $forceStaff = $this->option('force-staff');

        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found!");
                return 1;
            }
            
            $this->info("Running migrations for {$clinic->name} (ID: {$clinic->id}, DB: {$clinic->database_name})");
            $this->migrateTenantDatabase($clinic, $fresh, true, $forceStaff);
        } else {
            $this->info("Running migrations for all clinics...");
            
            $clinics = Clinic::where('approval_status', 'approved')->get();
            $this->withProgressBar($clinics, function ($clinic) use ($fresh, $forceStaff) {
                $this->migrateTenantDatabase($clinic, $fresh, false, $forceStaff);
            });
            
            $this->newLine(2);
            $this->info("All tenant migrations completed!");
        }

        return 0;
    }

    /**
     * Run migrations for a specific tenant database
     */
    private function migrateTenantDatabase(Clinic $clinic, bool $fresh, bool $showOutput = true, bool $forceStaff = false)
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
            
            // Drop all tables if fresh migration requested
            if ($fresh) {
                if ($showOutput) {
                    $this->info("Dropping all tables in {$clinic->database_name}...");
                }
                
                $this->dropAllTables($clinic->database_name);
            }
            
            // 1. Always create staff table first, since other tables reference it
            if ($fresh || $forceStaff || !Schema::connection('tenant')->hasTable('staff')) {
                if ($showOutput) {
                    $this->info("Creating staff table...");
                }
                
                $this->createStaffTable();
                
                if ($showOutput && Schema::connection('tenant')->hasTable('staff')) {
                    $this->info("Staff table created successfully!");
                }
            }
            
            // 2. Run only the staff migration first
            try {
                if ($showOutput) {
                    $this->info("Running staff migrations...");
                }
                
                $staffMigrationPath = 'database/migrations/tenant/2025_04_13_create_staff_table.php';
                
                // Only run if the file exists
                if (file_exists(base_path($staffMigrationPath))) {
                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => 'database/migrations/tenant/2025_04_13_create_staff_table.php',
                        '--force' => true,
                    ]);
                    
                    if ($showOutput) {
                        $this->info(trim(Artisan::output()));
                    }
                } else {
                    if ($showOutput) {
                        $this->warn("Staff migration file not found at: " . $staffMigrationPath);
                    }
                }
            } catch (\Exception $e) {
                if ($showOutput) {
                    $this->warn("Note: Staff migration may have already been run: " . $e->getMessage());
                }
                Log::warning("Staff migration issue: " . $e->getMessage());
            }
            
            // 3. Create deleted_staff table if it doesn't exist
            if (!Schema::connection('tenant')->hasTable('deleted_staff')) {
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
                    $this->info("Created deleted_staff table");
                }
            }
            
            // 4. Run the remaining migrations
            try {
                if ($showOutput) {
                    $this->info("Running all other tenant migrations...");
                }
                
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);
                
                if ($showOutput) {
                    $this->info(trim(Artisan::output()));
                }
            } catch (\Exception $e) {
                if ($showOutput) {
                    $this->error("Error running tenant migrations: " . $e->getMessage());
                }
                Log::error("Error running tenant migrations: " . $e->getMessage());
            }
            
            // Verify if staff table exists to confirm migration success
            $staffTableExists = Schema::connection('tenant')->hasTable('staff');
            if ($showOutput) {
                if ($staffTableExists) {
                    $this->info("Migration successful! Staff table exists.");
                } else {
                    $this->error("Migration issue: Staff table does not exist.");
                }
            }
            
            if ($showOutput) {
                $this->info("Migration completed for {$clinic->name}!");
            }
        } catch (\Exception $e) {
            if ($showOutput) {
                $this->error("Error migrating {$clinic->database_name}: " . $e->getMessage());
            }
            Log::error("Error migrating tenant database: " . $e->getMessage());
        }
    }
    
    /**
     * Drop all tables in the database
     */
    private function dropAllTables(string $database)
    {
        try {
            // Disable foreign key checks temporarily
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Get all tables
            $tables = DB::connection('tenant')
                ->select('SHOW TABLES');
                
            // The result column name depends on the database name: 'Tables_in_database'
            $column = 'Tables_in_' . $database;
            
            // Drop each table
            foreach ($tables as $table) {
                if (isset($table->$column)) {
                    $tableName = $table->$column;
                    DB::connection('tenant')->statement("DROP TABLE `{$tableName}`");
                }
            }
            
            // Re-enable foreign key checks
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            Log::error("Error dropping tables: " . $e->getMessage());
        }
    }
    
    /**
     * Create staff table directly
     */
    private function createStaffTable()
    {
        try {
            // Create the staff table
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
            
            Log::info("Staff table created successfully");
        } catch (\Exception $e) {
            Log::error("Error creating staff table: " . $e->getMessage());
        }
    }
}
