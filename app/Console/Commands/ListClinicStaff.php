<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

class ListClinicStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:list-staff {subdomain : The clinic subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all staff members in a clinic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        
        // Find the clinic
        $clinic = Clinic::where('subdomain', $subdomain)->first();
        
        if (!$clinic) {
            $this->error("Clinic with subdomain '$subdomain' not found!");
            return 1;
        }
        
        $this->info("Listing staff for clinic: {$clinic->name}");
        
        if (empty($clinic->database_name)) {
            $this->error("Clinic {$clinic->name} has no database!");
            return 1;
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
            
            // Get all staff
            $staff = DB::connection('tenant')
                ->table('staff')
                ->select('id', 'name', 'email', 'role', 'is_active', 'created_at')
                ->get();
                
            if ($staff->isEmpty()) {
                $this->warn("No staff found in this clinic.");
                return 0;
            }
            
            // Display staff in a table
            $headers = ['ID', 'Name', 'Email', 'Role', 'Active', 'Created At'];
            $rows = [];
            
            foreach ($staff as $member) {
                $rows[] = [
                    $member->id,
                    $member->name,
                    $member->email,
                    $member->role,
                    $member->is_active ? 'Yes' : 'No',
                    $member->created_at
                ];
            }
            
            $this->table($headers, $rows);
            
            // Count by role
            $roleCounts = [];
            foreach ($staff->groupBy('role') as $role => $members) {
                $roleCounts[] = [$role, $members->count()];
            }
            
            $this->info("\nStaff Count by Role:");
            $this->table(['Role', 'Count'], $roleCounts);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error listing staff: " . $e->getMessage());
            return 1;
        }
    }
}
