<?php

namespace App\Providers;

use App\Services\CustomLogger;
use Illuminate\Support\ServiceProvider;

class CustomLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the admin logger as a singleton
        $this->app->singleton(CustomLogger::class, function ($app) {
            return new CustomLogger();
        });

        // Admin logger alias
        $this->app->alias(CustomLogger::class, 'custom-logger');
        $this->app->alias(CustomLogger::class, 'admin-logger');
        
        // Tenant logger factory
        $this->app->bind('tenant-logger', function ($app, $parameters) {
            $tenantId = $parameters['tenant_id'] ?? null;
            
            if (!$tenantId) {
                // Try to get tenant ID from session if available
                if (session()->has('current_clinic_id')) {
                    $tenantId = session('current_clinic_id');
                }
            }
            
            return $tenantId ? CustomLogger::forTenant($tenantId) : $app->make('admin-logger');
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
} 