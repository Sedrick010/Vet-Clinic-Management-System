<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupExtractDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cleanup-extract-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up all extraction directories used for system updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of extraction directories...');
        
        $basePath = storage_path('app/self-updater');
        
        // Find all extraction directories
        $extractDirs = File::glob($basePath . '/extract_*');
        $backupExtractDirs = File::glob($basePath . '/extract_backup_*');
        $allExtractDirs = array_merge($extractDirs, $backupExtractDirs);
        
        $this->info("Found " . count($allExtractDirs) . " extraction directories.");
        
        $cleanedCount = 0;
        
        foreach ($allExtractDirs as $dir) {
            if (is_dir($dir)) {
                $this->info("Cleaning up extraction directory: {$dir}");
                try {
                    File::deleteDirectory($dir);
                    $cleanedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete directory {$dir}: " . $e->getMessage());
                    Log::error("Failed to delete directory {$dir}: " . $e->getMessage());
                }
            }
        }
        
        // Also clean up ZIP files (except the current version)
        $currentVersion = config('self-update.version_installed', '1.0.0');
        $zipFiles = File::glob($basePath . '/*.zip');
        $deletedZips = 0;
        
        foreach ($zipFiles as $zipFile) {
            $fileVersion = pathinfo($zipFile, PATHINFO_FILENAME);
            
            // Keep current version ZIP file for reference
            if ($fileVersion !== $currentVersion) {
                $this->info("Deleting old version ZIP file: {$zipFile}");
                try {
                    File::delete($zipFile);
                    $deletedZips++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete ZIP file {$zipFile}: " . $e->getMessage());
                    Log::error("Failed to delete ZIP file {$zipFile}: " . $e->getMessage());
                }
            }
        }
        
        $this->info("Cleanup completed successfully!");
        $this->info("Cleaned up {$cleanedCount} extraction directories and {$deletedZips} ZIP files.");
        
        return Command::SUCCESS;
    }
}
