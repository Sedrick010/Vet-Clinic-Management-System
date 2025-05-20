<?php

// Script to update GitHub token in .env file
echo "This script will update your GitHub token in the .env file.\n";
echo "Paste your new GitHub token: ";
$handle = fopen("php://stdin", "r");
$newToken = trim(fgets($handle));
fclose($handle);

if (empty($newToken)) {
    echo "Error: Token cannot be empty.\n";
    exit(1);
}

// Path to .env file
$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "Error: .env file not found at {$envFile}.\n";
    exit(1);
}

// Read the current env file
$envContents = file_get_contents($envFile);

// Update the token - be careful to preserve other settings
$updatedContents = preg_replace(
    '/SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN=([^\r\n]+)/',
    'SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN=' . $newToken,
    $envContents
);

// Write the updated env file
if (file_put_contents($envFile, $updatedContents)) {
    echo "Successfully updated GitHub token in .env file.\n";
    echo "Running config:clear to apply changes...\n";
    
    // Clear config cache
    system('php artisan config:clear');
    
    echo "Done! Your token has been updated.\n";
} else {
    echo "Error: Failed to write to .env file. Check file permissions.\n";
    exit(1);
} 