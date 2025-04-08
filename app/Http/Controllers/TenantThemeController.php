<?php

namespace App\Http\Controllers;

use App\Models\TenantTheme;
use App\Models\TenantThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Schema;

class TenantThemeController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    public function index()
    {
        try {
            // Check if the tables exist in the current database
            $hasThemeTable = Schema::hasTable('tenant_themes');
            $hasSettingsTable = Schema::hasTable('tenant_theme_settings');

            if (!$hasThemeTable || !$hasSettingsTable) {
                return view('tenant.themes.index', [
                    'themes' => collect(),
                    'settings' => collect(),
                    'error' => 'Theme tables not found in the current database.'
                ]);
            }

            $themes = TenantTheme::all();
            $settings = TenantThemeSetting::all();

            return view('tenant.themes.index', compact('themes', 'settings'));
        } catch (\Exception $e) {
            return view('tenant.themes.index', [
                'themes' => collect(),
                'settings' => collect(),
                'error' => 'Error loading themes: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Check if the tables exist
            if (!Schema::hasTable('tenant_themes') || !Schema::hasTable('tenant_theme_settings')) {
                return back()->with('error', 'Theme tables not found in the current database.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'primary_color' => 'required|string|max:7',
                'secondary_color' => 'required|string|max:7',
                'accent_color' => 'required|string|max:7',
                'font_family' => 'required|string|max:255',
                'button_style' => 'required|string|max:255',
                'card_style' => 'required|string|max:255',
                'layout_style' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            // Create theme
            $theme = TenantTheme::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', false),
            ]);

            // Create settings
            TenantThemeSetting::create([
                'theme_id' => $theme->id,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
                'font_family' => $request->font_family,
                'button_style' => $request->button_style,
                'card_style' => $request->card_style,
                'layout_style' => $request->layout_style,
            ]);

            DB::commit();

            return redirect()->route('tenant.themes.index')
                ->with('success', 'Theme created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating theme: ' . $e->getMessage());
        }
    }

    public function update(Request $request, TenantTheme $theme)
    {
        try {
            // Check if the tables exist
            if (!Schema::hasTable('tenant_themes') || !Schema::hasTable('tenant_theme_settings')) {
                return back()->with('error', 'Theme tables not found in the current database.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'primary_color' => 'required|string|max:7',
                'secondary_color' => 'required|string|max:7',
                'accent_color' => 'required|string|max:7',
                'font_family' => 'required|string|max:255',
                'button_style' => 'required|string|max:255',
                'card_style' => 'required|string|max:255',
                'layout_style' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            // Update theme
            $theme->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', false),
            ]);

            // Update settings
            $theme->settings()->update([
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
                'font_family' => $request->font_family,
                'button_style' => $request->button_style,
                'card_style' => $request->card_style,
                'layout_style' => $request->layout_style,
            ]);

            DB::commit();

            return redirect()->route('tenant.themes.index')
                ->with('success', 'Theme updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating theme: ' . $e->getMessage());
        }
    }

    public function destroy(TenantTheme $theme)
    {
        try {
            // Check if the tables exist
            if (!Schema::hasTable('tenant_themes') || !Schema::hasTable('tenant_theme_settings')) {
                return back()->with('error', 'Theme tables not found in the current database.');
            }

            DB::beginTransaction();

            // Delete settings first
            $theme->settings()->delete();
            
            // Delete theme
            $theme->delete();

            DB::commit();

            return redirect()->route('tenant.themes.index')
                ->with('success', 'Theme deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting theme: ' . $e->getMessage());
        }
    }

    public function edit()
    {
        // Get current tenant from session
        $clinic = session('current_clinic');
        
        if (!$clinic) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Theme settings are only available for clinic tenants.');
        }

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get or initialize theme
        $theme = DB::connection('tenant')->table('tenant_themes')
            ->where('tenant_id', $clinic->id)
            ->first();

        if (!$theme) {
            $theme = (object)[
                'tenant_id' => $clinic->id,
                'primary_color' => '#5e72e4',
                'secondary_color' => '#f7fafc',
                'accent_color' => '#11cdef',
                'text_color' => '#32325d',
                'background_color' => '#ffffff',
                'is_dark_mode' => false
            ];
        }

        // Switch back to main database
        $this->tenantDatabaseService->switchToMain();
        
        return view('tenant.theme.edit', compact('theme'));
    }

    public function updateTheme(Request $request)
    {
        // Get current tenant from session
        $clinic = session('current_clinic');
        
        if (!$clinic) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Theme settings are only available for clinic tenants.');
        }

        $validated = $request->validate([
            'primary_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'secondary_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'accent_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'text_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'background_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'is_dark_mode' => 'boolean',
        ]);

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);

        // Update or create theme settings
        DB::connection('tenant')->table('tenant_themes')
            ->updateOrInsert(
                ['tenant_id' => $clinic->id],
                array_merge($validated, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );

        // Switch back to main database
        $this->tenantDatabaseService->switchToMain();

        return redirect()->back()->with('success', 'Theme settings updated successfully');
    }
}
