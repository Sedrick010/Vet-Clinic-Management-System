<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantDatabaseService;
use App\Http\Middleware\CheckClinicActive;
use App\Services\SubdomainService;
use Illuminate\Support\Facades\View;
use App\Models\Clinic;
use App\Providers\ThemeServiceProvider;

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
        
        // Register ThemeServiceProvider
        $this->app->register(ThemeServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share the current clinic with all views
        View::composer('*', function ($view) {
            if (session()->has('current_clinic_id')) {
                $clinicId = session('current_clinic_id');
                $clinic = Clinic::find($clinicId);
                if ($clinic) {
                    $view->with('clinic', $clinic);
                }
            }
        });
    }
}
