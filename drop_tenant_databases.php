<?php

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Filter only the vetclinicv2 tenant databases
    $tenantDatabases = array_filter($databases, function($db) {
        return strpos($db, 'vetclinicv2_') === 0;
    });
    
    // Drop each tenant database
    $dropped = 0;
    foreach ($tenantDatabases as $db) {
        echo "Dropping database: $db\n";
        $pdo->exec("DROP DATABASE `$db`");
        $dropped++;
    }
    
    echo "\nSuccessfully dropped $dropped tenant databases.\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
} 