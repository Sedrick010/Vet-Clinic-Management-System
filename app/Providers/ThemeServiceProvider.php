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
                'cardSecondary' => '#f8f9fa',
                'cardAccent' => '#f0f2f5',
                'text' => '#344767',
                'textSecondary' => '#67748e'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #5e72e4 0%, #825ee4 100%)',
                'success' => 'linear-gradient(310deg, #2dce89 0%, #2dcca8 100%)',
                'info' => 'linear-gradient(310deg, #1171ef 0%, #11cdef 100%)',
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

        $oceanTheme = [
            'name' => 'ocean',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#1a73e8',
                'secondary' => '#5f6368',
                'success' => '#34a853',
                'info' => '#4285f4',
                'warning' => '#fbbc05',
                'danger' => '#ea4335',
                'background' => '#f8f9fa',
                'card' => '#ffffff',
                'cardSecondary' => '#f1f3f4',
                'cardAccent' => '#e8eaed',
                'text' => '#202124',
                'textSecondary' => '#5f6368'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #1a73e8 0%, #4285f4 100%)',
                'success' => 'linear-gradient(310deg, #34a853 0%, #0f9d58 100%)',
                'info' => 'linear-gradient(310deg, #4285f4 0%, #1a73e8 100%)',
                'warning' => 'linear-gradient(310deg, #fbbc05 0%, #f29900 100%)',
                'danger' => 'linear-gradient(310deg, #ea4335 0%, #d93025 100%)'
            ]
        ];

        $forestTheme = [
            'name' => 'forest',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#059669',
                'secondary' => '#4b5563',
                'success' => '#10b981',
                'info' => '#3b82f6',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'background' => '#f0fdf4',
                'card' => '#ffffff',
                'cardSecondary' => '#ecfdf5',
                'cardAccent' => '#d1fae5',
                'text' => '#1f2937',
                'textSecondary' => '#4b5563'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #059669 0%, #10b981 100%)',
                'success' => 'linear-gradient(310deg, #10b981 0%, #34d399 100%)',
                'info' => 'linear-gradient(310deg, #3b82f6 0%, #60a5fa 100%)',
                'warning' => 'linear-gradient(310deg, #f59e0b 0%, #fbbf24 100%)',
                'danger' => 'linear-gradient(310deg, #ef4444 0%, #f87171 100%)'
            ]
        ];

        $sunsetTheme = [
            'name' => 'sunset',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#ff6b6b',
                'secondary' => '#4a4a4a',
                'success' => '#4ecdc4',
                'info' => '#45b7d1',
                'warning' => '#ffbe0b',
                'danger' => '#ff4d6d',
                'background' => '#fff5f5',
                'card' => '#ffffff',
                'cardSecondary' => '#ffe3e3',
                'cardAccent' => '#ffc9c9',
                'text' => '#2b2d42',
                'textSecondary' => '#4a4a4a'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #ff6b6b 0%, #ff8787 100%)',
                'success' => 'linear-gradient(310deg, #4ecdc4 0%, #45b7d1 100%)',
                'info' => 'linear-gradient(310deg, #45b7d1 0%, #3a86ff 100%)',
                'warning' => 'linear-gradient(310deg, #ffbe0b 0%, #fb8500 100%)',
                'danger' => 'linear-gradient(310deg, #ff4d6d 0%, #c9184a 100%)'
            ]
        ];

        $modernTheme = [
            'name' => 'modern',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#6366f1',
                'secondary' => '#64748b',
                'success' => '#22c55e',
                'info' => '#3b82f6',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'background' => '#f8fafc',
                'card' => '#ffffff',
                'cardSecondary' => '#f1f5f9',
                'cardAccent' => '#e2e8f0',
                'text' => '#1e293b',
                'textSecondary' => '#64748b'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #6366f1 0%, #818cf8 100%)',
                'success' => 'linear-gradient(310deg, #22c55e 0%, #4ade80 100%)',
                'info' => 'linear-gradient(310deg, #3b82f6 0%, #60a5fa 100%)',
                'warning' => 'linear-gradient(310deg, #f59e0b 0%, #fbbf24 100%)',
                'danger' => 'linear-gradient(310deg, #ef4444 0%, #f87171 100%)'
            ]
        ];

        $vintageTheme = [
            'name' => 'vintage',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#8b4513',
                'secondary' => '#6b7280',
                'success' => '#059669',
                'info' => '#0ea5e9',
                'warning' => '#d97706',
                'danger' => '#b91c1c',
                'background' => '#fef3c7',
                'card' => '#ffffff',
                'cardSecondary' => '#fef9c3',
                'cardAccent' => '#fef08a',
                'text' => '#422006',
                'textSecondary' => '#78350f'
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #8b4513 0%, #a0522d 100%)',
                'success' => 'linear-gradient(310deg, #059669 0%, #047857 100%)',
                'info' => 'linear-gradient(310deg, #0ea5e9 0%, #0284c7 100%)',
                'warning' => 'linear-gradient(310deg, #d97706 0%, #b45309 100%)',
                'danger' => 'linear-gradient(310deg, #b91c1c 0%, #991b1b 100%)'
            ]
        ];

        $blossomTheme = [
            'name' => 'blossom',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#e75480', // pink
                'secondary' => '#b983ff', // light purple
                'success' => '#a3e635', // light green
                'info' => '#b983ff',
                'warning' => '#fbbf24',
                'danger' => '#f43f5e',
                'background' => '#fff0f6', // very light pink
                'card' => '#ffffff',
                'cardSecondary' => '#ffe4fa',
                'cardAccent' => '#f3c4fb',
                'text' => '#7c2d12', // deep brown
                'textSecondary' => '#a21caf' // purple
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #e75480 0%, #b983ff 100%)',
                'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                'info' => 'linear-gradient(310deg, #b983ff 0%, #e75480 100%)',
                'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
            ]
        ];

        $lagoonTheme = [
            'name' => 'lagoon',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#14b8a6', // teal
                'secondary' => '#38bdf8', // aqua
                'success' => '#22d3ee', // cyan
                'info' => '#0ea5e9',
                'warning' => '#fbbf24',
                'danger' => '#f43f5e',
                'background' => '#ecfeff', // very light aqua
                'card' => '#ffffff',
                'cardSecondary' => '#cffafe',
                'cardAccent' => '#a7f3d0',
                'text' => '#134e4a', // deep teal
                'textSecondary' => '#0e7490' // blue-green
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #14b8a6 0%, #38bdf8 100%)',
                'success' => 'linear-gradient(310deg, #22d3ee 0%, #0ea5e9 100%)',
                'info' => 'linear-gradient(310deg, #0ea5e9 0%, #38bdf8 100%)',
                'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
            ]
        ];

        $amberTheme = [
            'name' => 'amber',
            'version' => '1.0.0',
            'colors' => [
                'primary' => '#f59e42', // amber
                'secondary' => '#fbbf24', // gold
                'success' => '#a3e635', // light green
                'info' => '#fde68a', // light yellow
                'warning' => '#fbbf24',
                'danger' => '#f43f5e',
                'background' => '#fffbea', // very light amber
                'card' => '#ffffff',
                'cardSecondary' => '#fef3c7',
                'cardAccent' => '#fde68a',
                'text' => '#78350f', // deep amber
                'textSecondary' => '#b45309' // brown
            ],
            'gradients' => [
                'primary' => 'linear-gradient(310deg, #f59e42 0%, #fbbf24 100%)',
                'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                'info' => 'linear-gradient(310deg, #fde68a 0%, #fbbf24 100%)',
                'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
            ]
        ];

        // Get the current clinic from the subdomain
        $clinic = app(SubdomainService::class)->getCurrentClinic();

        // Determine which theme to use
        $themeConfig = $defaultTheme;
        if ($clinic) {
            // First get the base theme
            $baseTheme = $defaultTheme;
            switch ($clinic->theme) {
                case 'dark':
                    $baseTheme = $darkTheme;
                    break;
                case 'ocean':
                    $baseTheme = $oceanTheme;
                    break;
                case 'forest':
                    $baseTheme = $forestTheme;
                    break;
                case 'sunset':
                    $baseTheme = $sunsetTheme;
                    break;
                case 'modern':
                    $baseTheme = $modernTheme;
                    break;
                case 'vintage':
                    $baseTheme = $vintageTheme;
                    break;
                case 'blossom':
                    $baseTheme = $blossomTheme;
                    break;
                case 'lagoon':
                    $baseTheme = $lagoonTheme;
                    break;
                case 'amber':
                    $baseTheme = $amberTheme;
                    break;
            }
            
            // Check if this clinic has custom theme colors
            if ($clinic->theme_customization_level === 'advanced' && $clinic->custom_theme_colors) {
                // Merge custom colors with the base theme
                $baseTheme['colors'] = array_merge($baseTheme['colors'], $clinic->custom_theme_colors);
                
                // Also update gradients to match custom colors - always use the lightenColor function
                // to ensure proper gradient generation
                $primary = $baseTheme['colors']['primary'];
                $success = $baseTheme['colors']['success'];
                $info = $baseTheme['colors']['info'];
                $warning = $baseTheme['colors']['warning'];
                $danger = $baseTheme['colors']['danger'];
                
                // Always create gradients for custom colors
                $baseTheme['gradients']['primary'] = 'linear-gradient(310deg, ' . $primary . ' 0%, ' . $this->lightenColor($primary, 15) . ' 100%)';
                $baseTheme['gradients']['success'] = 'linear-gradient(310deg, ' . $success . ' 0%, ' . $this->lightenColor($success, 15) . ' 100%)';
                $baseTheme['gradients']['info'] = 'linear-gradient(310deg, ' . $info . ' 0%, ' . $this->lightenColor($info, 15) . ' 100%)';
                $baseTheme['gradients']['warning'] = 'linear-gradient(310deg, ' . $warning . ' 0%, ' . $this->lightenColor($warning, 15) . ' 100%)';
                $baseTheme['gradients']['danger'] = 'linear-gradient(310deg, ' . $danger . ' 0%, ' . $this->lightenColor($danger, 15) . ' 100%)';
            }
            
            $themeConfig = $baseTheme;
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

    /**
     * Helper method to lighten a hex color
     * 
     * @param string $hex Hex color code
     * @param int $percent Percentage to lighten (0-100)
     * @return string Lightened hex color
     */
    private function lightenColor($hex, $percent) {
        // Convert hex to rgb
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $rgb = [];
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = hexdec(substr($hex, $i * 2, 2));
            $rgb[$i] = round($rgb[$i] + (255 - $rgb[$i]) * ($percent / 100));
            $rgb[$i] = max(0, min(255, $rgb[$i]));
        }
        
        return '#' . sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    }
}