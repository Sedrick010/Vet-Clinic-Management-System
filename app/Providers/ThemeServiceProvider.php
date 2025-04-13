<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Share theme configurations with all views
        View::share('theme', [
            'name' => 'VetClinic Admin',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#5e72e4',
                'secondary' => '#8392ab',
                'success' => '#2dce89',
                'info' => '#11cdef',
                'warning' => '#fb6340',
                'danger' => '#f5365c'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #5e72e4 0%, #825ee4 100%)',
                'success' => 'linear-gradient(310deg, #2dce89 0%, #4fd1c5 100%)',
                'info' => 'linear-gradient(310deg, #11cdef 0%, #1171ef 100%)',
                'warning' => 'linear-gradient(310deg, #fb6340 0%, #fbb140 100%)',
                'danger' => 'linear-gradient(310deg, #f5365c 0%, #f56036 100%)'
            ]
        ]);

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
