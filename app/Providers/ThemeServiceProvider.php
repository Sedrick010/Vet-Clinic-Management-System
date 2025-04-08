<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\TenantDatabaseService;
use App\Models\TenantTheme;
use App\Models\TenantThemeSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

class ThemeServiceProvider extends ServiceProvider
{
    protected $tenantDatabaseService;

    /**
     * Default theme settings
     */
    protected $defaultTheme = [
        'primary_color' => '#4F46E5',
        'secondary_color' => '#10B981',
        'accent_color' => '#F59E0B',
        'text_color' => '#111827',
        'background_color' => '#FFFFFF',
        'font_family' => 'Inter',
        'button_style' => 'rounded',
        'card_style' => 'shadow',
        'layout_style' => 'default',
    ];

    public function __construct($app)
    {
        parent::__construct($app);
        $this->tenantDatabaseService = app(TenantDatabaseService::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                // Skip theme settings for admin routes
                if (str_starts_with(Request::path(), 'admin')) {
                    return;
                }

                // Check if we're in a tenant context by looking for current_clinic in session
                if (Session::has('current_clinic')) {
                    $clinic = Session::get('current_clinic');
                    
                    if ($clinic) {
                        $this->tenantDatabaseService->switchToTenant($clinic);
                        
                        // Get active theme
                        $activeTheme = TenantTheme::where('is_active', true)->first();
                        
                        if ($activeTheme && $activeTheme->settings) {
                            $themeSettings = $activeTheme->settings;
                            
                            // Share theme settings with all views only if a theme is active
                            $view->with([
                                'theme' => [
                                    'primary_color' => $themeSettings->primary_color,
                                    'secondary_color' => $themeSettings->secondary_color,
                                    'accent_color' => $themeSettings->accent_color,
                                    'text_color' => $themeSettings->text_color,
                                    'background_color' => $themeSettings->background_color,
                                    'font_family' => $themeSettings->font_family,
                                    'button_style' => $themeSettings->button_style,
                                    'card_style' => $themeSettings->card_style,
                                    'layout_style' => $themeSettings->layout_style,
                                ]
                            ]);
                            
                            return;
                        }
                    }
                }
                
                // If no theme is selected, don't apply any theme settings
                $view->with(['theme' => null]);
            } catch (\Exception $e) {
                Log::error('Error loading theme settings: ' . $e->getMessage());
                // If there's an error, don't apply any theme settings
                $view->with(['theme' => null]);
            }
        });
    }
}
