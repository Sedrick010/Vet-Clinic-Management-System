<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SubscriptionRequestController extends Controller
{
    /**
     * Display the subscription request form or current status.
     */
    public function index()
    {
        // Handle case when user is not logged in
        if (!auth()->check()) {
            return view('subscription.index', [
                'subscriptions' => [],
                'activeSubscription' => null,
                'pendingSubscription' => null,
                'isSidebar' => true,
                'planDistribution' => ['basic' => 0, 'standard' => 0, 'premium' => 0],
                'monthlyRequests' => ['labels' => [], 'data' => []]
            ]);
        }
        
        $user = Auth::user();
        
        // Check if the user has an associated clinic
        if (!$user->clinic) {
            // For admin users or users without a clinic association
            return view('subscription.index', [
                'subscriptions' => [],
                'activeSubscription' => null,
                'pendingSubscription' => null,
                'isSidebar' => true,
                'planDistribution' => ['basic' => 0, 'standard' => 0, 'premium' => 0],
                'monthlyRequests' => ['labels' => [], 'data' => []]
            ]);
        }
        
        $clinic = $user->clinic;
        $activeSubscription = null;
        $pendingSubscription = null;
        
        // Get subscription data
        $subscriptions = $user->hasRole('admin') 
            ? \App\Models\Subscription::with('user')->latest()->get()
            : \App\Models\Subscription::where('user_id', $user->id)->latest()->get();
            
        // Find active and pending subscriptions
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 'active') {
                $activeSubscription = $subscription;
            } elseif ($subscription->status == 'pending') {
                $pendingSubscription = $subscription;
            }
        }
        
        // For admin users, prepare stats data
        $planDistribution = ['basic' => 0, 'standard' => 0, 'premium' => 0];
        $monthlyRequests = ['labels' => [], 'data' => []];
        
        if ($user->hasRole('admin')) {
            // Get plan distribution
            foreach ($subscriptions as $subscription) {
                if (isset($planDistribution[$subscription->plan])) {
                    $planDistribution[$subscription->plan]++;
                }
            }
            
            // Get monthly subscription requests (last 6 months)
            $labels = [];
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $labels[] = $month->format('M Y');
                $count = \App\Models\Subscription::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $data[] = $count;
            }
            $monthlyRequests = [
                'labels' => $labels,
                'data' => $data
            ];
        }
        
        return view('subscription.index', [
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
            'pendingSubscription' => $pendingSubscription,
            'isSidebar' => true,
            'planDistribution' => $planDistribution,
            'monthlyRequests' => $monthlyRequests
        ]);
    }
    
    /**
     * Show the form for creating a new subscription request.
     */
    public function create()
    {
        // Default values
        $selectedPlan = request('plan') ?: 'standard';
        
        return view('subscription.create', [
            'isSidebar' => true,
            'selectedPlan' => $selectedPlan
        ]);
    }
    
    /**
     * Store a newly created subscription request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:basic,standard,premium',
            'duration' => 'required|integer|min:1|max:12',
            'payment_method' => 'required|string|in:bank_transfer,gcash,credit_card',
            'payment_reference' => 'nullable|string|max:255',
            'payment_details' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'auto_renew' => 'nullable|boolean',
            'clinic_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
        ]);
        
        try {
            // Calculate amount based on plan and duration
            $planPrices = [
                'basic' => 4999,
                'standard' => 4999,
                'premium' => 4999
            ];
            
            $discounts = [
                1 => 0,
                3 => 0.05,
                6 => 0.10,
                12 => 0.15
            ];
            
            $basePrice = $planPrices[$validated['plan']];
            $discount = $discounts[$validated['duration']] ?? 0;
            $amount = $basePrice * $validated['duration'] * (1 - $discount);
            
            // Create the subscription without requiring a user_id
            $subscription = new \App\Models\Subscription([
                'user_id' => auth()->check() ? auth()->id() : null, // Explicitly set to null for guests
                'plan' => $validated['plan'],
                'duration' => $validated['duration'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_details' => $validated['payment_details'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'amount_paid' => $amount,
                'status' => 'pending',
                'auto_renew' => isset($validated['auto_renew']),
                'guest_clinic_name' => $validated['clinic_name'],
                'guest_email' => $validated['contact_email'],
                'guest_phone' => $validated['contact_phone'],
            ]);
            
            $subscription->save();
            
            // Notify all admin users about the new subscription request
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                // Send notification to admin (implement the notification logic here)
                // For example, you could use Laravel's notification system:
                // $admin->notify(new \App\Notifications\NewSubscriptionRequest($subscription));
                
                // For now, just log the notification
                Log::info('Admin notification for new subscription request', [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'subscription_id' => $subscription->id,
                    'plan' => $subscription->plan,
                    'guest_clinic_name' => $subscription->guest_clinic_name,
                    'guest_email' => $subscription->guest_email
                ]);
            }
            
            return redirect()->route('subscription.thankyou')
                ->with('success', 'Your subscription request has been submitted successfully. Please allow 1-2 business days for processing. The administrator will review your request and contact you at the email provided.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create subscription: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('subscription.create')
                ->with('error', 'Failed to submit subscription request. Please try again: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Display a thank you page after subscription submission
     */
    public function thankYou()
    {
        return view('subscription.thankyou', [
            'isSidebar' => true
        ]);
    }
    
    /**
     * Display the specified subscription request details.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Check if the user exists
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get the subscription
        $subscription = null;
        
        if ($user->hasRole('admin')) {
            // Admin can view any subscription
            $subscription = \App\Models\Subscription::with('user')->findOrFail($id);
        } else {
            // Regular users can only view their own subscriptions
            $subscription = \App\Models\Subscription::where('user_id', $user->id)
                ->findOrFail($id);
        }
        
        return view('subscription.show', [
            'subscription' => $subscription,
            'isSidebar' => true,
        ]);
    }
    
    /**
     * Cancel a pending subscription request.
     */
    public function cancel($id)
    {
        $user = Auth::user();
        
        // Check if the user exists
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get the subscription and ensure it belongs to the user
        $subscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);
        
        $subscription->status = 'cancelled';
        $subscription->cancelled_at = now();
        $subscription->save();
        
        return redirect()->route('subscription.index')
            ->with('success', 'Subscription request has been cancelled successfully.');
    }
    
    /**
     * Cancel a pending subscription request by a user.
     */
    public function cancelRequest($id)
    {
        return $this->cancel($id);
    }
    
    /**
     * Admin approve a subscription request.
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if the user is an admin
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'expired_at' => 'required|date|after:today',
        ]);
        
        $subscription = \App\Models\Subscription::findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            return redirect()->route('subscription.show', $id)
                ->with('error', 'Only pending subscriptions can be approved.');
        }
        
        $subscription->status = 'active';
        $subscription->approved_at = now();
        $subscription->expired_at = $validated['expired_at'];
        $subscription->save();
        
        // Notify the user about approval
        // Code for notification would go here...
        
        return redirect()->route('subscription.show', $id)
            ->with('success', 'Subscription has been approved successfully.');
    }
    
    /**
     * Admin reject a subscription request.
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if the user is an admin
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $subscription = \App\Models\Subscription::findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            return redirect()->route('subscription.show', $id)
                ->with('error', 'Only pending subscriptions can be rejected.');
        }
        
        $subscription->status = 'rejected';
        $subscription->rejected_at = now();
        $subscription->rejection_reason = $validated['rejection_reason'];
        $subscription->save();
        
        // Notify the user about rejection
        // Code for notification would go here...
        
        return redirect()->route('subscription.show', $id)
            ->with('success', 'Subscription request has been rejected.');
    }
    
    /**
     * Admin extend a subscription.
     */
    public function extend(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if the user is an admin
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'extension_months' => 'required|integer|min:1|max:12',
            'extension_reason' => 'required|string|max:1000',
        ]);
        
        $subscription = \App\Models\Subscription::findOrFail($id);
        
        if ($subscription->status !== 'active') {
            return redirect()->route('subscription.show', $id)
                ->with('error', 'Only active subscriptions can be extended.');
        }
        
        // Calculate new expiry date
        $currentExpiry = \Carbon\Carbon::parse($subscription->expired_at);
        $newExpiry = $currentExpiry->addMonths($validated['extension_months']);
        
        $subscription->expired_at = $newExpiry;
        $subscription->notes = ($subscription->notes ? $subscription->notes . "\n\n" : '') . 
            "Extended by " . $validated['extension_months'] . " months on " . now()->format('Y-m-d') . 
            ". Reason: " . $validated['extension_reason'];
        $subscription->save();
        
        return redirect()->route('subscription.show', $id)
            ->with('success', 'Subscription has been extended successfully.');
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        // Check if the user exists
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get the subscription
        $subscription = null;
        
        if ($user->hasRole('admin')) {
            // Admin can edit any subscription
            $subscription = \App\Models\Subscription::with('user')->findOrFail($id);
        } else {
            // Regular users can only edit their own subscriptions
            $subscription = \App\Models\Subscription::where('user_id', $user->id)
                ->findOrFail($id);
                
            // Only allow editing of pending subscriptions
            if ($subscription->status !== 'pending') {
                return redirect()->route('subscription.show', $id)
                    ->with('error', 'Only pending subscriptions can be edited.');
            }
        }
        
        return view('subscription.edit', [
            'subscription' => $subscription,
            'isSidebar' => true,
        ]);
    }
    
    /**
     * Update the specified subscription in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if the user exists
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get the subscription
        $subscription = null;
        
        if ($user->hasRole('admin')) {
            // Admin can update any subscription
            $subscription = \App\Models\Subscription::findOrFail($id);
        } else {
            // Regular users can only update their own pending subscriptions
            $subscription = \App\Models\Subscription::where('user_id', $user->id)
                ->where('status', 'pending')
                ->findOrFail($id);
        }
        
        // Validate the request data
        $validated = $request->validate([
            'plan' => 'required|string|in:basic,standard,premium',
            'duration' => 'required|integer|min:1|max:12',
            'payment_method' => 'required|string|in:bank_transfer,gcash,credit_card,maya',
            'payment_reference' => 'nullable|string|max:255',
            'payment_details' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'auto_renew' => 'nullable|boolean',
        ]);
        
        try {
            // Calculate amount based on plan and duration
            $planPrices = [
                'basic' => 4999,
                'standard' => 4999,
                'premium' => 4999
            ];
            
            $discounts = [
                1 => 0,
                3 => 0.05,
                6 => 0.10,
                12 => 0.15
            ];
            
            $basePrice = $planPrices[$validated['plan']];
            $discount = $discounts[$validated['duration']] ?? 0;
            $amount = $basePrice * $validated['duration'] * (1 - $discount);
            
            // Update the subscription
            $subscription->plan = $validated['plan'];
            $subscription->duration = $validated['duration'];
            $subscription->payment_method = $validated['payment_method'];
            $subscription->payment_reference = $validated['payment_reference'] ?? null;
            $subscription->payment_details = $validated['payment_details'] ?? null;
            $subscription->notes = $validated['notes'] ?? null;
            $subscription->amount_paid = $amount;
            $subscription->auto_renew = isset($validated['auto_renew']);
            
            // If admin is updating, they can change the status
            if ($user->hasRole('admin') && $request->has('status')) {
                $subscription->status = $request->status;
            }
            
            $subscription->save();
            
            return redirect()->route('subscription.show', $subscription->id)
                ->with('success', 'Subscription has been updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update subscription: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('subscription.edit', $id)
                ->with('error', 'Failed to update subscription. Please try again.')
                ->withInput();
        }
    }
}
