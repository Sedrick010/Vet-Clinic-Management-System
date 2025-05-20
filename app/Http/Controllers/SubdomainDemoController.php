<?php

namespace App\Http\Controllers;

use App\Facades\Subdomain;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubdomainDemoController extends Controller
{
    /**
     * Show a demo page for subdomain handling
     */
    public function index(Request $request): View
    {
        // Get information about the current request
        $host = $request->getHost();
        $currentSubdomain = Subdomain::current();
        $currentClinic = Subdomain::getCurrentClinic();
        
        // Get all clinics to display links
        $clinics = Clinic::where('approval_status', 'approved')->get();
        
        // Generate URLs for each clinic
        $clinicUrls = [];
        foreach ($clinics as $clinic) {
            $clinicUrls[$clinic->id] = [
                'name' => $clinic->name,
                'subdomain' => $clinic->subdomain,
                'home_url' => Subdomain::url($clinic),
                'dashboard_url' => Subdomain::route($clinic, 'dashboard'),
                'demo_url' => Subdomain::url($clinic, '/subdomain-demo')
            ];
        }
        
        // Return the view with subdomain information
        return view('subdomain-demo', [
            'host' => $host,
            'current_subdomain' => $currentSubdomain,
            'current_clinic' => $currentClinic,
            'clinic_urls' => $clinicUrls,
            'main_domain' => parse_url(config('app.url'), PHP_URL_HOST),
            'is_main_domain' => !$currentSubdomain,
        ]);
    }
} 