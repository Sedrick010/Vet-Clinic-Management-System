<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomUpdaterService
{
    protected $config;
    
    public function __construct()
    {
        $this->config = config('self-update.repository_types.github');
    }
    
    /**
     * Check if a new version is available
     *
     * @return bool
     */
    public function isNewVersionAvailable(): bool
    {
        try {
            $currentVersion = config('self-update.version_installed');
            $latestVersion = $this->getLatestVersion();
            
            if (!$currentVersion || !$latestVersion) {
                return false;
            }
            
            // Remove the 'v' prefix if it exists for comparison
            $currentVersion = ltrim($currentVersion, 'v');
            $latestVersion = ltrim($latestVersion, 'v');
            
            return version_compare($currentVersion, $latestVersion, '<');
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the latest version from GitHub releases
     *
     * @return string|null
     */
    public function getLatestVersion(): ?string
    {
        try {
            $url = 'https://api.github.com/repos/' . 
                   $this->config['repository_vendor'] . 
                   '/' . 
                   $this->config['repository_name'] . 
                   '/releases';
            
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater',
                'Cache-Control' => 'no-cache'
            ];
            
            // Add authentication token if available
            if (!empty($this->config['private_access_token'])) {
                $headers['Authorization'] = 'token ' . $this->config['private_access_token'];
            }
            
            $response = Http::withHeaders($headers)->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases) && count($releases) > 0) {
                    return $releases[0]['tag_name'] ?? null;
                }
            } else {
                Log::warning('GitHub API request failed with status code: ' . $response->status());
                Log::warning('GitHub API response: ' . $response->body());
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting latest version: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get details about the latest version
     *
     * @return array|null
     */
    public function getLatestVersionDetails(): ?array
    {
        try {
            $url = 'https://api.github.com/repos/' . 
                   $this->config['repository_vendor'] . 
                   '/' . 
                   $this->config['repository_name'] . 
                   '/releases';
            
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater',
                'Cache-Control' => 'no-cache'
            ];
            
            // Add authentication token if available
            if (!empty($this->config['private_access_token'])) {
                $headers['Authorization'] = 'token ' . $this->config['private_access_token'];
            }
            
            $response = Http::withHeaders($headers)->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases) && count($releases) > 0) {
                    $latestRelease = $releases[0];
                    
                    // Parse description to get more information
                    $description = $latestRelease['body'] ?? '';
                    $is_critical = false;
                    $is_security = false;
                    $is_mandatory = false;
                    $features = [];
                    $bug_fixes = [];
                    
                    // Check for critical/security/mandatory markers in the description
                    $is_critical = stripos($description, '[critical]') !== false;
                    $is_security = stripos($description, '[security]') !== false;
                    $is_mandatory = stripos($description, '[mandatory]') !== false;
                    
                    // Extract features and bug fixes
                    if (preg_match('/## Features(.*?)(?:##|\z)/s', $description, $matches)) {
                        $featureText = trim($matches[1]);
                        $features = array_filter(array_map('trim', explode("\n", $featureText)));
                    }
                    
                    if (preg_match('/## Bug Fixes(.*?)(?:##|\z)/s', $description, $matches)) {
                        $bugFixText = trim($matches[1]);
                        $bug_fixes = array_filter(array_map('trim', explode("\n", $bugFixText)));
                    }
                    
                    return [
                        'version' => $latestRelease['tag_name'] ?? null,
                        'name' => $latestRelease['name'] ?? null,
                        'description' => $description,
                        'zip_url' => $latestRelease['zipball_url'] ?? null,
                        'published_at' => $latestRelease['published_at'] ?? null,
                        'is_critical' => $is_critical,
                        'is_security' => $is_security,
                        'is_mandatory' => $is_mandatory,
                        'features' => $features,
                        'bug_fixes' => $bug_fixes,
                    ];
                }
            } else {
                Log::warning('GitHub API request failed with status code: ' . $response->status());
                Log::warning('GitHub API response: ' . $response->body());
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting latest version details: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all available releases
     * 
     * @param int $limit Number of releases to fetch
     * @return array
     */
    public function getAllReleases(int $limit = 5): array
    {
        try {
            $url = 'https://api.github.com/repos/' . 
                   $this->config['repository_vendor'] . 
                   '/' . 
                   $this->config['repository_name'] . 
                   '/releases';
            
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater',
                'Cache-Control' => 'no-cache'
            ];
            
            // Add authentication token if available
            if (!empty($this->config['private_access_token'])) {
                $headers['Authorization'] = 'token ' . $this->config['private_access_token'];
            }
            
            $response = Http::withHeaders($headers)->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases)) {
                    $formattedReleases = [];
                    $count = 0;
                    
                    foreach ($releases as $release) {
                        if ($count >= $limit) break;
                        
                        // Parse description to get more information
                        $description = $release['body'] ?? '';
                        $is_critical = false;
                        $is_security = false;
                        $is_mandatory = false;
                        $features = [];
                        $bug_fixes = [];
                        
                        // Check for critical/security/mandatory markers in the description
                        $is_critical = stripos($description, '[critical]') !== false;
                        $is_security = stripos($description, '[security]') !== false;
                        $is_mandatory = stripos($description, '[mandatory]') !== false;
                        
                        // Extract features and bug fixes
                        if (preg_match('/## Features(.*?)(?:##|\z)/s', $description, $matches)) {
                            $featureText = trim($matches[1]);
                            $features = array_filter(array_map('trim', explode("\n", $featureText)));
                        }
                        
                        if (preg_match('/## Bug Fixes(.*?)(?:##|\z)/s', $description, $matches)) {
                            $bugFixText = trim($matches[1]);
                            $bug_fixes = array_filter(array_map('trim', explode("\n", $bugFixText)));
                        }
                        
                        $formattedReleases[] = [
                            'version' => $release['tag_name'] ?? null,
                            'name' => $release['name'] ?? null,
                            'description' => $description,
                            'zip_url' => $release['zipball_url'] ?? null,
                            'published_at' => $release['published_at'] ?? null,
                            'is_prerelease' => $release['prerelease'] ?? false,
                            'is_critical' => $is_critical,
                            'is_security' => $is_security,
                            'is_mandatory' => $is_mandatory,
                            'features' => $features,
                            'bug_fixes' => $bug_fixes,
                        ];
                        
                        $count++;
                    }
                    
                    return $formattedReleases;
                }
            } else {
                Log::warning('GitHub API request failed with status code: ' . $response->status());
                Log::warning('GitHub API response: ' . $response->body());
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting all releases: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Force refresh check for updates
     *
     * @return bool
     */
    public function forceRefreshUpdateCheck(): bool
    {
        // Clear any cached items first to force a fresh check
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear Laravel's config cache
        \Artisan::call('config:clear');
        
        return $this->isNewVersionAvailable();
    }

    /**
     * Download the latest update package
     * 
     * @return string|null Path to the downloaded package
     */
    public function downloadLatestUpdate(): ?string
    {
        try {
            $latestVersionDetails = $this->getLatestVersionDetails();
            
            if (!$latestVersionDetails || empty($latestVersionDetails['zip_url'])) {
                Log::error('No download URL found for the latest version');
                return null;
            }
            
            $zipUrl = $latestVersionDetails['zip_url'];
            $version = $latestVersionDetails['version'] ?? 'latest';
            
            // Ensure download directory exists
            $downloadPath = $this->config['download_path'] ?? storage_path('self-updater');
            if (!file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }
            
            // Set file name for the download
            $fileName = $downloadPath . DIRECTORY_SEPARATOR . 'update-' . $version . '.zip';
            
            // Prepare headers for GitHub API
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater',
            ];
            
            // Add authentication token if available
            if (!empty($this->config['private_access_token'])) {
                $headers['Authorization'] = 'token ' . $this->config['private_access_token'];
            }
            
            // Download the file
            $response = Http::withHeaders($headers)->get($zipUrl);
            
            if ($response->successful()) {
                file_put_contents($fileName, $response->body());
                Log::info("Update package downloaded to: {$fileName}");
                return $fileName;
            } else {
                Log::error("Failed to download update: HTTP status " . $response->status());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error downloading update: ' . $e->getMessage());
            return null;
        }
    }
} 