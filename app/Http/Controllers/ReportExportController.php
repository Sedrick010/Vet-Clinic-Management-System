<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SubdomainService;
use App\Services\TenantDatabaseService;

require_once base_path('vendor/setasign/fpdf/fpdf.php');

class ReportExportController extends Controller
{
    protected $subdomainService;
    protected $tenantDatabaseService;

    public function __construct(SubdomainService $subdomainService, TenantDatabaseService $tenantDatabaseService)
    {
        $this->subdomainService = $subdomainService;
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Handle exporting reports to PDF.
     *
     * @param Request $request
     * @param string $type The type of report to export
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportPdf(Request $request, $type)
    {
        // Get the clinic from subdomain
        $clinic = $this->subdomainService->getCurrentClinic();
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Direct check for subscription plan to prevent middleware bypass
        if ($clinic->subscription_plan === 'free' || !$clinic->is_subscription_active) {
            return redirect()->route('subscription.index')
                ->with('upgrade_required', true)
                ->with('error', 'PDF export is only available on paid plans (Basic, Standard, and Business). Please upgrade your subscription to access this feature.');
        }

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Parameters used for some report types
        $startDate = $request->query('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        // Based on the report type, generate the corresponding PDF
        switch ($type) {
            case 'dashboard':
                return $this->generateDashboardPdf($clinic);
            case 'inventory':
                return $this->generateInventoryPdf($clinic);
            case 'appointment':
                return $this->generateAppointmentPdf($clinic, $startDate, $endDate);
            case 'client':
                return $this->generateClientPdf($clinic, $startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }
    
    /**
     * Handle exporting reports to CSV.
     *
     * @param Request $request
     * @param string $type The type of report to export
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportCsv(Request $request, $type)
    {
        // Get the clinic and switch to tenant database
        $clinic = $this->subdomainService->getCurrentClinic();

        if (!$clinic) {
            return redirect()->route('dashboard')->with('error', 'Clinic not found.');
        }

        // Switch to tenant database
        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Parameters used for some report types
        $startDate = $request->query('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        // Based on the report type, generate the corresponding CSV
        switch ($type) {
            case 'dashboard':
                return $this->generateDashboardCsv($clinic);
            case 'inventory':
                return $this->generateInventoryCsv($clinic);
            case 'appointment':
                return $this->generateAppointmentCsv($clinic, $startDate, $endDate);
            case 'client':
                return $this->generateClientCsv($clinic, $startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }

    /**
     * Generate dashboard summary report as PDF.
     */
    private function generateDashboardPdf($clinic)
    {
        // To be implemented based on dashboard needs
        return response()->download('to_be_implemented.pdf');
    }

    /**
     * Generate inventory report as PDF.
     */
    private function generateInventoryPdf($clinic)
    {
        // To be implemented based on inventory needs
        return response()->download('to_be_implemented.pdf');
    }

    /**
     * Generate appointment analytics report as PDF.
     */
    private function generateAppointmentPdf($clinic, $startDate, $endDate)
    {
        // To be implemented based on appointment needs
        return response()->download('to_be_implemented.pdf');
    }

    /**
     * Generate client/patient report as PDF.
     */
    private function generateClientPdf($clinic, $startDate, $endDate)
    {
        // To be implemented based on client/patient needs
        return response()->download('to_be_implemented.pdf');
    }
    
    /**
     * Generate dashboard summary report as CSV.
     */
    private function generateDashboardCsv($clinic)
    {
        // To be implemented based on dashboard needs
        return response()->download('to_be_implemented.csv');
    }

    /**
     * Generate inventory report as CSV.
     */
    private function generateInventoryCsv($clinic)
    {
        // To be implemented based on inventory needs
        return response()->download('to_be_implemented.csv');
    }

    /**
     * Generate appointment analytics report as CSV.
     */
    private function generateAppointmentCsv($clinic, $startDate, $endDate)
    {
        // To be implemented based on appointment needs
        return response()->download('to_be_implemented.csv');
    }

    /**
     * Generate client/patient report as CSV.
     */
    private function generateClientCsv($clinic, $startDate, $endDate)
    {
        // To be implemented based on client/patient needs
        return response()->download('to_be_implemented.csv');
    }
} 