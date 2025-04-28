<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        
        $processedRequests = Subscription::whereIn('status', ['approved', 'rejected', 'active'])
                                        ->latest()
                                        ->paginate(10);
        
        // Plan distribution data for chart
        $planDistribution = [
            'free' => Subscription::where('plan', 'free')->where('status', 'active')->count(),
            'basic' => Subscription::where('plan', 'basic')->where('status', 'active')->count(),
            'standard' => Subscription::where('plan', 'standard')->where('status', 'active')->count(),
            'premium' => Subscription::where('plan', 'premium')->where('status', 'active')->count() + 
                         Subscription::where('plan', 'business')->where('status', 'active')->count()
        ];
        
        // Monthly subscription requests data (last 6 months)
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $months = [];
        $monthlyData = [];
        $revenueData = [];
        
        for ($i = 0; $i < 6; $i++) {
            $currentDate = clone $startDate;
            $currentDate->addMonths($i);
            $nextMonth = clone $currentDate;
            $nextMonth->addMonth();
            
            $months[] = $currentDate->format('M Y');
            
            // Count of new subscriptions for the month
            $count = Subscription::whereBetween('created_at', [
                $currentDate->format('Y-m-d'),
                $nextMonth->format('Y-m-d')
            ])->count();
            
            $monthlyData[] = $count;
            
            // Revenue from approved subscriptions for the month
            $revenue = Subscription::whereBetween('approved_at', [
                $currentDate->format('Y-m-d'),
                $nextMonth->format('Y-m-d')
            ])->where('status', 'active')
              ->sum('amount_paid');
            
            $revenueData[] = $revenue;
        }
        
        $monthlyRequests = [
            'labels' => $months,
            'data' => $monthlyData
        ];
        
        $monthlyRevenue = [
            'labels' => $months,
            'data' => $revenueData
        ];
        
        // Calculate total statistics
        $stats = [
            'totalActive' => Subscription::where('status', 'active')->count(),
            'totalPending' => Subscription::where('status', 'pending')->count(),
            'totalRejected' => Subscription::where('status', 'rejected')->count(),
            'totalRevenue' => Subscription::where('status', 'active')->sum('amount_paid'),
            'averageRevenue' => Subscription::where('status', 'active')->avg('amount_paid') ?? 0
        ];
        
        return view('admin.subscription-requests.index', [
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests,
            'planDistribution' => $planDistribution,
            'monthlyRequests' => $monthlyRequests,
            'monthlyRevenue' => $monthlyRevenue,
            'stats' => $stats,
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

    /**
     * Approve a subscription request.
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'expiration_date' => 'required|date|after:today',
        ]);
        
        $subscription = Subscription::findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            return redirect()->route('admin.subscription-requests.show', $id)
                ->with('error', 'Only pending subscription requests can be approved.');
        }
        
        // Calculate expiration date based on duration
        $expirationDate = Carbon::now()->addMonths($subscription->duration);
        if (!empty($validated['expiration_date'])) {
            $expirationDate = Carbon::parse($validated['expiration_date']);
        }
        
        $subscription->status = 'active';
        $subscription->approved_at = Carbon::now();
        $subscription->admin_notes = $validated['admin_notes'] ?? null;
        $subscription->expired_at = $expirationDate;
        $subscription->save();
        
        // Update the clinic's subscription status if applicable
        if ($subscription->user_id) {
            $user = $subscription->user;
            if ($user && $user->clinic) {
                $clinic = $user->clinic;
                $clinic->is_subscription_active = true;
                $clinic->subscription_ends_at = $expirationDate;
                $clinic->subscription_plan = $subscription->plan;
                $clinic->save();
            }
        } else if (!empty($subscription->guest_clinic_name) && !empty($subscription->guest_email)) {
            // Handle guest subscription where no user_id exists
            \Illuminate\Support\Facades\Log::info('Guest subscription approved', [
                'subscription_id' => $subscription->id,
                'guest_clinic_name' => $subscription->guest_clinic_name,
                'guest_email' => $subscription->guest_email
            ]);
            // Optional: Check if there's a clinic with the same name or email
            $clinic = Clinic::where('name', $subscription->guest_clinic_name)
                           ->orWhere('email', $subscription->guest_email)
                           ->first();
            if ($clinic) {
                $clinic->is_subscription_active = true;
                $clinic->subscription_ends_at = $expirationDate;
                $clinic->subscription_plan = $subscription->plan;
                $clinic->save();
            }
        }
        
        // Could add email notification here
        
        return redirect()->route('admin.subscription-requests.index')
            ->with('success', 'Subscription request has been approved successfully.');
    }
    
    /**
     * Reject a subscription request.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $subscription = Subscription::findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            return redirect()->route('admin.subscription-requests.show', $id)
                ->with('error', 'Only pending subscription requests can be rejected.');
        }
        
        $subscription->status = 'rejected';
        $subscription->rejected_at = Carbon::now();
        $subscription->rejection_reason = $validated['rejection_reason'];
        $subscription->admin_notes = $validated['admin_notes'] ?? null;
        $subscription->save();
        
        // Could add email notification here
        
        return redirect()->route('admin.subscription-requests.index')
            ->with('success', 'Subscription request has been rejected.');
    }
}
