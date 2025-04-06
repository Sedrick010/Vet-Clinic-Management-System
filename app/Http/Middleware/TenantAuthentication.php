<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;
use App\Services\TenantDatabaseService;

class TenantAuthentication
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as a tenant user
        if (!session()->has('tenant_user')) {
            return redirect()->route('login');
        }
        
        // Check if clinic exists and is approved
        $clinicId = session('current_clinic_id');
        $clinic = Clinic::find($clinicId);
        
        if (!$clinic || $clinic->approval_status !== 'approved') {
            return redirect()->route('clinics.pending');
        }
        
        // Switch to tenant database for this request
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Store clinic in request for convenience
        $request->attributes->set('clinic', $clinic);
        $request->attributes->set('tenant_user', session('tenant_user'));
        
        return $next($request);
    }
}
