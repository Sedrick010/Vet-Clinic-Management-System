<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

abstract class Controller
{
    /**
     * Ensure that tenant database connection is configured and working
     *
     * @return bool True if connection is established, false otherwise
     */
    protected function ensureTenantConnection(): bool
    {
        if (!session()->has('current_clinic_id')) {
            Log::warning('No clinic ID in session, cannot establish tenant connection');
            return false;
        }

        $clinicId = session('current_clinic_id');
        
        try {
            // Get the clinic information
            $clinic = \App\Models\Clinic::findOrFail($clinicId);
            
            // Configure tenant database connection if it doesn't exist or reconnect if it failed
            if (!config('database.connections.tenant') || !DB::connection('tenant')->getDatabaseName()) {
                app(\App\Services\TenantDatabaseService::class)->switchToTenant($clinic);
            }
            
            // Test the connection
            DB::connection('tenant')->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Error connecting to tenant database: ' . $e->getMessage(), [
                'clinic_id' => $clinicId ?? 'unknown'
            ]);
            return false;
        }
    }
}
