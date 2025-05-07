<?php

// Load Laravel environment
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get GitHub configuration
$config = config('self-update.repository_types.github');
$repoVendor = $config['repository_vendor'];
$repoName = $config['repository_name'];
$token = $config['private_access_token'];

echo "Checking connection to GitHub API for repository: {$repoVendor}/{$repoName}\n";
echo "Using token from config: " . (empty($token) ? "NONE (not set)" : substr($token, 0, 5) . "..." . substr($token, -5)) . "\n\n";

// Try fetching the releases directly
$url = "https://api.github.com/repos/{$repoVendor}/{$repoName}/releases";

$headers = [
    'Accept: application/vnd.github.v3+json',
    'User-Agent: PHP GitHub API Test',
    'Cache-Control: no-cache'
];

if (!empty($token)) {
    $headers[] = "Authorization: token {$token}";
}

// Initialize cURL session
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Response Code: {$httpCode}\n";

if ($error) {
    echo "Error: {$error}\n";
} else {
    if ($httpCode === 200) {
        $releases = json_decode($response, true);
        if (is_array($releases) && count($releases) > 0) {
            echo "Success! Found " . count($releases) . " releases.\n\n";
            echo "Latest release:\n";
            echo "  Tag: " . ($releases[0]['tag_name'] ?? 'N/A') . "\n";
            echo "  Name: " . ($releases[0]['name'] ?? 'N/A') . "\n";
            echo "  Published: " . ($releases[0]['published_at'] ?? 'N/A') . "\n";
            
            $currentVersion = config('self-update.version_installed');
            $latestVersion = ltrim($releases[0]['tag_name'] ?? '', 'v');
            
            echo "\nCurrent version: {$currentVersion}\n";
            echo "Latest version: {$latestVersion}\n";
            echo "Is new version available: " . (version_compare($currentVersion, $latestVersion, '<') ? 'YES' : 'NO') . "\n";
        } else {
            echo "No releases found or invalid response format.\n";
            echo "Response data: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : '') . "\n";
        }
    } else {
        echo "Error response: " . $response . "\n";
    }
} 