<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ZipArchive;

class CreateSystemBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backup {--force : Run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the current system version';

    /**
     * The list of directories and files to exclude from the backup.
     * 
     * @var array
     */
    protected $excludeFolders = [
        '__MACOSX',
        'node_modules',
        'bootstrap/cache',
        'bower',
        'storage/app',
        'storage/framework',
        'storage/logs',
        'storage/self-update',
        'vendor',
        '.git',
        '.github',
        'public/storage',
        'public/uploads',
        '.env.backup',
        '.env.example',
        '.DS_Store',
        'phpunit.xml',
        '*.log',
        'tests',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating system backup...');
        
        // Get current version
        $currentVersion = config('self-update.version_installed', '1.0.0');
        
        // Ask for confirmation if not forced
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to create a backup of version {$currentVersion}?")) {
                $this->info('Backup cancelled.');
                return;
            }
        }
        
        // Create timestamp for the backup filename
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_{$currentVersion}_{$timestamp}.zip";
        
        // Check and prepare the backup directory
        $backupDir = storage_path('app/version-backups');
        $backupPath = $backupDir . '/' . $backupName;
        
        $this->prepareBackupDirectory($backupDir);
        
        // Create the backup
        try {
            // Create a new ZIP archive
            $zip = new ZipArchive();
            $result = $zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== true) {
                $this->error("Failed to create backup archive (error code: {$result})");
                Log::error("Failed to create backup archive (error code: {$result})");
                return 1;
            }
            
            // Add files to the zip archive
            $this->info('Adding files to backup archive...');
            $this->addFilesToZip($zip, base_path(), '');
            
            // Close the archive
            $zipCloseResult = $zip->close();
            if (!$zipCloseResult) {
                $this->error("Failed to close ZIP archive: " . $zip->getStatusString());
                Log::error("Failed to close ZIP archive: " . $zip->getStatusString());
                return 1;
            }
            
            // Ensure the created file has proper permissions
            chmod($backupPath, 0664);
            
            // Check if backup was created
            if (File::exists($backupPath)) {
                $sizeInMb = round(filesize($backupPath) / 1048576, 2);
                $this->info("✓ Backup created successfully: {$backupName} ({$sizeInMb} MB)");
                $this->info("  Location: {$backupPath}");
                Log::info("Backup created successfully at {$backupPath}");
                return 0;
            } else {
                $this->error('Failed to create backup file');
                Log::error("Failed to create backup file at {$backupPath}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error creating backup: " . $e->getMessage());
            Log::error("Error creating backup: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return 1;
        }
    }
    
    /**
     * Prepare the backup directory by creating it if it doesn't exist
     * and ensuring it has the right permissions.
     * 
     * @param string $backupDir
     * @return void
     */
    protected function prepareBackupDirectory(string $backupDir): void
    {
        if (!File::exists($backupDir)) {
            $this->info("Creating backup directory: {$backupDir}");
            File::makeDirectory($backupDir, 0775, true);
        }
        
        // Ensure the directory is writable
        if (!is_writable($backupDir)) {
            $this->warn("Backup directory is not writable. Updating permissions...");
            chmod($backupDir, 0775);
        }
        
        $this->info("✓ Backup directory ready: {$backupDir}");
    }
    
    /**
     * Add files to the zip archive, excluding specified directories.
     * 
     * @param ZipArchive $zip
     * @param string $basePath
     * @param string $relativePath
     * @return void
     */
    protected function addFilesToZip(ZipArchive $zip, string $basePath, string $relativePath): void
    {
        $fullPath = $basePath . ($relativePath ? '/' . $relativePath : '');
        
        // Get all files and directories in the current path
        $items = glob($fullPath . '/*');
        
        foreach ($items as $item) {
            // Get the relative path of the item
            $itemRelativePath = $relativePath ? $relativePath . '/' . basename($item) : basename($item);
            
            // Check if this item should be excluded
            $shouldExclude = false;
            foreach ($this->excludeFolders as $excludeFolder) {
                if (strpos($itemRelativePath, $excludeFolder) === 0 || $itemRelativePath === $excludeFolder) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                continue;
            }
            
            // If it's a directory, recurse into it
            if (is_dir($item)) {
                $this->addFilesToZip($zip, $basePath, $itemRelativePath);
            } 
            // If it's a file, add it to the zip
            elseif (is_file($item)) {
                try {
                    $zip->addFile($item, $itemRelativePath);
                } catch (\Exception $e) {
                    $this->warn("Could not add file to backup: {$itemRelativePath}");
                    Log::warning("Could not add file to backup: {$itemRelativePath}, error: " . $e->getMessage());
                }
            }
        }
    }
} 