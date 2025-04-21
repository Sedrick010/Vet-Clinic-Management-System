<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionRequestController extends Controller
{
    /**
     * Display a listing of subscription requests.
     */
    public function index()
    {
        $pendingRequests = Subscription::where('status', 'pending')
                                     ->latest()
                                     ->get();
        
        $processedRequests = Subscription::whereIn('status', ['approved', 'rejected'])
                                        ->latest()
                                        ->paginate(10);
        
        return view('admin.subscription-requests.index', [
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests,
            'isSidebar' => true,
        ]);
    }

    /**
     * Display the specified subscription request.
     */
    public function show($id)
    {
        $subscriptionRequest = Subscription::findOrFail($id);
        
        return view('admin.subscription-requests.show', [
            'subscriptionRequest' => $subscriptionRequest,
            'isSidebar' => true,
        ]);
    }
}
