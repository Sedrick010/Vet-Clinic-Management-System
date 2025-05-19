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
            '.git',
            '.github',
            'public/storage',
            'public/uploads',
            '.env',
            '.env.backup',
            '.env.example',
            '.DS_Store',
            'phpunit.xml',
            '*.log',
            'tests',
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
        // Prevent timeout for long-running updates
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        $zipFilePath = $this->storagePath . '/' . $version . '.zip';
        
        if (!File::exists($zipFilePath)) {
            Log::error("Cannot deploy version {$version}: Zip file not found");
            return false;
        }
        
        try {
            $currentVersion = config('self-update.version_installed', '1.0.0');
            Log::info("Starting deployment: Moving from version {$currentVersion} to {$version}");
            
            // Check if we're updating or downgrading
            $isUpdate = version_compare($version, $currentVersion, '>');
            $action = $isUpdate ? 'update' : 'downgrade';
            
            // Create a backup of the current version before changing
            $backupPath = $this->createBackup();
            if (!$backupPath) {
                Log::warning("Failed to create backup before {$action} to version {$version}");
                // Continue with deployment even if backup fails, but log warning
            } else {
                Log::info("Backup created successfully at {$backupPath} before {$action} to version {$version}");
            }
            
            // Create a temporary extraction directory with a unique name to avoid conflicts
            $extractionId = time() . '_' . rand(1000, 9999);
            $extractPath = $this->storagePath . '/extract_' . $extractionId;
            
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0775, true);
            }
            
            // Extract the zip file
            Log::info("Extracting version {$version} zip file to {$extractPath}");
            $zip = new ZipArchive;
            $openResult = $zip->open($zipFilePath);
            
            if ($openResult !== true) {
                Log::error("Failed to open zip file for version {$version}: Error code {$openResult}");
                return false;
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Detect the source code root
            Log::info("Detecting source code root in extracted files");
            $sourceDir = $this->detectSourceCodeRoot($extractPath);
            Log::info("Source code root detected: {$sourceDir}");
            
            if (!File::exists($sourceDir)) {
                Log::error("Source directory not found: {$sourceDir}");
                $this->cleanupTempFiles($extractPath, $zipFilePath);
                return false;
            }
            
            // Put the application into maintenance mode
            Log::info("Putting application into maintenance mode for {$action} to version {$version}");
            Artisan::call('down', [
                '--render' => "{$action} to version {$version}",
                '--refresh' => 15,  // Auto refresh the page every 15 seconds
                '--secret' => 'update-session-' . md5(time()),  // Add a bypass token
                '--status' => 503
            ]);
            
            // Copy files to base directory, respecting exclusions
            Log::info("Copying files from {$sourceDir} to base directory");
            $this->copyFilesEnhanced($sourceDir, $this->basePath);
            
            // Update the installed version in the .env file
            Log::info("Updating installed version in .env file to {$version}");
            $this->updateInstalledVersion($version);
            
            // Clear all caches to avoid stale configuration/views
            Log::info("Clearing application caches");
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            // Run migrations for the main database
            Log::info("Running migrations for main database");
            Artisan::call('migrate', ['--force' => true]);
            
            // Run migrations for all tenant databases
            Log::info("Running migrations for all tenant databases");
            try {
                Artisan::call('migrate:all-tenants', ['--force' => true]);
                Log::info("Tenant migrations completed successfully");
            } catch (\Exception $e) {
                Log::error("Error running tenant migrations: " . $e->getMessage());
                // Continue with the deployment even if tenant migrations fail
            }
            
            // Update version identifiers in blade files
            Log::info("Updating version identifiers in blade files");
            $this->updateVersionIdentifiers($version);
            
            // Clean up extraction directory and downloaded zip
            $this->cleanupTempFiles($extractPath, $zipFilePath);
            
            // Mark the version as current in the database
            Log::info("Marking version {$version} as current in the database");
            $this->markVersionAsCurrent($version);
            
            // Bring the application back online without destroying active sessions
            Log::info("Bringing application back online");
            Artisan::call('up', ['--no-interaction' => true]);
            
            Log::info("Version {$version} deployed successfully. {$action} complete.");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error deploying version {$version}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Try to bring the application back online if an error occurs
            try {
                Artisan::call('up');
                Log::info("Application brought back online after deployment error");
            } catch (\Exception $ex) {
                // Just log the error, don't throw it
                Log::error("Failed to bring application back online: " . $ex->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Enhanced copy files method with better exclusion handling and special file treatment
     * 
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function copyFilesEnhanced(string $source, string $destination): void
    {
        $source = rtrim($source, '/\\') . DIRECTORY_SEPARATOR;
        $destination = rtrim($destination, '/\\') . DIRECTORY_SEPARATOR;
        
        // Recursively iterate through all files and directories
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($source, '', $item->getPathname());
            $destPath = $destination . $relativePath;
            
            // Skip excluded paths
            if ($this->shouldExclude($relativePath)) {
                Log::debug("Skipping excluded path: {$relativePath}");
                continue;
            }
            
            if ($item->isDir()) {
                // Create directory if it doesn't exist
                if (!File::exists($destPath)) {
                    File::makeDirectory($destPath, 0755, true);
                    Log::debug("Created directory: {$destPath}");
                }
            } else {
                // Handle special files
                if (basename($relativePath) === 'artisan') {
                    // Make artisan executable
                    File::copy($item->getPathname(), $destPath);
                    chmod($destPath, 0755);
                    Log::debug("Copied artisan file with executable permissions: {$destPath}");
                } else {
                    // Copy regular file
                    File::copy($item->getPathname(), $destPath);
                    Log::debug("Copied file: {$destPath}");
                }
            }
        }
    }
    
    /**
     * Check if a path should be excluded from copying
     * 
     * @param string $path Relative path to check
     * @return bool
     */
    protected function shouldExclude(string $path): bool
    {
        $path = str_replace('\\', '/', $path); // Normalize path separators
        
        foreach ($this->excludeFolders as $exclude) {
            // Check for wildcard patterns
            if (strpos($exclude, '*') !== false) {
                $pattern = '#^' . str_replace(['.', '*'], ['\.', '.*'], $exclude) . '#i';
                if (preg_match($pattern, $path)) {
                    return true;
                }
            } 
            // Check for direct matches or folder prefixes
            else if ($path === $exclude || strpos($path, $exclude . '/') === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect the root directory of the application code in the extracted zip
     * 
     * @param string $extractPath The path where the zip was extracted
     * @return string The path to the actual application code root
     */
    protected function detectSourceCodeRoot(string $extractPath): string
    {
        // Check for direct extraction (no subdirectory)
        if (File::exists($extractPath . '/artisan') || 
            File::exists($extractPath . '/composer.json')) {
            Log::info("Found application files directly in extract root");
            return $extractPath;
        }
        
        // GitHub archives usually have a single subdirectory containing all files
        $subfolders = array_filter(glob($extractPath . '/*'), 'is_dir');
        
        if (count($subfolders) === 1) {
            // Check if this folder has typical Laravel application files
            $potentialRoot = $subfolders[0];
            
            if (File::exists($potentialRoot . '/artisan') || 
                File::exists($potentialRoot . '/composer.json')) {
                Log::info("Found application files in single subfolder");
                return $potentialRoot;
            }
        }
        
        // Check all subfolders for Laravel app structure
        foreach ($subfolders as $subfolder) {
            if (File::exists($subfolder . '/artisan') || 
                File::exists($subfolder . '/composer.json')) {
                Log::info("Found application files in subfolder");
                return $subfolder;
            }
            
            // Check for specific Laravel folders
            if (File::exists($subfolder . '/app') && 
                File::exists($subfolder . '/public') && 
                File::exists($subfolder . '/database')) {
                Log::info("Found Laravel directory structure");
                return $subfolder;
            }
        }
        
        // Fallback: return the first subfolder or extract path if no subfolders
        return count($subfolders) > 0 ? $subfolders[0] : $extractPath;
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
    
    /**
     * Clean up temporary files after deployment
     * 
     * @param string $extractPath
     * @param string $zipFilePath
     * @return void
     */
    protected function cleanupTempFiles(string $extractPath, string $zipFilePath): void
    {
        try {
            // Clean up extraction directory
            if (File::exists($extractPath)) {
                Log::info("Cleaning up extraction directory: {$extractPath}");
                File::deleteDirectory($extractPath);
            }
            
            // Clean up the downloaded ZIP file
            if (File::exists($zipFilePath)) {
                Log::info("Cleaning up downloaded zip file: {$zipFilePath}");
                File::delete($zipFilePath);
            }
            
            // Check for and clean up ANY old extraction directories
            $oldExtractDirs = File::glob($this->storagePath . '/extract_*');
            Log::info("Found " . count($oldExtractDirs) . " extraction directories to check for cleanup");
            
            foreach ($oldExtractDirs as $dir) {
                // Always clean up extract directories, regardless of age
                if ($dir !== $extractPath) { // Don't try to delete the current directory twice
                    Log::info("Cleaning up extraction directory: {$dir}");
                    File::deleteDirectory($dir);
                }
            }
            
            Log::info("Temporary files cleanup completed successfully");
        } catch (\Exception $e) {
            // Log but don't throw, as this is not critical
            Log::warning("Error cleaning up temporary files: " . $e->getMessage());
        }
    }
    
    /**
     * Update version identifiers in blade files
     * This helps visually verify that the update was successfully applied
     * 
     * @param string $version
     * @return void
     */
    public function updateVersionIdentifiers(string $version): void
    {
        try {
            // Clean the version number
            $version = ltrim($version, 'v');
            
            // Files to update identifiers in
            $files = [
                resource_path('views/system/versions/manage.blade.php'),
                resource_path('views/system/versions/roadmap.blade.php'),
                resource_path('views/system/versions/update-success.blade.php')
            ];
            
            foreach ($files as $file) {
                if (File::exists($file)) {
                    $content = File::get($file);
                    
                    // Update MANAGE-UI- version
                    $content = preg_replace(
                        '/VERSION IDENTIFIER: MANAGE-UI-v[0-9.]+/',
                        'VERSION IDENTIFIER: MANAGE-UI-v' . $version,
                        $content
                    );
                    
                    // Update ROADMAP-UI- version
                    $content = preg_replace(
                        '/VERSION IDENTIFIER: ROADMAP-UI-v[0-9.]+/',
                        'VERSION IDENTIFIER: ROADMAP-UI-v' . $version,
                        $content
                    );
                    
                    // Update SUCCESS-UI- version
                    $content = preg_replace(
                        '/VERSION IDENTIFIER: SUCCESS-UI-v[0-9.]+/',
                        'VERSION IDENTIFIER: SUCCESS-UI-v' . $version,
                        $content
                    );
                    
                    // Save the modified content
                    File::put($file, $content);
                    Log::info("Updated version identifiers in file: {$file}");
                }
            }
            
            Log::info("Successfully updated version identifiers to v{$version}");
        } catch (\Exception $e) {
            // Log but don't throw, as this is not critical
            Log::warning("Error updating version identifiers: " . $e->getMessage());
        }
    }
} 