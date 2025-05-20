<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionLimitController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display the limit reached page for a specific feature or resource.
     *
     * @param Request $request
     * @param string $limitType
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $limitType)
    {
        // Get the current clinic
        $clinicId = session('current_clinic_id');
        $clinic = Clinic::find($clinicId);

        if (!$clinic) {
            return redirect()->route('dashboard')->with('error', 'Clinic not found');
        }

        // Get the limit for the specified feature
        $limit = $this->subscriptionService->getLimitForFeature($clinic, $limitType . '_limit');

        // Convert limit type to human-readable format
        $limitTypeHuman = str_replace('_', ' ', $limitType);
        $limitTypeHuman = ucfirst($limitTypeHuman);

        return view('subscription.limit-reached', [
            'clinic' => $clinic,
            'limitType' => $limitTypeHuman,
            'limit' => $limit
        ]);
    }
} 