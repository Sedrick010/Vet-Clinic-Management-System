<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\SystemVersion;
use ZipArchive;

/**
 * Service for handling version deployment operations.
 * This service downloads version ZIP files on-demand when updating or downgrading,
 * and immediately deploys them. ZIP files are downloaded to the self-updater directory
 * temporarily and are automatically cleaned up after successful deployment.
 */
class VersionDeploymentService
{
    protected $customUpdater;
    protected $storagePath;
    protected $backupPath;
    protected $basePath;
    protected $excludeFolders;
    
    public function __construct(CustomUpdaterService $customUpdater)
    {
        $this->customUpdater = $customUpdater;
        $this->storagePath = storage_path('app/self-updater');
        $this->backupPath = storage_path('app/version-backups');
        $this->basePath = base_path();
        $this->excludeFolders = config('self-update.exclude_folders', [
            '__MACOSX',
            'node_modules',
            'bootstrap/cache',
            'bower',
            'storage/app',
            'storage/framework',
            'storage/logs',
            'storage/self-update',
            'vendor',
        ]);
        
        // Ensure storage directories exist with proper permissions
        if (!File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0775, true);
        } else {
            chmod($this->storagePath, 0775);
        }
        
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0775, true);
        } else {
            chmod($this->backupPath, 0775);
        }
    }
    
    /**
     * Download a specific version from GitHub
     *
     * @param string $version The version to download
     * @return string|null Path to downloaded file
     */
    public function downloadVersion(string $version): ?string
    {
        try {
            // Get repository configuration
            $config = config('self-update.repository_types.github');
            
            // Build GitHub API URL for the specific release
            $url = "https://api.github.com/repos/{$config['repository_vendor']}/{$config['repository_name']}/releases/tags/{$version}";
            
            // Prepare headers
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater',
                'Cache-Control' => 'no-cache'
            ];
            
            // Add authentication token if available
            if (!empty($config['private_access_token'])) {
                $headers['Authorization'] = 'token ' . $config['private_access_token'];
            }
            
            Log::info("Downloading version {$version} from GitHub");
            
            // Get release information
            $response = Http::withHeaders($headers)->get($url);
            
            if (!$response->successful()) {
                Log::error("Failed to get release info for version {$version}: " . $response->status());
                return null;
            }
            
            $releaseInfo = $response->json();
            
            // Define the file path
            $zipFilePath = $this->storagePath . '/' . $version . '.zip';
            
            // Check if there are any release assets (like pre-packaged zip files)
            $downloadUrl = null;
            $assets = $releaseInfo['assets'] ?? [];
            foreach ($assets as $asset) {
                // Look for zip files in the assets
                if (isset($asset['name']) && isset($asset['browser_download_url']) && 
                    (str_ends_with(strtolower($asset['name']), '.zip') || 
                     str_contains(strtolower($asset['name']), $version))) {
                    $downloadUrl = $asset['browser_download_url'];
                    Log::info("Found release asset: {$asset['name']}");
                    break;
                }
            }
            
            // If no assets found, fall back to zipball URL
            if (!$downloadUrl) {
                $downloadUrl = $releaseInfo['zipball_url'] ?? null;
                Log::info("No release assets found, using zipball URL instead");
            }
            
            if (!$downloadUrl) {
                Log::error("No download URL found for version {$version}");
                return null;
            }
            
            // Download the zip file
            $zipResponse = Http::withHeaders($headers)->get($downloadUrl);
            
            if (!$zipResponse->successful()) {
                Log::error("Failed to download zip for version {$version}: " . $zipResponse->status());
                return null;
            }
            
            // Save the zip file
            File::put($zipFilePath, $zipResponse->body());
            
            Log::info("Version {$version} downloaded successfully to {$zipFilePath}");
            
            return $zipFilePath;
            
        } catch (\Exception $e) {
            Log::error("Error downloading version {$version}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a backup of the current version before updating
     *
     * @return string|null Path to backup file
     */
    public function createBackup(): ?string
    {
        try {
            $currentVersion = config('self-update.version_installed', '1.0.0');
            Log::info("Creating backup of version {$currentVersion} before updating");
            
            // Create a timestamp for the backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $backupFilename = "backup_{$currentVersion}_{$timestamp}.zip";
            $backupPath = $this->backupPath . '/' . $backupFilename;
            
            // Ensure backup directory is writable
            if (!is_writable(dirname($backupPath))) {
                chmod(dirname($backupPath), 0775);
                Log::info("Updated permissions on backup directory");
            }
            
            // Create a new ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                Log::error("Failed to create backup archive");
                return null;
            }
            
            // Add files to the zip archive
            $this->addFilesToZip($zip, $this->basePath, '');
            
            // Close the archive
            if (!$zip->close()) {
                Log::error("Failed to close ZIP archive: " . $zip->getStatusString());
                return null;
            }
            
            if (File::exists($backupPath)) {
                // Ensure the created file has proper permissions
                chmod($backupPath, 0664);
                Log::info("Backup created successfully at {$backupPath}");
                return $backupPath;
            } else {
                Log::error("Failed to create backup file");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Error creating backup: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add files to a ZIP archive, respecting exclusions
     *
     * @param ZipArchive $zip
     * @param string $sourceDir
     * @param string $relativePath
     */
    protected function addFilesToZip(ZipArchive $zip, string $sourceDir, string $relativePath): void
    {
        $sourceDir = rtrim($sourceDir, '/\\') . DIRECTORY_SEPARATOR;
        $files = File::allFiles($sourceDir . $relativePath);
        
        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $relativePart = str_replace($sourceDir, '', $filePath);
            
            // Skip excluded folders
            $shouldExclude = false;
            foreach ($this->excludeFolders as $excludeFolder) {
                if (strpos($relativePart, $excludeFolder . DIRECTORY_SEPARATOR) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                continue;
            }
            
            // Add file to zip
            $zip->addFile($filePath, $relativePart);
        }
    }
    
    /**
     * Deploy a specific version from a downloaded zip
     *
     * @param string $version
     * @return bool
     */
    public function deployVersion(string $version): bool
    {
        $zipFilePath = $this->storagePath . '/' . $version . '.zip';
        
        if (!File::exists($zipFilePath)) {
            Log::error("Cannot deploy version {$version}: Zip file not found");
            return false;
        }
        
        try {
            Log::info("Starting deployment of version {$version}");
            
            // Create a backup of the current version before updating
            $backupPath = $this->createBackup();
            if (!$backupPath) {
                Log::warning("Failed to create backup before updating to version {$version}");
                // Continue with update even if backup fails, but log warning
            } else {
                Log::info("Backup created successfully before updating to version {$version}");
            }
            
            // Create a temporary extraction directory
            $extractPath = $this->storagePath . '/extract_' . time();
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0775, true);
            }
            
            // Extract the zip file
            $zip = new ZipArchive;
            $openResult = $zip->open($zipFilePath);
            
            if ($openResult !== true) {
                Log::error("Failed to open zip file for version {$version}: Error code {$openResult}");
                return false;
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // GitHub archives have a root folder containing all files
            // Get the first directory in the extract path
            $directories = File::directories($extractPath);
            if (empty($directories)) {
                Log::error("No root directory found in zip for version {$version}");
                File::deleteDirectory($extractPath);
                return false;
            }
            
            $sourceDir = $directories[0];
            
            // Put the application into maintenance mode
            // Fix for '--message' option error - use with() method instead
            Artisan::call('down', ['--render' => "Upgrading to version {$version}"]);
            
            // Copy files to base directory, respecting exclusions
            $this->copyFiles($sourceDir, $this->basePath);
            
            // Update the installed version in the .env file
            $this->updateInstalledVersion($version);
            
            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            // Run migrations if they exist
            if (File::exists($this->basePath . '/database/migrations')) {
                Artisan::call('migrate', ['--force' => true]);
            }
            
            // Clean up extraction directory
            File::deleteDirectory($extractPath);
            
            // Clean up the downloaded ZIP file
            if (File::exists($zipFilePath)) {
                File::delete($zipFilePath);
            }
            
            // Mark the version as current in the database
            $this->markVersionAsCurrent($version);
            
            // Bring the application back online
            Artisan::call('up');
            
            Log::info("Version {$version} deployed successfully");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error deploying version {$version}: " . $e->getMessage());
            
            // Try to bring the application back online if an error occurs
            try {
                Artisan::call('up');
            } catch (\Exception $ex) {
                // Just log the error, don't throw it
                Log::error("Failed to bring application back online: " . $ex->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Copy files from source to destination, respecting exclusions
     */
    public function copyFiles(string $source, string $destination): void
    {
        $source = rtrim($source, '/\\') . DIRECTORY_SEPARATOR;
        $destination = rtrim($destination, '/\\') . DIRECTORY_SEPARATOR;
        
        $files = File::allFiles($source);
        
        foreach ($files as $file) {
            $relativePath = str_replace($source, '', $file->getPathname());
            
            // Skip excluded folders
            $shouldExclude = false;
            foreach ($this->excludeFolders as $excludeFolder) {
                if (strpos($relativePath, $excludeFolder . DIRECTORY_SEPARATOR) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                continue;
            }
            
            $destPath = $destination . $relativePath;
            $destDir = dirname($destPath);
            
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            
            File::copy($file->getPathname(), $destPath);
        }
    }
    
    /**
     * Update the installed version in the .env file
     */
    public function updateInstalledVersion(string $version): void
    {
        $envFilePath = base_path('.env');
        $envContent = File::get($envFilePath);
        
        if (preg_match('/SELF_UPDATER_VERSION_INSTALLED=(.*)/', $envContent)) {
            // Update existing variable
            $envContent = preg_replace(
                '/SELF_UPDATER_VERSION_INSTALLED=(.*)/',
                'SELF_UPDATER_VERSION_INSTALLED=' . $version,
                $envContent
            );
        } else {
            // Add new variable
            $envContent .= "\nSELF_UPDATER_VERSION_INSTALLED={$version}\n";
        }
        
        File::put($envFilePath, $envContent);
        
        // Update config value for current request
        config(['self-update.version_installed' => $version]);
    }
    
    /**
     * Mark a version as current in the database
     */
    public function markVersionAsCurrent(string $version): void
    {
        // Reset all versions to not current
        SystemVersion::where('is_current', true)->update(['is_current' => false]);
        
        // Find and mark the deployed version as current
        $versionRecord = SystemVersion::where('version', $version)->first();
        
        if ($versionRecord) {
            $versionRecord->is_current = true;
            $versionRecord->save();
        } else {
            // Create a new version record if it doesn't exist
            SystemVersion::create([
                'version' => $version,
                'name' => "Version {$version}",
                'description' => "Deployed version",
                'is_current' => true,
                'released_at' => now(),
            ]);
        }
    }
} 