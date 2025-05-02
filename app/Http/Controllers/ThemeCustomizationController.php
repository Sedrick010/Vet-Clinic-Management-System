<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ThemeCustomizationController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the advanced theme customization interface.
     */
    public function edit(Request $request): View
    {
        $clinicId = session('current_clinic_id');
        if (!$clinicId) {
            abort(404, 'No clinic selected');
        }

        $clinic = Clinic::findOrFail($clinicId);
        
        // Check if this clinic is on business plan
        $themeCustomizationLevel = $this->subscriptionService->getThemeCustomizationLevel($clinic);
        
        if ($themeCustomizationLevel !== 'advanced') {
            abort(403, 'This feature is only available on the Business Plan');
        }
        
        // Get current theme colors
        $themeConfig = $clinic->getThemeConfig();
        $customColors = $clinic->custom_theme_colors ?: [];
        
        // Merge with default theme to ensure all colors are present
        $currentColors = array_merge($themeConfig['colors'], $customColors);
        
        return view('themes.customize', [
            'clinic' => $clinic,
            'currentColors' => $currentColors,
            'baseTheme' => $clinic->theme,
        ]);
    }
    
    /**
     * Update the custom theme colors.
     */
    public function update(Request $request): RedirectResponse
    {
        $clinicId = session('current_clinic_id');
        if (!$clinicId) {
            abort(404, 'No clinic selected');
        }

        $clinic = Clinic::findOrFail($clinicId);
        
        // Check if this clinic is on business plan
        $themeCustomizationLevel = $this->subscriptionService->getThemeCustomizationLevel($clinic);
        
        if ($themeCustomizationLevel !== 'advanced') {
            return redirect()->route('clinic.profile')
                ->with('error', 'Advanced theme customization is only available on the Business Plan');
        }
        
        // Validate color inputs
        $validateHexColor = function ($attribute, $value, $fail) {
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                $fail('The '.$attribute.' must be a valid hex color code.');
            }
        };
        
        $validator = Validator::make($request->all(), [
            'primary' => ['required', $validateHexColor],
            'secondary' => ['required', $validateHexColor],
            'success' => ['required', $validateHexColor],
            'info' => ['required', $validateHexColor],
            'warning' => ['required', $validateHexColor],
            'danger' => ['required', $validateHexColor],
            'background' => ['required', $validateHexColor],
            'card' => ['required', $validateHexColor],
            'text' => ['required', $validateHexColor],
            'textSecondary' => ['required', $validateHexColor],
            'base_theme' => ['required', 'in:default,dark'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Update custom theme colors
        $customColors = [
            'primary' => $request->primary,
            'secondary' => $request->secondary,
            'success' => $request->success,
            'info' => $request->info,
            'warning' => $request->warning,
            'danger' => $request->danger,
            'background' => $request->background,
            'card' => $request->card,
            'text' => $request->text,
            'textSecondary' => $request->textSecondary,
        ];
        
        // Update clinic with custom colors and base theme
        $clinic->update([
            'custom_theme_colors' => $customColors,
            'theme' => $request->base_theme,
            'theme_customization_level' => 'advanced',
        ]);
        
        // Clear any cache that might be affecting theme rendering
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Force browsers to reload CSS by adding a cache-busting query parameter to the CSS files
        $cssVersion = time();
        \Session::put('css_version', $cssVersion);
        
        // Clear view cache too
        try {
            \Artisan::call('view:clear');
            \Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Continue even if we can't clear cache
            \Log::error('Failed to clear cache: ' . $e->getMessage());
        }
        
        // Redirect with success message and force refresh
        return redirect()->route('dashboard')
            ->with('success', 'Your custom theme has been applied successfully.')
            ->with('_refresh', true);
    }
    
    /**
     * Reset the custom theme colors.
     */
    public function reset(Request $request): RedirectResponse
    {
        $clinicId = session('current_clinic_id');
        if (!$clinicId) {
            abort(404, 'No clinic selected');
        }

        $clinic = Clinic::findOrFail($clinicId);
        
        // Reset custom colors
        $clinic->update([
            'custom_theme_colors' => null,
            'theme_customization_level' => 'basic',
        ]);
        
        // Clear any cache that might be affecting theme rendering
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Force browsers to reload CSS
        $cssVersion = time();
        \Session::put('css_version', $cssVersion);
        
        // Clear view cache too
        try {
            \Artisan::call('view:clear');
            \Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Continue even if we can't clear cache
            \Log::error('Failed to clear cache: ' . $e->getMessage());
        }
        
        // Redirect with success message
        return redirect()->route('dashboard')
            ->with('success', 'Your theme has been reset to default!')
            ->with('_refresh', true);
    }
}
