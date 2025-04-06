<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Clinic;
use Carbon\Carbon;

class BackupDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-databases {--destination=local} {--compress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup main database and all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backups...');
        
        // Set timestamp for backup files
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        
        // Get command options
        $destination = $this->option('destination');
        $compress = $this->option('compress');
        
        // Create backup directory if not exists
        $backupPath = storage_path('app/backups/' . $timestamp);
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        // Get database connection details
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $mainDatabase = config('database.connections.mysql.database');
        
        // Path to mysqldump
        $mysqldump = 'C:/xampp/mysql/bin/mysqldump.exe';
        
        // 1. Backup main database
        $this->info("Backing up main database: {$mainDatabase}");
        $mainBackupFile = "{$backupPath}/{$mainDatabase}.sql";
        
        $command = "\"{$mysqldump}\" --host={$host} --port={$port} --user={$username}" . 
                  ($password ? " --password={$password}" : "") . 
                  " --databases {$mainDatabase} --result-file=\"{$mainBackupFile}\"";
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            $this->error("Failed to backup main database");
            Log::error("Database backup failed for {$mainDatabase}");
        } else {
            $this->info("Main database backup successful: {$mainBackupFile}");
            
            // Compress if requested
            if ($compress) {
                $this->compressFile($mainBackupFile);
            }
        }
        
        // 2. Backup all tenant databases
        $this->info("Retrieving tenant databases...");
        $clinics = Clinic::where('approval_status', 'approved')->get();
        $this->info("Found " . $clinics->count() . " tenant databases to backup");
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($clinics as $clinic) {
            $tenantDb = $clinic->database_name;
            
            $this->info("Backing up tenant database: {$tenantDb} ({$clinic->name})");
            $tenantBackupFile = "{$backupPath}/{$tenantDb}.sql";
            
            $command = "\"{$mysqldump}\" --host={$host} --port={$port} --user={$username}" . 
                      ($password ? " --password={$password}" : "") . 
                      " --databases {$tenantDb} --result-file=\"{$tenantBackupFile}\"";
            
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                $this->error("Failed to backup tenant database: {$tenantDb}");
                Log::error("Database backup failed for tenant {$tenantDb}");
                $failCount++;
            } else {
                $this->info("Tenant database backup successful: {$tenantBackupFile}");
                $successCount++;
                
                // Compress if requested
                if ($compress) {
                    $this->compressFile($tenantBackupFile);
                }
            }
        }
        
        // 3. Generate backup report
        $this->generateReport($backupPath, $timestamp, $mainDatabase, $clinics, $successCount, $failCount);
        
        // 4. Move to different storage if needed
        if ($destination !== 'local') {
            $this->moveBackups($backupPath, $destination);
        }
        
        $this->info("Database backup process completed!");
        $this->info("Successful backups: {$successCount}, Failed backups: {$failCount}");
        $this->info("Backup files stored in: {$backupPath}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Compress a file using gzip
     */
    private function compressFile($filePath)
    {
        $this->info("Compressing file: {$filePath}");
        
        // Check if we're on Windows and use the appropriate command
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // For Windows, we'll use PHP's gzcompress
            $content = file_get_contents($filePath);
            file_put_contents("{$filePath}.gz", gzencode($content, 9));
            unlink($filePath); // Remove original file
        } else {
            // For Unix-like systems, we can use the gzip command
            exec("gzip {$filePath}");
        }
    }
    
    /**
     * Generate an HTML report of the backups
     */
    private function generateReport($backupPath, $timestamp, $mainDatabase, $clinics, $successCount, $failCount)
    {
        $this->info("Generating backup report...");
        
        $reportFile = "{$backupPath}/backup_report.html";
        
        $reportContent = "<!DOCTYPE html>
        <html>
        <head>
            <title>VetClinic Database Backup Report - {$timestamp}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #3366cc; }
                .success { color: green; }
                .error { color: red; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
            </style>
        </head>
        <body>
            <h1>VetClinic Database Backup Report</h1>
            <p>Backup completed on: {$timestamp}</p>
            <h2>Backup Summary</h2>
            <p>Main database: <span class='success'>{$mainDatabase}</span></p>
            <p>Total tenant databases: {$clinics->count()}</p>
            <p>Successful backups: <span class='success'>{$successCount}</span></p>
            <p>Failed backups: <span class='error'>{$failCount}</span></p>
            
            <h2>Databases</h2>
            <table>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Database Name</th>
                    <th>Backup File</th>
                </tr>
                <tr>
                    <td>Main</td>
                    <td>-</td>
                    <td>VetClinic Central</td>
                    <td>{$mainDatabase}</td>
                    <td>" . basename($mainDatabase) . ".sql</td>
                </tr>";
        
        foreach ($clinics as $clinic) {
            $reportContent .= "
                <tr>
                    <td>Tenant</td>
                    <td>{$clinic->id}</td>
                    <td>{$clinic->name}</td>
                    <td>{$clinic->database_name}</td>
                    <td>{$clinic->database_name}.sql</td>
                </tr>";
        }
        
        $reportContent .= "
            </table>
        </body>
        </html>";
        
        file_put_contents($reportFile, $reportContent);
        $this->info("Backup report created: {$reportFile}");
    }
    
    /**
     * Move backups to a different storage location
     */
    private function moveBackups($backupPath, $destination)
    {
        $this->info("Moving backups to {$destination} storage...");
        
        // Implementation depends on your needs:
        // - S3 storage
        // - FTP/SFTP
        // - Network drive
        // For now, we'll just log this
        
        $this->warn("Moving backups to {$destination} is not implemented yet");
    }
} 