<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicSelectorController;
use Illuminate\Support\Facades\Route;

// Main application routes (accessible via the main domain)
Route::domain(config('app.url'))->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Clinic registration routes
    Route::get('/register-clinic', [ClinicController::class, 'create'])->name('clinics.create');
    Route::post('/register-clinic', [ClinicController::class, 'store'])->name('clinics.store');
});

// Default route for local development (without subdomain)
Route::get('/', function () {
    return view('welcome');
});

// Clinic selector routes
Route::middleware(['auth'])->group(function() {
    Route::get('/select-clinic', [ClinicSelectorController::class, 'index'])->name('clinics.select');
    Route::get('/switch-clinic/{clinicId}', [ClinicSelectorController::class, 'switchClinic'])->name('clinics.switch');
});

// Tenant routes (accessible via subdomain or directly in local development)
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['isSidebar' => true]);
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});
