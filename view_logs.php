<?php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Default to admin logger
$tenantId = null;
$isAdmin = true;

// Parse command line options
$options = getopt('t:', ['tenant:', 'admin', 'clear', 'test', 'list-tenants']);
$numericArg = null;

// Process non-option arguments for line count
foreach ($argv as $arg) {
    if (is_numeric($arg)) {
        $numericArg = (int)$arg;
        break;
    }
}

// Check if we should list tenants with logs
if (isset($options['list-tenants'])) {
    listTenantLogs();
    exit;
}

// Check if we want tenant logs
if (isset($options['t']) || isset($options['tenant'])) {
    $tenantId = $options['t'] ?? $options['tenant'];
    $isAdmin = false;
}

// Get the appropriate logger
$logger = $isAdmin 
    ? new \App\Services\CustomLogger() 
    : \App\Services\CustomLogger::forTenant($tenantId);

// Show which log we're accessing
echo "Using " . ($isAdmin ? "admin" : "tenant (ID: $tenantId)") . " log: " . $logger->getLogFilePath() . "\n\n";

// Check if we should clear the logs
if (isset($options['clear'])) {
    $logger->clearLog();
    echo "Log file cleared.\n";
    exit;
}

// Check if we should add a test log entry
if (isset($options['test'])) {
    $logger->info("This is a test log entry");
    $logger->warning("This is a warning test");
    $logger->error("This is an error test");
    $logger->debug("This is a debug test");
    echo "Test log entries added.\n";
}

// Determine number of lines to display
$lines = $numericArg ?? 0; // 0 means all lines

// Get and display the log contents
$logContents = $logger->getLogContents($lines);

if (empty($logContents)) {
    echo "No logs found.\n";
} else {
    echo "=== LOG CONTENTS ===\n";
    echo $logContents;
    echo "===================\n";
}

// List available tenant logs
function listTenantLogs() {
    $tenantLogDir = storage_path('logs/tenants');
    
    if (!is_dir($tenantLogDir)) {
        echo "No tenant logs found.\n";
        return;
    }
    
    $dirs = glob($tenantLogDir . '/*', GLOB_ONLYDIR);
    
    if (empty($dirs)) {
        echo "No tenant logs found.\n";
    } else {
        echo "Available tenant logs:\n";
        foreach ($dirs as $dir) {
            $tenantId = basename($dir);
            $logFile = $dir . '/tenant.log';
            
            if (file_exists($logFile)) {
                $size = filesize($logFile);
                $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
                echo "- Tenant ID: $tenantId (Size: " . formatBytes($size) . ", Last modified: $lastModified)\n";
            }
        }
    }
    
    echo "\nAdmin log: " . storage_path('logs/admin.log') . "\n";
    if (file_exists(storage_path('logs/admin.log'))) {
        $size = filesize(storage_path('logs/admin.log'));
        $lastModified = date('Y-m-d H:i:s', filemtime(storage_path('logs/admin.log')));
        echo "  Size: " . formatBytes($size) . ", Last modified: $lastModified\n";
    }
}

// Format bytes to human-readable format
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Display usage instructions
echo "\nUsage:\n";
echo "  php view_logs.php                 - View all admin logs\n";
echo "  php view_logs.php 10              - View last 10 admin log entries\n";
echo "  php view_logs.php -t TENANT_ID    - View logs for a specific tenant\n";
echo "  php view_logs.php --tenant=TENANT_ID 10  - View last 10 log entries for a tenant\n";
echo "  php view_logs.php --admin         - View admin logs (default)\n";
echo "  php view_logs.php --test          - Add test log entries to current log\n";
echo "  php view_logs.php --clear         - Clear the current log file\n";
echo "  php view_logs.php --list-tenants  - List available tenant logs\n"; 