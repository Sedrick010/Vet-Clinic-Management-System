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
            
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater'
            ])->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases) && count($releases) > 0) {
                    return $releases[0]['tag_name'] ?? null;
                }
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
            
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater'
            ])->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases) && count($releases) > 0) {
                    $latestRelease = $releases[0];
                    
                    return [
                        'version' => $latestRelease['tag_name'] ?? null,
                        'name' => $latestRelease['name'] ?? null,
                        'description' => $latestRelease['body'] ?? null,
                        'zip_url' => $latestRelease['zipball_url'] ?? null,
                        'published_at' => $latestRelease['published_at'] ?? null,
                    ];
                }
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
            
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'PHP Laravel Self-Updater'
            ])->get($url);
            
            if ($response->successful()) {
                $releases = $response->json();
                
                if (is_array($releases)) {
                    $formattedReleases = [];
                    $count = 0;
                    
                    foreach ($releases as $release) {
                        if ($count >= $limit) break;
                        
                        $formattedReleases[] = [
                            'version' => $release['tag_name'] ?? null,
                            'name' => $release['name'] ?? null,
                            'description' => $release['body'] ?? null,
                            'zip_url' => $release['zipball_url'] ?? null,
                            'published_at' => $release['published_at'] ?? null,
                            'is_prerelease' => $release['prerelease'] ?? false,
                        ];
                        
                        $count++;
                    }
                    
                    return $formattedReleases;
                }
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting all releases: ' . $e->getMessage());
            return [];
        }
    }
} 