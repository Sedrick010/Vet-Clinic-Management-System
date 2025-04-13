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
            
            return view('dashboard', [
                'isSidebar' => true,
                'clinicName' => $clinic->name,
                'userRole' => $tenantUser->role,
                'userName' => $tenantUser->name,
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
    
});
