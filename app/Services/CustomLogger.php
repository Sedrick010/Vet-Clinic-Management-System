<?php

namespace App\Services;

class CustomLogger
{
    protected $logFile;
    protected $tenantId;
    
    /**
     * Create a new logger instance
     *
     * @param string|null $logFile Custom log file path
     * @param string|null $tenantId Tenant ID for multi-tenant logging
     */
    public function __construct($logFile = null, $tenantId = null)
    {
        $this->tenantId = $tenantId;
        
        // If tenant ID is provided, store logs in a tenant-specific folder
        if ($tenantId) {
            $this->logFile = $logFile ?? storage_path("logs/tenants/{$tenantId}/tenant.log");
        } else {
            // Default admin log file
            $this->logFile = $logFile ?? storage_path('logs/admin.log');
        }
        
        // Create the log file if it doesn't exist
        if (!file_exists($this->logFile)) {
            $directory = dirname($this->logFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            touch($this->logFile);
        }
    }
    
    /**
     * Get a tenant-specific logger instance
     *
     * @param string $tenantId The tenant identifier
     * @return CustomLogger
     */
    public static function forTenant($tenantId)
    {
        return new static(null, $tenantId);
    }
    
    /**
     * Get an admin logger instance
     *
     * @return CustomLogger
     */
    public static function forAdmin()
    {
        return new static();
    }
    
    /**
     * Log a message with a timestamp
     *
     * @param string $message The message to log
     * @param string $level The log level (info, warning, error, debug)
     * @param array $context Additional context data to include
     * @return bool Whether the log was written successfully
     */
    public function log($message, $level = 'info', array $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $tenantInfo = $this->tenantId ? "[tenant:{$this->tenantId}]" : "[admin]";
        $logEntry = "[$timestamp] $tenantInfo [$level] $message$contextStr" . PHP_EOL;
        
        return file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Log an info message
     */
    public function info($message, array $context = [])
    {
        return $this->log($message, 'info', $context);
    }
    
    /**
     * Log a warning message
     */
    public function warning($message, array $context = [])
    {
        return $this->log($message, 'warning', $context);
    }
    
    /**
     * Log an error message
     */
    public function error($message, array $context = [])
    {
        return $this->log($message, 'error', $context);
    }
    
    /**
     * Log a debug message
     */
    public function debug($message, array $context = [])
    {
        return $this->log($message, 'debug', $context);
    }
    
    /**
     * Get the contents of the log file
     * 
     * @param int $lines Number of lines to return (0 for all)
     * @return string The log contents
     */
    public function getLogContents($lines = 0)
    {
        if (!file_exists($this->logFile)) {
            return '';
        }
        
        if ($lines <= 0) {
            return file_get_contents($this->logFile);
        }
        
        // Get the last N lines
        $file = new \SplFileObject($this->logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $result = [];
        
        $file->seek($startLine);
        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }
        
        return implode('', $result);
    }
    
    /**
     * Clear the log file
     */
    public function clearLog()
    {
        return file_put_contents($this->logFile, '');
    }
    
    /**
     * Get the path to the log file
     *
     * @return string
     */
    public function getLogFilePath()
    {
        return $this->logFile;
    }
    
    /**
     * Check if this is a tenant logger
     *
     * @return bool
     */
    public function isTenantLogger()
    {
        return $this->tenantId !== null;
    }
} 