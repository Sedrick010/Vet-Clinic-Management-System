<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantDatabaseService;

class FixClientsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-clients-table {subdomain? : The clinic subdomain} {--all : Fix all clinics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the deleted_at column to clients table for soft deletes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $all = $this->option('all');

        if ($all) {
            $clinics = Clinic::whereNotNull('database_name')->get();
            $this->info("Found " . $clinics->count() . " clinics to process");

            $bar = $this->output->createProgressBar($clinics->count());
            $bar->start();

            foreach ($clinics as $clinic) {
                $this->fixClinicClientTable($clinic);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("All clinics processed");
        } elseif ($subdomain) {
            $clinic = Clinic::where('subdomain', $subdomain)->first();
            
            if (!$clinic) {
                $this->error("Clinic with subdomain '$subdomain' not found!");
                return 1;
            }
            
            $this->fixClinicClientTable($clinic);
            $this->info("Processed clinic: {$clinic->name}");
        } else {
            $this->error("Please provide a subdomain or use the --all option");
            return 1;
        }

        return 0;
    }

    /**
     * Fix the clients table for a specific clinic
     */
    private function fixClinicClientTable(Clinic $clinic)
    {
        if (empty($clinic->database_name)) {
            $this->warn("Clinic {$clinic->name} has no database name, skipping");
            return;
        }

        try {
            // Configure the tenant database connection
            config(['database.connections.tenant' => [
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
            ]]);
            
            // Clear any existing connections
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Check if the table exists
            if (!Schema::connection('tenant')->hasTable('clients')) {
                $this->warn("Table 'clients' does not exist in {$clinic->database_name}");
                return;
            }
            
            // Check if the column already exists
            if (Schema::connection('tenant')->hasColumn('clients', 'deleted_at')) {
                $this->line("Column 'deleted_at' already exists in clients table for {$clinic->name}");
                return;
            }
            
            // Add the deleted_at column
            Schema::connection('tenant')->table('clients', function ($table) {
                $table->softDeletes();
            });
            
            $this->info("Added 'deleted_at' column to clients table for {$clinic->name}");
            
        } catch (\Exception $e) {
            $this->error("Error fixing clients table for {$clinic->name}: " . $e->getMessage());
        }
    }
}
