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

// Protection against unregistered subdomains - apply at the top of the file
Route::middleware([ValidateTenantSubdomain::class])->group(function () {
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
            
            return view('dashboard', [
                'isSidebar' => true,
                'clinicName' => $clinic->name,
                'userRole' => $tenantUser->role,
                'userName' => $tenantUser->name,
                'staffCount' => $staffCount,
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
    
    // Auth routes for both admin and tenant users - moved inside tenant.validate middleware
    require __DIR__.'/auth.php';
    
    // Customer routes
    Route::middleware('guest')->group(function () {
        // Main site routes for clinic browsing
        Route::get('/clinics', [CustomerController::class, 'showClinicSelection'])->name('clinics.browse');
        Route::get('/clinic/{subdomain}/select', [CustomerController::class, 'selectClinic'])->name('clinic.select');
        
        // Subdomain-specific routes
        Route::get('/customer/register', [CustomerController::class, 'showRegistrationForm'])->name('customer.register');
        Route::post('/customer/register', [CustomerController::class, 'register']);
        Route::get('/customer/login', [CustomerController::class, 'showLoginForm'])->name('customer.login');
        Route::post('/customer/login', [CustomerController::class, 'login']);
    });

    Route::middleware(['customer.auth'])->group(function () {
        Route::get('/customer/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
        Route::post('/customer/logout', [CustomerController::class, 'logout'])->name('customer.logout');
    });

    // Tenant staff management routes - only for authenticated tenant users
    Route::middleware([\App\Http\Middleware\AuthTenantStaff::class])->group(function() {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('/staff/{id}/resend-invitation', [StaffController::class, 'resendInvitation'])->name('staff.resend-invitation');
        // Debug route - only for development
        Route::post('/staff/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
    });
});
