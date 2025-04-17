<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantDatabaseService;
use App\Http\Middleware\CheckClinicActive;
use App\Services\SubdomainService;

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
        
        // Explicitly bind the CheckClinicActive class
        $this->app->bind(CheckClinicActive::class, function ($app) {
            return new CheckClinicActive($app->make(SubdomainService::class));
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
