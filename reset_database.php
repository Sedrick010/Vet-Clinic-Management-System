<?php

// Get database configuration from .env
$dbConnection = 'mysql';
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'vetclinicv2_new';
$dbUsername = 'root';
$dbPassword = '';

// Read the actual values from .env if possible
if (file_exists('.env')) {
    $envFile = file_get_contents('.env');
    preg_match('/DB_CONNECTION=(.*)/', $envFile, $matches);
    $dbConnection = $matches[1] ?? $dbConnection;
    
    preg_match('/DB_HOST=(.*)/', $envFile, $matches);
    $dbHost = $matches[1] ?? $dbHost;
    
    preg_match('/DB_PORT=(.*)/', $envFile, $matches);
    $dbPort = $matches[1] ?? $dbPort;
    
    preg_match('/DB_DATABASE=(.*)/', $envFile, $matches);
    $dbName = $matches[1] ?? $dbName;
    
    preg_match('/DB_USERNAME=(.*)/', $envFile, $matches);
    $dbUsername = $matches[1] ?? $dbUsername;
    
    preg_match('/DB_PASSWORD=(.*)/', $envFile, $matches);
    $dbPassword = $matches[1] ?? $dbPassword;
}

try {
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("$dbConnection:host=$dbHost;port=$dbPort", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Dropping database $dbName if exists...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`;");
    
    echo "Creating database $dbName...\n";
    $pdo->exec("CREATE DATABASE `$dbName`;");
    
    echo "Database reset successfully. You can now run: php artisan migrate\n";
} catch (PDOException $e) {
    echo "Database reset failed: " . $e->getMessage() . "\n";
    exit(1);
} 