<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Limit;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->configureRateLimiting();
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
        
        // Add custom route binding resolver for Inventory model
        Route::bind('inventory', function ($value) {
            // Get current clinic
            $clinicId = session('current_clinic_id');
            if (!$clinicId) {
                abort(403, 'No clinic selected');
            }
            
            $clinic = \App\Models\Clinic::find($clinicId);
            if (!$clinic) {
                abort(404, 'Clinic not found');
            }
            
            // Switch to tenant DB
            try {
                app(\App\Services\TenantDatabaseService::class)->switchToTenant($clinic);
                // Now find the inventory item
                return \App\Models\Inventory::findOrFail($value);
            } catch (\Exception $e) {
                Log::error('Failed to resolve inventory route binding', [
                    'error' => $e->getMessage(),
                    'inventory_id' => $value
                ]);
                abort(500, 'Database connection error');
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiters are already configured in the boot method
    }
} 