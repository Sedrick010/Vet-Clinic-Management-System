<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantDatabaseService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TenantDatabaseService as a singleton
        $this->app->singleton(TenantDatabaseService::class, function ($app) {
            return new TenantDatabaseService();
        });

        // Register the SubdomainService in the container
        $this->app->singleton('subdomain', function ($app) {
            return new \App\Services\SubdomainService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
