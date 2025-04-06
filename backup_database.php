<?php
/**
 * VetClinic Database Backup Script
 * This script backs up the main database and all tenant databases
 */

// Disable time limit for long-running operations
set_time_limit(0);

// Load environment variables from .env file
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || empty(trim($line))) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value, '"\'');
    }
} else {
    die("Error: .env file not found\n");
}

// Create backup directory
$backupDir = __DIR__ . '/database_backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Set database credentials from .env
$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';
$mainDb = $env['DB_DATABASE'] ?? 'vetclinicv2_new';

// Set path to mysqldump (adjust if needed)
$mysqldumpPath = 'C:/xampp/mysql/bin/mysqldump.exe';

if (!file_exists($mysqldumpPath)) {
    die("Error: mysqldump not found at $mysqldumpPath\n");
}

// Set timestamp for backup files
$timestamp = date('Y-m-d_H-i-s');

// Backup main database
echo "Backing up main database: $mainDb...\n";
$mainDbBackupFile = "$backupDir/{$mainDb}_{$timestamp}.sql";
$command = "\"{$mysqldumpPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser}" . 
           ($dbPass ? " --password={$dbPass}" : "") . 
           " --databases {$mainDb} --result-file=\"{$mainDbBackupFile}\"";

exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    echo "Error: Failed to backup main database\n";
} else {
    echo "Main database backed up successfully to: $mainDbBackupFile\n";
}

// Connect to the database to get tenant databases
try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$mainDb", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tenant databases
    $stmt = $pdo->query("SELECT id, name, database_name FROM clinics WHERE approval_status = 'approved'");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($tenants) . " tenant databases to backup\n";
    
    // Backup each tenant database
    foreach ($tenants as $tenant) {
        $tenantDb = $tenant['database_name'];
        $tenantName = $tenant['name'];
        
        echo "Backing up tenant database: $tenantDb ($tenantName)...\n";
        $tenantBackupFile = "$backupDir/{$tenantDb}_{$timestamp}.sql";
        
        $command = "\"{$mysqldumpPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser}" . 
                   ($dbPass ? " --password={$dbPass}" : "") . 
                   " --databases {$tenantDb} --result-file=\"{$tenantBackupFile}\"";
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            echo "Error: Failed to backup tenant database: $tenantDb\n";
        } else {
            echo "Tenant database backed up successfully to: $tenantBackupFile\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}

echo "All database backups completed\n";
echo "Backup files are stored in: $backupDir\n";

// Create a simple HTML report
$reportFile = "$backupDir/backup_report_{$timestamp}.html";
$reportContent = "<!DOCTYPE html>
<html>
<head>
    <title>VetClinic Database Backup Report - $timestamp</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #3366cc; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>VetClinic Database Backup Report</h1>
    <p>Backup completed on: " . date('Y-m-d H:i:s') . "</p>
    <h2>Backup Summary</h2>
    <p>Main database: <span class='success'>$mainDb</span></p>
    <p>Total tenant databases: " . count($tenants) . "</p>
    <p>Backup location: $backupDir</p>
    
    <h2>Tenant Databases</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Clinic Name</th>
            <th>Database Name</th>
            <th>Backup File</th>
        </tr>";

// Add main database to report
$reportContent .= "
        <tr>
            <td>Main</td>
            <td>VetClinic Central</td>
            <td>$mainDb</td>
            <td>" . basename($mainDbBackupFile) . "</td>
        </tr>";

// Add tenant databases to report
foreach ($tenants as $tenant) {
    $reportContent .= "
        <tr>
            <td>{$tenant['id']}</td>
            <td>{$tenant['name']}</td>
            <td>{$tenant['database_name']}</td>
            <td>{$tenant['database_name']}_{$timestamp}.sql</td>
        </tr>";
}

$reportContent .= "
    </table>
</body>
</html>";

file_put_contents($reportFile, $reportContent);
echo "Backup report created: $reportFile\n"; 