<?php

use App\Http\Controllers\Admin\ClinicSubscriptionController;
use Illuminate\Support\Facades\Route;

// Admin routes for clinic subscription management
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin/clinics/{clinic}')->name('admin.clinics.')->group(function () {
        // Subscription management
        Route::get('/subscription', [ClinicSubscriptionController::class, 'edit'])->name('subscription.edit');
        Route::put('/subscription', [ClinicSubscriptionController::class, 'update'])->name('subscription.update');
        Route::put('/subscription/toggle-activation', [ClinicSubscriptionController::class, 'toggleActivation'])->name('subscription.toggle-activation');
    });
}); 