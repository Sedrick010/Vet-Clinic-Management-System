<?php

// Test GitHub API for releases
$vendor = 'Sedrick010';
$repo = 'Vet-Clinic-Management-System';
$apiUrl = "https://api.github.com/repos/{$vendor}/{$repo}/releases";

// Set up cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/vnd.github.v3+json',
    'User-Agent: PHP Laravel Self-Updater'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Output results
echo "Status Code: $httpCode\n\n";

if ($error) {
    echo "Error: $error\n";
} else {
    echo "Response:\n";
    $data = json_decode($response);
    echo "Raw response: " . $response . "\n\n";
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg() . "\n";
    } else {
        echo "Decoded response:\n";
        print_r($data);
    }
} 