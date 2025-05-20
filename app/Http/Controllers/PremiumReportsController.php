<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SubdomainService;

class PremiumReportsController extends Controller
{
    protected $subdomainService;

    public function __construct(SubdomainService $subdomainService)
    {
        $this->subdomainService = $subdomainService;
    }

    /**
     * Display premium reports dashboard.
     */
    public function index()
    {
        $clinic = $this->subdomainService->getCurrentClinic();
        
        return view('premium.reports', [
            'clinic' => $clinic,
            'isSidebar' => true,
            'clinicName' => $clinic->name,
        ]);
    }

    /**
     * Generate advanced analytics report.
     */
    public function analytics()
    {
        $clinic = $this->subdomainService->getCurrentClinic();
        
        // Mock data for demonstration purposes
        $data = [
            'total_patients' => rand(100, 500),
            'monthly_visits' => rand(50, 200),
            'revenue' => rand(5000, 15000),
            'satisfaction_score' => rand(85, 99),
            'common_treatments' => [
                'Vaccination' => rand(10, 30),
                'Check-up' => rand(20, 50),
                'Surgery' => rand(5, 15),
                'Dental' => rand(10, 25),
                'Emergency' => rand(5, 20),
            ],
            'patient_growth' => [
                'Jan' => rand(5, 20),
                'Feb' => rand(5, 20),
                'Mar' => rand(5, 20),
                'Apr' => rand(5, 20),
                'May' => rand(5, 20),
                'Jun' => rand(5, 20),
            ]
        ];
        
        return view('premium.analytics', [
            'clinic' => $clinic,
            'isSidebar' => true,
            'clinicName' => $clinic->name,
            'data' => $data
        ]);
    }
} 