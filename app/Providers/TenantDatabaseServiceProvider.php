<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TenantDatabaseService;
use App\Models\Clinic;

class TenantDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register TenantDatabaseService if not already registered
        if (!$this->app->bound(TenantDatabaseService::class)) {
            $this->app->singleton(TenantDatabaseService::class, function ($app) {
                return new TenantDatabaseService();
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set up event listener for database connection issues with tenant connection
        DB::listen(function ($query) {
            if ($query->connectionName === 'tenant' && $query->time > 1000) {
                Log::warning('Slow tenant database query', [
                    'query' => $query->sql,
                    'time' => $query->time,
                    'connection' => $query->connectionName,
                ]);
            }
        });
        
        // Register a macro on the DB facade for easy tenant switching
        DB::macro('useTenant', function (Clinic $clinic) {
            app(TenantDatabaseService::class)->switchToTenant($clinic);
            return DB::connection('tenant');
        });
        
        // Register a macro to easily check if the tenant connection is established
        DB::macro('hasTenantConnection', function () {
            try {
                return config('database.connections.tenant') &&
                       DB::connection('tenant')->getDatabaseName() !== null && 
                       DB::connection('tenant')->getPdo() !== null;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
} 