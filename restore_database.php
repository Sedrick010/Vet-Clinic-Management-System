<?php
/**
 * VetClinic Database Restore Script
 * This script restores a database from a backup file
 * 
 * Usage: php restore_database.php database_name backup_file.sql
 */

// Check command line arguments
if ($argc < 3) {
    echo "Error: Missing required arguments\n";
    echo "Usage: php restore_database.php database_name backup_file.sql\n";
    exit(1);
}

$databaseName = $argv[1];
$backupFile = $argv[2];

// Check if backup file exists
if (!file_exists($backupFile)) {
    echo "Error: Backup file not found: {$backupFile}\n";
    exit(1);
}

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

// Set database credentials from .env
$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

// Set path to mysql (adjust if needed)
$mysqlPath = 'C:/xampp/mysql/bin/mysql.exe';

if (!file_exists($mysqlPath)) {
    die("Error: mysql client not found at $mysqlPath\n");
}

// Check if backup file is gzipped
if (substr($backupFile, -3) === '.gz') {
    echo "Detected gzipped backup file. Extracting...\n";
    $content = gzdecode(file_get_contents($backupFile));
    $tempFile = tempnam(sys_get_temp_dir(), 'sql_');
    file_put_contents($tempFile, $content);
    $backupFile = $tempFile;
    echo "Extracted to temporary file: {$backupFile}\n";
}

echo "Restoring database: {$databaseName} from backup: {$backupFile}\n";

// First, drop the existing database if it exists
$dropCommand = "\"{$mysqlPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser}" . 
           ($dbPass ? " --password={$dbPass}" : "") . 
           " -e \"DROP DATABASE IF EXISTS {$databaseName}; CREATE DATABASE {$databaseName};\"";

exec($dropCommand, $output, $returnVar);

if ($returnVar !== 0) {
    echo "Error: Failed to drop/recreate database\n";
    exit(1);
}

echo "Database dropped and recreated successfully\n";

// Restore from backup file
$restoreCommand = "\"{$mysqlPath}\" --host={$dbHost} --port={$dbPort} --user={$dbUser}" . 
               ($dbPass ? " --password={$dbPass}" : "") . 
               " {$databaseName} < \"{$backupFile}\"";

exec($restoreCommand, $output, $returnVar);

if ($returnVar !== 0) {
    echo "Error: Failed to restore database from backup\n";
    exit(1);
}

echo "Database restored successfully\n";

// Clean up temporary file if we extracted from a gzipped backup
if (isset($tempFile) && file_exists($tempFile)) {
    unlink($tempFile);
    echo "Temporary file removed\n";
}

echo "Restore process completed successfully\n"; 