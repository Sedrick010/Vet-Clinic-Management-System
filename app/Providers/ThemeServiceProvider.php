<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\SubdomainService;
use App\Facades\Subdomain;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define theme configurations
        $defaultTheme = [
            'name' => 'default',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#5e72e4',
                'secondary' => '#8392ab',
                'success' => '#2dce89',
                'info' => '#11cdef',
                'warning' => '#fb6340',
                'danger' => '#f5365c',
                'background' => '#f8f9fe',
                'card' => '#ffffff',
                'text' => '#344767',
                'textSecondary' => '#67748e'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #5e72e4 0%, #825ee4 100%)',
                'success' => 'linear-gradient(310deg, #2dce89 0%, #4fd1c5 100%)',
                'info' => 'linear-gradient(310deg, #11cdef 0%, #1171ef 100%)',
                'warning' => 'linear-gradient(310deg, #fb6340 0%, #fbb140 100%)',
                'danger' => 'linear-gradient(310deg, #f5365c 0%, #f56036 100%)'
            ]
        ];
        
        $darkTheme = [
            'name' => 'dark',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#6f42c1',
                'secondary' => '#4c566a',
                'success' => '#40b983',
                'info' => '#3498db',
                'warning' => '#f39c12',
                'danger' => '#e74c3c',
                'background' => '#1e1e2d',
                'card' => '#2a2a3c',
                'cardSecondary' => '#323248',
                'cardAccent' => '#252536',
                'text' => '#e6e6e6',
                'textSecondary' => '#b5b5c3'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #6f42c1 0%, #8344c1 100%)',
                'success' => 'linear-gradient(310deg, #40b983 0%, #36d1b7 100%)',
                'info' => 'linear-gradient(310deg, #3498db 0%, #3464ef 100%)',
                'warning' => 'linear-gradient(310deg, #f39c12 0%, #f5b74f 100%)',
                'danger' => 'linear-gradient(310deg, #e74c3c 0%, #e76c47 100%)'
            ]
        ];

        // Get the current clinic from the subdomain
        $clinic = app(SubdomainService::class)->getCurrentClinic();

        // Determine which theme to use
        $themeConfig = $defaultTheme;
        if ($clinic && $clinic->theme === 'dark') {
            $themeConfig = $darkTheme;
        }

        // Share theme configurations with all views
        View::share('theme', $themeConfig);

        // Share admin menu with all views for consistency
        View::share('adminMenu', [
            [
                'name' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'color' => 'primary',
                'matches' => ['admin/dashboard']
            ],
            [
                'name' => 'Clinic Approvals',
                'route' => 'admin.clinics.index',
                'icon' => 'fas fa-clinic-medical',
                'color' => 'success',
                'matches' => ['admin/clinics']
            ],
            [
                'name' => 'Database Check',
                'route' => 'admin.database.check',
                'icon' => 'fas fa-database',
                'color' => 'info',
                'matches' => ['admin/database-check']
            ]
        ]);
    }
}
