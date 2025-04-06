<?php

// Get database configuration from .env
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'vetclinicv2_new';
$dbUsername = 'root';
$dbPassword = '';

// Read the actual values from .env if possible
if (file_exists('.env')) {
    $envFile = file_get_contents('.env');
    
    preg_match('/DB_HOST=(.*)/', $envFile, $matches);
    $dbHost = trim($matches[1] ?? $dbHost);
    
    preg_match('/DB_PORT=(.*)/', $envFile, $matches);
    $dbPort = trim($matches[1] ?? $dbPort);
    
    preg_match('/DB_DATABASE=(.*)/', $envFile, $matches);
    $dbName = trim($matches[1] ?? $dbName);
    
    preg_match('/DB_USERNAME=(.*)/', $envFile, $matches);
    $dbUsername = trim($matches[1] ?? $dbUsername);
    
    preg_match('/DB_PASSWORD=(.*)/', $envFile, $matches);
    $dbPassword = trim($matches[1] ?? $dbPassword);
}

echo "Creating database using the following configuration:\n";
echo "Host: $dbHost\n";
echo "Port: $dbPort\n";
echo "Database: $dbName\n";
echo "Username: $dbUsername\n";
echo "Password: " . ($dbPassword ? "[HIDDEN]" : "[EMPTY]") . "\n\n";

// Try to use mysqli
if (extension_loaded('mysqli')) {
    try {
        echo "Connecting to MySQL server using mysqli...\n";
        $mysqli = new mysqli($dbHost, $dbUsername, $dbPassword, '', (int)$dbPort);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        echo "Dropping database $dbName if exists...\n";
        $mysqli->query("DROP DATABASE IF EXISTS `$dbName`");
        
        echo "Creating database $dbName...\n";
        if ($mysqli->query("CREATE DATABASE `$dbName`")) {
            echo "Database created successfully.\n";
            echo "You can now run: php artisan migrate\n";
        } else {
            echo "Error creating database: " . $mysqli->error . "\n";
        }
        
        $mysqli->close();
    } catch (Exception $e) {
        echo "Database creation failed (mysqli): " . $e->getMessage() . "\n";
    }
} else {
    echo "MySQLi extension is not available. Please install it or use another method to create your database.\n";
} 