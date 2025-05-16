<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool log(string $message, string $level = 'info', array $context = [])
 * @method static bool info(string $message, array $context = [])
 * @method static bool warning(string $message, array $context = [])
 * @method static bool error(string $message, array $context = [])
 * @method static bool debug(string $message, array $context = [])
 * @method static string getLogContents(int $lines = 0)
 * @method static bool clearLog()
 * @method static string getLogFilePath()
 * @method static bool isTenantLogger()
 * @method static \App\Services\CustomLogger forTenant(string $tenantId)
 * @method static \App\Services\CustomLogger forAdmin()
 * 
 * @see \App\Services\CustomLogger
 */
class CustomLog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'custom-logger';
    }
    
    /**
     * Get a logger for a specific tenant
     *
     * @param string $tenantId
     * @return \App\Services\CustomLogger
     */
    public static function tenant($tenantId = null)
    {
        if (!$tenantId && session()->has('current_clinic_id')) {
            $tenantId = session('current_clinic_id');
        }
        
        return app('tenant-logger', ['tenant_id' => $tenantId]);
    }
    
    /**
     * Get the admin logger
     *
     * @return \App\Services\CustomLogger
     */
    public static function admin()
    {
        return app('admin-logger');
    }
} 