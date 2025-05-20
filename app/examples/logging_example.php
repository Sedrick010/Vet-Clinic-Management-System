<?php

/**
 * This is an example file showing how to use the CustomLogger in your application
 * with support for separate admin and tenant logs
 */

// Method 1: Using the facade (recommended)
use App\Facades\CustomLog;

//----------------
// ADMIN LOGGING
//----------------

// Default logger is admin logger
CustomLog::info('This is an admin info message');
CustomLog::warning('This is an admin warning message');
CustomLog::error('This is an admin error message');
CustomLog::debug('This is an admin debug message');

// Explicitly use admin logger
CustomLog::admin()->info('This is an explicit admin info message');

// Log with context data
CustomLog::info('Admin action performed', [
    'user_id' => 1, 
    'action' => 'settings_changed'
]);

//----------------
// TENANT LOGGING
//----------------

// Log to a specific tenant's log file
CustomLog::tenant('tenant_123')->info('This is a tenant-specific message');
CustomLog::tenant('tenant_123')->error('Error in tenant system', [
    'module' => 'appointments',
    'error_code' => 'ERR_123'
]);

// If tenant ID is in session, it will use the current tenant automatically
// Assuming session('current_clinic_id') is set:
CustomLog::tenant()->info('This uses the current tenant from session');

//----------------
// Other Methods
//----------------

// Method 2: Using dependency injection
use App\Services\CustomLogger;

class SomeClass
{
    protected $adminLogger;
    protected $tenantLogger;
    
    public function __construct(CustomLogger $adminLogger)
    {
        $this->adminLogger = $adminLogger;
        
        // For a specific tenant
        $this->tenantLogger = CustomLogger::forTenant('tenant_456');
    }
    
    public function someAdminMethod()
    {
        $this->adminLogger->info('This is a log message for admin');
    }
    
    public function someTenantMethod()
    {
        $this->tenantLogger->info('This is a log message for tenant_456');
    }
}

// Method 3: Using the app container
$adminLogger = app('admin-logger');
$adminLogger->info('This is an admin log message using the app container');

$tenantLogger = app('tenant-logger', ['tenant_id' => 'tenant_789']);
$tenantLogger->info('This is a tenant log message using the app container');

// Method 4: Using the class directly
$adminCustomLogger = new CustomLogger();
$adminCustomLogger->info('This is an admin log message using the class directly');

$tenantCustomLogger = CustomLogger::forTenant('tenant_abc');
$tenantCustomLogger->info('This is a tenant log message using the class directly');

/**
 * Viewing logs
 * 
 * You can view logs in several ways:
 * 
 * 1. Using the view_logs.php script in the root directory:
 *    - View admin logs: php view_logs.php
 *    - View tenant logs: php view_logs.php -t tenant_123
 *    - List all tenant logs: php view_logs.php --list-tenants
 * 
 * 2. Using the CustomLog facade:
 */

// Get admin log contents
$adminLogContents = CustomLog::admin()->getLogContents();

// Get tenant log contents (limited to 20 lines)
$tenantLogContents = CustomLog::tenant('tenant_123')->getLogContents(20);

/**
 * Checking which log type you're using
 */
if (CustomLog::admin()->isTenantLogger()) {
    // This won't execute - admin logger is not a tenant logger
    echo "This is a tenant logger";
} else {
    // This will execute
    echo "This is the admin logger";
}

if (CustomLog::tenant('tenant_123')->isTenantLogger()) {
    // This will execute
    echo "This is a tenant logger";
}

/**
 * Clearing logs
 */
// CustomLog::clearLog(); // Uncomment to clear logs 