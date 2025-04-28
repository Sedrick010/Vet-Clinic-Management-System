<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicSelectorController;
use App\Http\Controllers\AdminController;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\ValidateTenantSubdomain;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicProfileController;
use App\Http\Controllers\TenantProfileController;

// Protection against unregistered subdomains - apply at the top of the file
Route::middleware([
    \App\Http\Middleware\ValidateTenantSubdomain::class,
    \App\Http\Middleware\CheckClinicEnabled::class
])->group(function () {
    Route::get('/', function () {
        // Check if accessing from a subdomain
        $host = request()->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;
        
        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }
        
        $clinic = null;
        if ($subdomain) {
            $clinic = \App\Models\Clinic::where('subdomain', $subdomain)->first();
        }
        
        return view('welcome', [
            'clinic' => $clinic,
            'is_subdomain' => !empty($subdomain)
        ]);
    })->name('welcome');

    // Clinic registration routes
    Route::get('/register-clinic', [ClinicController::class, 'create'])->name('clinics.create');
    Route::post('/register-clinic', [ClinicController::class, 'store'])->name('clinics.store');
    Route::get('/clinic-pending', [ClinicController::class, 'pending'])->name('clinics.pending');

    // Admin routes - only accessible to admin users
    Route::prefix('admin')->middleware(['auth', 'admin', \App\Http\Middleware\CheckSessionValid::class])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/theme-reference', [AdminController::class, 'themeReference'])->name('admin.theme-reference');
        Route::get('/clinics', [ClinicController::class, 'index'])->name('admin.clinics.index');
        Route::post('/clinics/{id}/approve', [ClinicController::class, 'approve'])->name('admin.clinics.approve');
        Route::post('/clinics/{id}/reject', [ClinicController::class, 'reject'])->name('admin.clinics.reject');
        Route::delete('/clinics/{id}', [ClinicController::class, 'destroy'])->name('admin.clinics.destroy');
        Route::post('/clinics/{id}/recreate-database', [ClinicController::class, 'recreateDatabase'])->name('admin.clinics.recreate-database');
        Route::get('/database-check', function() {
            return view('admin.database-check');
        })->name('admin.database.check');
        Route::get('/clinics/{clinic}', [ClinicController::class, 'show'])->name('admin.clinics.show');
        Route::patch('/clinics/{clinic}/toggle-active', [ClinicController::class, 'toggleActive'])->name('admin.clinics.toggle-active');
        Route::patch('/clinics/{clinic}/toggle-enabled', [\App\Http\Controllers\Admin\ClinicController::class, 'toggleEnabled'])->name('admin.clinics.toggle-enabled');
    });

    // Clinic selector routes
    Route::middleware(['auth', \App\Http\Middleware\CheckSessionValid::class])->group(function() {
        Route::get('/select-clinic', [ClinicSelectorController::class, 'index'])->name('clinics.select');
        Route::get('/switch-clinic/{clinicId}', [ClinicSelectorController::class, 'switchClinic'])->name('clinics.switch');
    });

    // Dashboard - handles both admin and tenant users
    Route::get('/dashboard', function (Request $request) {
        // Check if user is authenticated with Laravel Auth (admin or regular user)
        if (auth()->check()) {
            $user = auth()->user();
            
            // If admin user, redirect to admin dashboard
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            
            // For other central database users, show regular dashboard
            return view('dashboard', [
                'isSidebar' => true,
                'userName' => $user->name,
                'userRole' => $user->role,
                'clinicName' => 'Central System'
            ]);
        }
        
        // Check if user is authenticated as a tenant user
        if (session()->has('tenant_user')) {
            // Check if clinic exists and is approved
            $clinicId = session('current_clinic_id');
            $clinic = \App\Models\Clinic::find($clinicId);
            
            if (!$clinic || $clinic->approval_status !== 'approved') {
                return redirect()->route('clinics.pending');
            }
            
            // Always get fresh clinic data to reflect subscription changes
            $freshClinic = \App\Models\Clinic::find($clinicId)->fresh();
            if ($freshClinic) {
                $clinic = $freshClinic;
            }
            
            // Get tenant user data as object
            $tenantUser = (object)session('tenant_user');
            
            // Switch to tenant database for this request
            app(\App\Services\TenantDatabaseService::class)->switchToTenant($clinic);
            
            // Check if this staff account has been deleted
            try {
                if (\Illuminate\Support\Facades\DB::connection('tenant')->getSchemaBuilder()->hasTable('deleted_staff')) {
                    $deleted = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('deleted_staff')
                        ->where('id', $tenantUser->id)
                        ->orWhere('email', $tenantUser->email)
                        ->exists();
                    
                    if ($deleted) {
                        // Staff account has been deleted, invalidate session
                        \Illuminate\Support\Facades\Log::info('Invalidating session for deleted staff member in dashboard', [
                            'staff_id' => $tenantUser->id,
                            'email' => $tenantUser->email
                        ]);
                        
                        // Clear all session data
                        \Illuminate\Support\Facades\Session::flush();
                        
                        // Redirect to login with message
                        return redirect()->route('login')
                            ->with('error', 'Your account has been deactivated. Please contact the clinic administrator.');
                    }
                }
            } catch (\Exception $e) {
                // Log the error but allow the request to continue
                \Illuminate\Support\Facades\Log::error('Error checking for deleted staff in dashboard: ' . $e->getMessage());
            }
            
            // Get staff count for the dashboard
            $staffCount = 0;
            try {
                $staffCount = \App\Models\Staff::count();
            } catch (\Exception $e) {
                // Log error but continue
                \Illuminate\Support\Facades\Log::error('Failed to get staff count: ' . $e->getMessage());
            }
            
            // Get inventory stats for the dashboard
            $inventoryStats = [];
            try {
                $inventoryStats = \App\Http\Controllers\InventoryController::getInventoryStats();
            } catch (\Exception $e) {
                // Log error but continue
                \Illuminate\Support\Facades\Log::error('Failed to get inventory stats: ' . $e->getMessage());
            }
            
            return view('dashboard', [
                'isSidebar' => true,
                'clinicName' => $clinic->name,
                'userRole' => $tenantUser->role,
                'userName' => $tenantUser->name,
                'staffCount' => $staffCount,
                'clinic' => $clinic,
                'inventoryStats' => $inventoryStats,
            ]);
        }
        
        // Not authenticated at all, redirect to login
        return redirect()->route('login');
    })->middleware([\App\Http\Middleware\CheckSessionValid::class])->name('dashboard');

    // Profile routes
    Route::middleware(['auth', \App\Http\Middleware\CheckSessionValid::class])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Subdomain Demo
    Route::get('/subdomain-demo', [App\Http\Controllers\SubdomainDemoController::class, 'index'])
        ->name('subdomain.demo');
    
    // Session check route for AJAX validation
    Route::get('/session-check', function() {
        return response()->json([
            'valid' => (auth()->check() || session()->has('tenant_user'))
        ]);
    })->name('session.check');
    
    // Subscription status check route for AJAX
    Route::get('/check-subscription-status', function() {
        if (!session()->has('tenant_user') || !session('current_clinic_id')) {
            return response()->json(['error' => 'No tenant session'], 403);
        }
        
        $clinicId = session('current_clinic_id');
        $clinic = \App\Models\Clinic::find($clinicId);
        
        if (!$clinic) {
            return response()->json(['error' => 'Clinic not found'], 404);
        }
        
        return response()->json([
            'status' => (bool)$clinic->is_subscription_active
        ]);
    })->name('subscription.status.check');
    
    // Auth routes for both admin and tenant users - moved inside tenant.validate middleware
    require __DIR__.'/auth.php';
    
    // Customer routes
    Route::middleware('guest')->group(function () {
        // Main site routes for clinic browsing
        Route::get('/clinics', [CustomerController::class, 'showClinicSelection'])->name('clinics.browse');
        Route::get('/clinic/{subdomain}/select', [CustomerController::class, 'selectClinic'])->name('clinic.select');
        
        // Subdomain-specific routes
        Route::get('/customer/register/{subdomain?}', [CustomerController::class, 'showRegistrationForm'])->name('customer.register');
        Route::post('/customer/register/{subdomain}', [CustomerController::class, 'register']);
        Route::get('/customer/login/{subdomain?}', [CustomerController::class, 'showLoginForm'])->name('customer.login');
        Route::post('/customer/login/{subdomain}', [CustomerController::class, 'login']);
    });

    Route::middleware(['customer.auth'])->group(function () {
        Route::get('/customer/dashboard/{subdomain}', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
        Route::post('/customer/logout/{subdomain}', [CustomerController::class, 'logout'])->name('customer.logout');
    });

    // Tenant staff management routes - only for authenticated tenant users in active clinics
    Route::middleware([
        \App\Http\Middleware\AuthTenantStaff::class, 
        \App\Http\Middleware\CheckClinicActive::class,
        \App\Http\Middleware\CheckClinicEnabled::class
    ])->group(function() {
        // Staff routes
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('/staff/{id}/resend-invitation', [StaffController::class, 'resendInvitation'])->name('staff.resend-invitation');
        Route::post('/staff/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');

        // Pet routes
        Route::get('/pets', [\App\Http\Controllers\PetController::class, 'index'])->name('pets.index');
        Route::get('/pets/create', [\App\Http\Controllers\PetController::class, 'create'])->name('pets.create');
        Route::post('/pets', [\App\Http\Controllers\PetController::class, 'store'])->name('pets.store');
        Route::get('/pets/{id}', [\App\Http\Controllers\PetController::class, 'show'])->name('pets.show');
        Route::get('/pets/{id}/edit', [\App\Http\Controllers\PetController::class, 'edit'])->name('pets.edit');
        Route::put('/pets/{id}', [\App\Http\Controllers\PetController::class, 'update'])->name('pets.update');
        Route::delete('/pets/{id}', [\App\Http\Controllers\PetController::class, 'destroy'])->name('pets.destroy');

        // Client routes
        Route::get('/clients', [\App\Http\Controllers\ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [\App\Http\Controllers\ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [\App\Http\Controllers\ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{id}/edit', [\App\Http\Controllers\ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'destroy'])->name('clients.destroy');

        // Appointment routes
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/get-pets/{clientId}', [AppointmentController::class, 'getPetsByClient'])->name('appointments.get-pets');
        Route::get('/appointments/pets/{clientId}', [AppointmentController::class, 'getPetsByClient'])->name('appointments.get-pets.alt');
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
        
        // Clinic profile routes - accessible to all staff, but editing is restricted within the controller
        Route::get('/clinic/profile', [ClinicProfileController::class, 'edit'])->name('clinic.profile');
        Route::put('/clinic/profile', [ClinicProfileController::class, 'update'])->name('clinic.profile.update');
        
        // Tenant Profile routes
        Route::get('/tenant/profile', [TenantProfileController::class, 'edit'])->name('tenant.profile.edit');
        Route::patch('/tenant/profile', [TenantProfileController::class, 'update'])->name('tenant.profile.update');
        Route::put('/tenant/profile/password', [TenantProfileController::class, 'updatePassword'])->name('tenant.profile.password.update');
    });

    // Premium features - requires active subscription
    Route::middleware([
        \App\Http\Middleware\AuthTenantStaff::class, 
        \App\Http\Middleware\CheckClinicActive::class,
        \App\Http\Middleware\CheckClinicEnabled::class,
        \App\Http\Middleware\RealTimeSubscriptionCheck::class,
        \App\Http\Middleware\CheckSubscriptionAccess::class
    ])->prefix('premium')->name('premium.')->group(function() {
        Route::get('/reports', [\App\Http\Controllers\PremiumReportsController::class, 'index'])->name('reports');
        Route::get('/analytics', [\App\Http\Controllers\PremiumReportsController::class, 'analytics'])->name('analytics');
    });

    // Admin routes for managing clinics
    Route::middleware(['auth', 'admin', 'web'])->prefix('admin/clinics')->name('admin.clinics.')->group(function () {
        // Show clinic details
        Route::get('/{clinic}', [\App\Http\Controllers\Admin\ClinicController::class, 'show'])->name('show');
    });

    // Admin routes for clinic subscription management
    Route::middleware(['auth', 'admin', 'web'])->prefix('admin/clinics/{clinic}')->name('admin.clinics.')->group(function () {
        // Subscription management
        Route::get('/subscription', [\App\Http\Controllers\Admin\ClinicSubscriptionController::class, 'edit'])->name('subscription.edit');
        Route::put('/subscription', [\App\Http\Controllers\Admin\ClinicSubscriptionController::class, 'update'])->name('subscription.update');
        Route::put('/subscription/toggle-activation', [\App\Http\Controllers\Admin\ClinicSubscriptionController::class, 'toggleActivation'])->name('subscription.toggle-activation');
        Route::patch('/subscription/toggle', [\App\Http\Controllers\Admin\ClinicSubscriptionController::class, 'toggle'])->name('subscription.toggle');
    });

    // Appointment routes
    Route::resource('appointments', AppointmentController::class);

    // Inventory Management Routes - only accessible to tenant users
    Route::middleware([
        \App\Http\Middleware\AuthTenantStaff::class, 
        \App\Http\Middleware\CheckClinicActive::class,
        \App\Http\Middleware\CheckClinicEnabled::class,
        \App\Http\Middleware\RealTimeSubscriptionCheck::class,
        \App\Http\Middleware\CheckSubscriptionAccess::class
    ])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\InventoryController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\InventoryController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\InventoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\InventoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\InventoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [App\Http\Controllers\InventoryController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Public subscription routes for viewing plans and submitting requests
    Route::get('/subscription', [\App\Http\Controllers\SubscriptionRequestController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/create', [\App\Http\Controllers\SubscriptionRequestController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [\App\Http\Controllers\SubscriptionRequestController::class, 'store'])->name('subscription.store');
    Route::get('/subscription/thank-you', [\App\Http\Controllers\SubscriptionRequestController::class, 'thankYou'])->name('subscription.thankyou');

    // Authenticated Subscription Routes (protected by middleware)
    Route::middleware([\App\Http\Middleware\AuthTenantStaff::class])->group(function () {
        Route::get('/subscription/{id}', [\App\Http\Controllers\SubscriptionRequestController::class, 'show'])->name('subscription.show');
        Route::get('/subscription/{id}/edit', [\App\Http\Controllers\SubscriptionRequestController::class, 'edit'])->name('subscription.edit');
        Route::put('/subscription/{id}', [\App\Http\Controllers\SubscriptionRequestController::class, 'update'])->name('subscription.update');
        Route::post('/subscription/{id}/cancel', [\App\Http\Controllers\SubscriptionRequestController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/subscription/{id}/cancel-request', [\App\Http\Controllers\SubscriptionRequestController::class, 'cancelRequest'])->name('subscription.cancel-request');
        
        // Admin actions (protected by admin role check in the controller)
        Route::post('/subscription/{id}/approve', [\App\Http\Controllers\SubscriptionRequestController::class, 'approve'])->name('subscription.approve');
        Route::post('/subscription/{id}/reject', [\App\Http\Controllers\SubscriptionRequestController::class, 'reject'])->name('subscription.reject');
        Route::post('/subscription/{id}/extend', [\App\Http\Controllers\SubscriptionRequestController::class, 'extend'])->name('subscription.extend');
    });
    
    // Admin subscription requests routes
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function() {
        Route::get('/subscription-requests', [\App\Http\Controllers\Admin\SubscriptionRequestController::class, 'index'])->name('admin.subscription-requests.index');
        Route::get('/subscription-requests/{id}', [\App\Http\Controllers\Admin\SubscriptionRequestController::class, 'show'])->name('admin.subscription-requests.show');
        Route::post('/subscription-requests/{id}/approve', [\App\Http\Controllers\Admin\SubscriptionRequestController::class, 'approve'])->name('admin.subscription-requests.approve');
        Route::post('/subscription-requests/{id}/reject', [\App\Http\Controllers\Admin\SubscriptionRequestController::class, 'reject'])->name('admin.subscription-requests.reject');
    });

    // Update the clinic info route to use the ClinicProfileController instead of an inline route definition
    Route::get('/clinic-info', [ClinicProfileController::class, 'edit'])->name('clinic.info')->middleware([
        \App\Http\Middleware\AuthTenantStaff::class,
        \App\Http\Middleware\CheckClinicActive::class,
        \App\Http\Middleware\CheckClinicEnabled::class
    ]);

    // Debug routes - only available in local environment
    if (app()->environment('local')) {
        Route::get('/debug/pets-check', [AppointmentController::class, 'debugPetsCheck'])->name('debug.pets-check');
    }
});
