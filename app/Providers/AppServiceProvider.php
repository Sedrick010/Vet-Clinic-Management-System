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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
