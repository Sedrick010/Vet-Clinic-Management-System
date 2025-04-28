<?php
namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Log;

class PetController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    private function getClinic(Request $request)
    {
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;

        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }

        if ($subdomain) {
            return \App\Models\Clinic::where('subdomain', $subdomain)->first();
        }

        return null;
    }

    public function index(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Check if deleted_at column exists
        $hasDeletedAt = false;
        try {
            $hasDeletedAt = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('pets', 'deleted_at');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking for deleted_at column: ' . $e->getMessage());
        }

        $query = Pet::with('owner');
        
        // Only apply the SoftDeletes condition if the column exists
        if ($hasDeletedAt) {
            $query->whereNull('deleted_at');
        }
        
        $pets = $query->orderBy('name')->paginate(10);
        
        // Get theme from clinic settings
        $theme = [];
        if (isset($clinic->settings['theme'])) {
            $theme = $clinic->settings['theme'];
        } else {
            // Use the clinic's theme property
            $clinicTheme = $clinic->theme ?? 'default';
            $theme = [
                'name' => $clinicTheme,
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
            
            // Apply theme-specific colors
            switch ($clinicTheme) {
                case 'dark':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'forest':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'sunset':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'vintage':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'blossom':
                    $theme['colors'] = [
                        'primary' => '#e75480',
                        'secondary' => '#b983ff',
                        'success' => '#a3e635',
                        'info' => '#b983ff',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fff0f6',
                        'card' => '#ffffff',
                        'cardSecondary' => '#ffe4fa',
                        'cardAccent' => '#f3c4fb',
                        'text' => '#7c2d12',
                        'textSecondary' => '#a21caf'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #e75480 0%, #b983ff 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #b983ff 0%, #e75480 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'lagoon':
                    $theme['colors'] = [
                        'primary' => '#14b8a6',
                        'secondary' => '#38bdf8',
                        'success' => '#22d3ee',
                        'info' => '#0ea5e9',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#ecfeff',
                        'card' => '#ffffff',
                        'cardSecondary' => '#cffafe',
                        'cardAccent' => '#a7f3d0',
                        'text' => '#134e4a',
                        'textSecondary' => '#0e7490'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #14b8a6 0%, #38bdf8 100%)',
                        'success' => 'linear-gradient(310deg, #22d3ee 0%, #0ea5e9 100%)',
                        'info' => 'linear-gradient(310deg, #0ea5e9 0%, #38bdf8 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'amber':
                    $theme['colors'] = [
                        'primary' => '#f59e42',
                        'secondary' => '#fbbf24',
                        'success' => '#a3e635',
                        'info' => '#fde68a',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fffbea',
                        'card' => '#ffffff',
                        'cardSecondary' => '#fef3c7',
                        'cardAccent' => '#fde68a',
                        'text' => '#78350f',
                        'textSecondary' => '#b45309'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #f59e42 0%, #fbbf24 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #fde68a 0%, #fbbf24 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
            }
        }
        
        // Get tenant user data for the sidebar (from session)
        $tenantUser = null;
        $userRole = 'staff';
        $userName = '';
        
        if (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $userRole = $tenantUser->role;
            $userName = $tenantUser->name;
        }
        
        // Set up admin menu if needed (for sidebar)
        $adminMenu = [];
        
        return view('pets.index', compact('pets', 'clinic', 'theme', 'userRole', 'userName', 'adminMenu'))
            ->with('isSidebar', true);
    }

    public function create(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $clients = Client::orderBy('name')->get();
        
        // Get theme from clinic settings
        $theme = [];
        if (isset($clinic->settings['theme'])) {
            $theme = $clinic->settings['theme'];
        } else {
            // Use the clinic's theme property
            $clinicTheme = $clinic->theme ?? 'default';
            $theme = [
                'name' => $clinicTheme,
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
            
            // Apply theme-specific colors
            switch ($clinicTheme) {
                case 'dark':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'forest':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'sunset':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'vintage':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'blossom':
                    $theme['colors'] = [
                        'primary' => '#e75480',
                        'secondary' => '#b983ff',
                        'success' => '#a3e635',
                        'info' => '#b983ff',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fff0f6',
                        'card' => '#ffffff',
                        'cardSecondary' => '#ffe4fa',
                        'cardAccent' => '#f3c4fb',
                        'text' => '#7c2d12',
                        'textSecondary' => '#a21caf'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #e75480 0%, #b983ff 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #b983ff 0%, #e75480 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'lagoon':
                    $theme['colors'] = [
                        'primary' => '#14b8a6',
                        'secondary' => '#38bdf8',
                        'success' => '#22d3ee',
                        'info' => '#0ea5e9',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#ecfeff',
                        'card' => '#ffffff',
                        'cardSecondary' => '#cffafe',
                        'cardAccent' => '#a7f3d0',
                        'text' => '#134e4a',
                        'textSecondary' => '#0e7490'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #14b8a6 0%, #38bdf8 100%)',
                        'success' => 'linear-gradient(310deg, #22d3ee 0%, #0ea5e9 100%)',
                        'info' => 'linear-gradient(310deg, #0ea5e9 0%, #38bdf8 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'amber':
                    $theme['colors'] = [
                        'primary' => '#f59e42',
                        'secondary' => '#fbbf24',
                        'success' => '#a3e635',
                        'info' => '#fde68a',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fffbea',
                        'card' => '#ffffff',
                        'cardSecondary' => '#fef3c7',
                        'cardAccent' => '#fde68a',
                        'text' => '#78350f',
                        'textSecondary' => '#b45309'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #f59e42 0%, #fbbf24 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #fde68a 0%, #fbbf24 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
            }
        }
        
        // Get tenant user data for the sidebar (from session)
        $tenantUser = null;
        $userRole = 'staff';
        $userName = '';
        
        if (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $userRole = $tenantUser->role;
            $userName = $tenantUser->name;
        }
        
        // Set up admin menu if needed (for sidebar)
        $adminMenu = [];
        
        return view('pets.create', compact('clients', 'clinic', 'theme', 'userRole', 'userName', 'adminMenu'))
            ->with('isSidebar', true);
    }

    public function store(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:tenant.clients,id',
            'species' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,unknown',
            'notes' => 'nullable|string'
        ]);

        try {
            Pet::create($validated);
            return redirect()->route('pets.index')
                ->with('success', 'Pet created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create pet: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create pet. Please try again.');
        }
    }

    public function show(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $pet = Pet::with(['owner', 'appointments'])->findOrFail($id);
        
        // Get theme from clinic settings
        $theme = [];
        if (isset($clinic->settings['theme'])) {
            $theme = $clinic->settings['theme'];
        } else {
            // Use the clinic's theme property
            $clinicTheme = $clinic->theme ?? 'default';
            $theme = [
                'name' => $clinicTheme,
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
            
            // Apply theme-specific colors
            switch ($clinicTheme) {
                case 'dark':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'forest':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'sunset':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'vintage':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'blossom':
                    $theme['colors'] = [
                        'primary' => '#e75480',
                        'secondary' => '#b983ff',
                        'success' => '#a3e635',
                        'info' => '#b983ff',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fff0f6',
                        'card' => '#ffffff',
                        'cardSecondary' => '#ffe4fa',
                        'cardAccent' => '#f3c4fb',
                        'text' => '#7c2d12',
                        'textSecondary' => '#a21caf'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #e75480 0%, #b983ff 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #b983ff 0%, #e75480 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'lagoon':
                    $theme['colors'] = [
                        'primary' => '#14b8a6',
                        'secondary' => '#38bdf8',
                        'success' => '#22d3ee',
                        'info' => '#0ea5e9',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#ecfeff',
                        'card' => '#ffffff',
                        'cardSecondary' => '#cffafe',
                        'cardAccent' => '#a7f3d0',
                        'text' => '#134e4a',
                        'textSecondary' => '#0e7490'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #14b8a6 0%, #38bdf8 100%)',
                        'success' => 'linear-gradient(310deg, #22d3ee 0%, #0ea5e9 100%)',
                        'info' => 'linear-gradient(310deg, #0ea5e9 0%, #38bdf8 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'amber':
                    $theme['colors'] = [
                        'primary' => '#f59e42',
                        'secondary' => '#fbbf24',
                        'success' => '#a3e635',
                        'info' => '#fde68a',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fffbea',
                        'card' => '#ffffff',
                        'cardSecondary' => '#fef3c7',
                        'cardAccent' => '#fde68a',
                        'text' => '#78350f',
                        'textSecondary' => '#b45309'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #f59e42 0%, #fbbf24 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #fde68a 0%, #fbbf24 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
            }
        }
        
        // Get tenant user data for the sidebar (from session)
        $tenantUser = null;
        $userRole = 'staff';
        $userName = '';
        
        if (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $userRole = $tenantUser->role;
            $userName = $tenantUser->name;
        }
        
        // Set up admin menu if needed (for sidebar)
        $adminMenu = [];
        
        return view('pets.show', compact('pet', 'clinic', 'theme', 'userRole', 'userName', 'adminMenu'))
            ->with('isSidebar', true);
    }

    public function edit(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $pet = Pet::findOrFail($id);
        $clients = Client::orderBy('name')->get();
        
        // Get theme from clinic settings
        $theme = [];
        if (isset($clinic->settings['theme'])) {
            $theme = $clinic->settings['theme'];
        } else {
            // Use the clinic's theme property
            $clinicTheme = $clinic->theme ?? 'default';
            $theme = [
                'name' => $clinicTheme,
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
            
            // Apply theme-specific colors
            switch ($clinicTheme) {
                case 'dark':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'forest':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'sunset':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'vintage':
                    $theme['colors'] = [
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
                    ];
                    break;
                case 'blossom':
                    $theme['colors'] = [
                        'primary' => '#e75480',
                        'secondary' => '#b983ff',
                        'success' => '#a3e635',
                        'info' => '#b983ff',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fff0f6',
                        'card' => '#ffffff',
                        'cardSecondary' => '#ffe4fa',
                        'cardAccent' => '#f3c4fb',
                        'text' => '#7c2d12',
                        'textSecondary' => '#a21caf'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #e75480 0%, #b983ff 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #b983ff 0%, #e75480 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'lagoon':
                    $theme['colors'] = [
                        'primary' => '#14b8a6',
                        'secondary' => '#38bdf8',
                        'success' => '#22d3ee',
                        'info' => '#0ea5e9',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#ecfeff',
                        'card' => '#ffffff',
                        'cardSecondary' => '#cffafe',
                        'cardAccent' => '#a7f3d0',
                        'text' => '#134e4a',
                        'textSecondary' => '#0e7490'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #14b8a6 0%, #38bdf8 100%)',
                        'success' => 'linear-gradient(310deg, #22d3ee 0%, #0ea5e9 100%)',
                        'info' => 'linear-gradient(310deg, #0ea5e9 0%, #38bdf8 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
                case 'amber':
                    $theme['colors'] = [
                        'primary' => '#f59e42',
                        'secondary' => '#fbbf24',
                        'success' => '#a3e635',
                        'info' => '#fde68a',
                        'warning' => '#fbbf24',
                        'danger' => '#f43f5e',
                        'background' => '#fffbea',
                        'card' => '#ffffff',
                        'cardSecondary' => '#fef3c7',
                        'cardAccent' => '#fde68a',
                        'text' => '#78350f',
                        'textSecondary' => '#b45309'
                    ];
                    $theme['gradients'] = [
                        'primary' => 'linear-gradient(310deg, #f59e42 0%, #fbbf24 100%)',
                        'success' => 'linear-gradient(310deg, #a3e635 0%, #bef264 100%)',
                        'info' => 'linear-gradient(310deg, #fde68a 0%, #fbbf24 100%)',
                        'warning' => 'linear-gradient(310deg, #fbbf24 0%, #f59e42 100%)',
                        'danger' => 'linear-gradient(310deg, #f43f5e 0%, #be185d 100%)'
                    ];
                    break;
            }
        }
        
        // Get tenant user data for the sidebar (from session)
        $tenantUser = null;
        $userRole = 'staff';
        $userName = '';
        
        if (session()->has('tenant_user')) {
            $tenantUser = (object)session('tenant_user');
            $userRole = $tenantUser->role;
            $userName = $tenantUser->name;
        }
        
        // Set up admin menu if needed (for sidebar)
        $adminMenu = [];
        
        return view('pets.edit', compact('pet', 'clients', 'clinic', 'theme', 'userRole', 'userName', 'adminMenu'))
            ->with('isSidebar', true);
    }

    public function update(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:tenant.clients,id',
            'species' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,unknown',
            'notes' => 'nullable|string'
        ]);

        try {
            $pet = Pet::findOrFail($id);
            $pet->update($validated);
            
            return redirect()->route('pets.index')
                ->with('success', 'Pet updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update pet: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update pet. Please try again.');
        }
    }

    public function destroy(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        try {
            $pet = Pet::findOrFail($id);
            $pet->delete();
            
            return redirect()->route('pets.index')
                ->with('success', 'Pet deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete pet: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete pet. Please try again.');
        }
    }
} 