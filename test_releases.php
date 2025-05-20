<?php

// Test script to verify sorting of GitHub releases by date

require __DIR__.'/vendor/autoload.php';

// Configure Laravel app
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the custom updater service
$customUpdater = app(\App\Services\CustomUpdaterService::class);

echo "TESTING GITHUB RELEASES SORTING\n";
echo "==============================\n\n";

echo "Current version: " . config('self-update.version_installed', 'unknown') . "\n\n";

// Get the latest version
echo "Checking latest version...\n";
$latestVersion = $customUpdater->getLatestVersion();
echo "Latest version from GitHub API: " . ($latestVersion ?? 'unknown') . "\n\n";

// Get all releases
echo "Getting all releases (sorted by date)...\n";
$allReleases = $customUpdater->getAllReleases(20);

if (is_array($allReleases) && count($allReleases) > 0) {
    echo "Found " . count($allReleases) . " releases:\n";
    echo "---------------------------------\n";
    
    foreach ($allReleases as $index => $release) {
        $version = $release['version'] ?? 'unknown';
        $name = $release['name'] ?? 'Unnamed';
        $publishedAt = $release['published_at'] ?? 'Unknown date';
        
        echo ($index + 1) . ". {$version} - {$name} - {$publishedAt}\n";
    }
    
    // Display a comparison with database records
    echo "\n\nComparing with database records...\n";
    echo "---------------------------------\n";
    
    $dbVersions = \App\Models\SystemVersion::orderBy('released_at', 'desc')->get();
    
    if ($dbVersions->isEmpty()) {
        echo "No versions found in database.\n";
    } else {
        echo "Found " . $dbVersions->count() . " versions in database:\n";
        
        foreach ($dbVersions as $version) {
            $current = $version->is_current ? ' (CURRENT)' : '';
            echo "- {$version->version}{$current} - {$version->name} - Released: {$version->released_at->format('Y-m-d H:i:s')}\n";
        }
    }
    
    // Check if the latest version from GitHub is in the database
    $latestGitHubVersion = $allReleases[0]['version'] ?? null;
    if ($latestGitHubVersion) {
        $existsInDb = \App\Models\SystemVersion::where('version', $latestGitHubVersion)->exists();
        echo "\nLatest GitHub version {$latestGitHubVersion} " . ($existsInDb ? "EXISTS" : "DOES NOT EXIST") . " in database.\n";
    }
} else {
    echo "No releases found or an error occurred.\n";
}

echo "\nTest complete.\n"; 