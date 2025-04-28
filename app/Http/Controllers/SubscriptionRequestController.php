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
        // For tenant users
        if (session()->has('tenant_user') && session()->has('current_clinic_id')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Get the clinic
            $clinic = \App\Models\Clinic::find($clinicId);
            if (!$clinic) {
                \Illuminate\Support\Facades\Log::error('Tenant subscription index: Clinic not found', [
                    'tenant_user' => $tenantUser,
                    'clinic_id' => $clinicId
                ]);
                
                return view('subscription.index', [
                    'subscriptions' => [],
                    'activeSubscription' => null,
                    'pendingSubscription' => null,
                    'isSidebar' => true,
                    'planDistribution' => ['free' => 0, 'basic' => 0, 'standard' => 0, 'business' => 0, 'premium' => 0],
                    'monthlyRequests' => ['labels' => [], 'data' => []],
                    'monthlyRevenue' => ['labels' => [], 'data' => []],
                    'stats' => [
                        'totalActive' => 0,
                        'totalPending' => 0,
                        'totalRejected' => 0,
                        'totalRevenue' => 0,
                        'averageRevenue' => 0
                    ]
                ]);
            }
            
            // Get subscription requests for this clinic only
            $subscriptions = \App\Models\SubscriptionRequest::where('clinic_id', $clinicId)->latest()->get();
            
            $activeSubscription = $subscriptions->where('status', 'active')->first();
            $pendingSubscription = $subscriptions->where('status', 'pending')->first();
            
            \Illuminate\Support\Facades\Log::info('Tenant subscription results', [
                'subscription_count' => $subscriptions->count(),
                'has_active' => !is_null($activeSubscription),
                'has_pending' => !is_null($pendingSubscription)
            ]);
            
            return view('subscription.index', [
                'subscriptions' => $subscriptions,
                'activeSubscription' => $activeSubscription,
                'pendingSubscription' => $pendingSubscription,
                'isSidebar' => true,
                'planDistribution' => ['free' => 0, 'basic' => 0, 'standard' => 0, 'business' => 0, 'premium' => 0],
                'monthlyRequests' => ['labels' => [], 'data' => []],
                'monthlyRevenue' => ['labels' => [], 'data' => []],
                'stats' => [
                    'totalActive' => 0,
                    'totalPending' => 0,
                    'totalRejected' => 0,
                    'totalRevenue' => 0,
                    'averageRevenue' => 0
                ]
            ]);
        }
        
        // Handle case when user is not logged in and not a tenant
        if (!auth()->check()) {
            return view('subscription.index', [
                'subscriptions' => [],
                'activeSubscription' => null,
                'pendingSubscription' => null,
                'isSidebar' => true,
                'planDistribution' => ['free' => 0, 'basic' => 0, 'standard' => 0, 'business' => 0, 'premium' => 0],
                'monthlyRequests' => ['labels' => [], 'data' => []],
                'monthlyRevenue' => ['labels' => [], 'data' => []],
                'stats' => [
                    'totalActive' => 0,
                    'totalPending' => 0,
                    'totalRejected' => 0,
                    'totalRevenue' => 0,
                    'averageRevenue' => 0
                ]
            ]);
        }
        
        // For regular users (not tenants)
        $user = Auth::user();
        
        // Check if the user has an associated clinic
        if (!$user->clinic && !$user->hasRole('admin')) {
            // For users without a clinic association
            return view('subscription.index', [
                'subscriptions' => [],
                'activeSubscription' => null,
                'pendingSubscription' => null,
                'isSidebar' => true,
                'planDistribution' => ['free' => 0, 'basic' => 0, 'standard' => 0, 'business' => 0, 'premium' => 0],
                'monthlyRequests' => ['labels' => [], 'data' => []],
                'monthlyRevenue' => ['labels' => [], 'data' => []],
                'stats' => [
                    'totalActive' => 0,
                    'totalPending' => 0,
                    'totalRejected' => 0,
                    'totalRevenue' => 0,
                    'averageRevenue' => 0
                ]
            ]);
        }
        
        $clinic = $user->clinic;
        
        // Get subscription data for admin or regular user
        $subscriptions = $user->hasRole('admin') 
            ? \App\Models\Subscription::with('user')->latest()->get()
            : \App\Models\Subscription::where('user_id', $user->id)->latest()->get();
        
        // Find active and pending subscriptions
        $activeSubscription = $subscriptions->where('status', 'active')->first();
        $pendingSubscription = $subscriptions->where('status', 'pending')->first();
        
        // For admin users, prepare stats data
        $planDistribution = ['free' => 0, 'basic' => 0, 'standard' => 0, 'business' => 0, 'premium' => 0];
        $monthlyRequests = ['labels' => [], 'data' => []];
        $monthlyRevenue = ['labels' => [], 'data' => []];
        $stats = [
            'totalActive' => 0,
            'totalPending' => 0,
            'totalRejected' => 0,
            'totalRevenue' => 0,
            'averageRevenue' => 0
        ];
        
        if ($user->hasRole('admin')) {
            // Get plan distribution
            foreach ($subscriptions->where('status', 'active') as $subscription) {
                if (isset($planDistribution[$subscription->plan])) {
                    $planDistribution[$subscription->plan]++;
                }
            }
            
            // Get monthly subscription requests (last 6 months)
            $labels = [];
            $requestData = [];
            $revenueData = [];
            $startDate = \Carbon\Carbon::now()->subMonths(5)->startOfMonth();
            
            for ($i = 0; $i < 6; $i++) {
                $currentDate = clone $startDate;
                $currentDate->addMonths($i);
                $nextMonth = clone $currentDate;
                $nextMonth->addMonth();
                
                $labels[] = $currentDate->format('M Y');
                
                // Count of new subscriptions for the month
                $count = \App\Models\Subscription::whereBetween('created_at', [
                    $currentDate->format('Y-m-d'),
                    $nextMonth->format('Y-m-d')
                ])->count();
                
                $requestData[] = $count;
                
                // Revenue from approved subscriptions for the month
                $revenue = \App\Models\Subscription::whereBetween('approved_at', [
                    $currentDate->format('Y-m-d'),
                    $nextMonth->format('Y-m-d')
                ])->where('status', 'active')
                  ->sum('amount_paid');
                
                $revenueData[] = $revenue;
            }
            
            $monthlyRequests = [
                'labels' => $labels,
                'data' => $requestData
            ];
            
            $monthlyRevenue = [
                'labels' => $labels,
                'data' => $revenueData
            ];
            
            // Calculate total statistics
            $stats = [
                'totalActive' => \App\Models\Subscription::where('status', 'active')->count(),
                'totalPending' => \App\Models\Subscription::where('status', 'pending')->count(),
                'totalRejected' => \App\Models\Subscription::where('status', 'rejected')->count(),
                'totalRevenue' => \App\Models\Subscription::where('status', 'active')->sum('amount_paid'),
                'averageRevenue' => \App\Models\Subscription::where('status', 'active')->avg('amount_paid') ?? 0
            ];
        }
        
        return view('subscription.index', [
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
            'pendingSubscription' => $pendingSubscription,
            'isSidebar' => true,
            'planDistribution' => $planDistribution,
            'monthlyRequests' => $monthlyRequests,
            'monthlyRevenue' => $monthlyRevenue,
            'stats' => $stats
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
            'plan' => 'required|string|in:free,basic,standard,business',
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
                'free' => 0,
                'basic' => 599,
                'standard' => 1599,
                'business' => 3599
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
            
            // Determine the user_id based on who is making the request
            $userId = null;
            
            // For authenticated users
            if (auth()->check()) {
                $userId = auth()->id();
            } 
            // For tenant users, use the clinic owner's ID
            elseif (session()->has('tenant_user') && session()->has('current_clinic_id')) {
                $clinicId = session('current_clinic_id');
                $clinic = \App\Models\Clinic::find($clinicId);
                
                if ($clinic && $clinic->user_id) {
                    $userId = $clinic->user_id;
                    \Illuminate\Support\Facades\Log::info('Tenant user creating subscription for clinic owner', [
                        'tenant_user' => session('tenant_user'),
                        'clinic_id' => $clinicId,
                        'owner_id' => $userId
                    ]);
                }
            }
            
            // Create the subscription without requiring a user_id
            $subscription = new \App\Models\Subscription([
                'user_id' => $userId, // Will be null for guests, set for authenticated or tenant users
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
        // Handle case for tenant users
        if (session()->has('tenant_user') && session()->has('current_clinic_id')) {
            $tenantUser = (object)session('tenant_user');
            $clinicId = session('current_clinic_id');
            
            // Get the clinic
            $clinic = \App\Models\Clinic::find($clinicId);
            if (!$clinic) {
                \Illuminate\Support\Facades\Log::error('Tenant subscription show: Clinic not found', [
                    'tenant_user' => $tenantUser,
                    'clinic_id' => $clinicId,
                    'subscription_id' => $id
                ]);
                
                return redirect()->route('subscription.index')
                    ->with('error', 'Clinic not found.');
            }
            
            // Get subscription directly by ID
            $subscription = \App\Models\Subscription::find($id);
            
            if (!$subscription) {
                \Illuminate\Support\Facades\Log::error('Tenant subscription show: Subscription not found', [
                    'tenant_user' => $tenantUser,
                    'clinic_id' => $clinicId,
                    'subscription_id' => $id
                ]);
                
                return redirect()->route('subscription.index')
                    ->with('error', 'Subscription not found.');
            }
            
            // Log the subscription details for debugging
            \Illuminate\Support\Facades\Log::info('Tenant subscription show: Found subscription', [
                'tenant_user_role' => $tenantUser->role,
                'clinic_id' => $clinicId,
                'clinic_user_id' => $clinic->user_id,
                'subscription_id' => $id,
                'subscription_user_id' => $subscription->user_id,
                'subscription_status' => $subscription->status
            ]);
            
            // Allow tenant staff to view the subscription if it's linked to their clinic
            return view('subscription.show', [
                'subscription' => $subscription,
                'isSidebar' => true,
            ]);
        }
        
        // Handle case for authenticated users
        if (auth()->check()) {
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
        
        return redirect()->route('login');
    }
    
    /**
     * Cancel a pending subscription request.
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Admins can cancel any subscription, users can only cancel their own pending
        if ($user->hasRole('admin')) {
            $subscription = \App\Models\Subscription::findOrFail($id);
        } else {
            $subscription = \App\Models\Subscription::where('user_id', $user->id)
                ->where('status', 'pending')
                ->findOrFail($id);
        }

        $subscription->status = 'cancelled';
        $subscription->cancelled_at = now();
        if ($request->has('cancellation_reason')) {
            $subscription->notes = ($subscription->notes ? $subscription->notes . "\n\n" : '') .
                "Cancelled by " . ($user->hasRole('admin') ? 'admin' : 'user') . " on " . now()->format('Y-m-d') . ". Reason: " . $request->cancellation_reason;
        }
        $subscription->save();

        return redirect()->route('subscription.index')
            ->with('success', 'Subscription has been cancelled successfully.');
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

        // Robustly update the correct clinic
        $clinic = null;
        if ($subscription->user_id && $subscription->user && $subscription->user->clinic) {
            $clinic = $subscription->user->clinic;
        }
        // Fallback: Try to find the clinic by guest_clinic_name or guest_email
        if (!$clinic && !empty($subscription->guest_clinic_name) && !empty($subscription->guest_email)) {
            $clinic = \App\Models\Clinic::where('name', $subscription->guest_clinic_name)
                ->orWhere('email', $subscription->guest_email)
                ->first();
        }
        // Fallback: Try to find the clinic by session (for tenant users)
        if (!$clinic && session()->has('current_clinic_id')) {
            $clinic = \App\Models\Clinic::find(session('current_clinic_id'));
        }
        // If we found a clinic, update it
        if ($clinic) {
            $clinic->is_subscription_active = true;
            $clinic->subscription_ends_at = $subscription->expired_at;
            $clinic->subscription_plan = $subscription->plan;
            $clinic->save();
        }
        
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
            'plan' => 'required|string|in:free,basic,standard,business',
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
                'free' => 0,
                'basic' => 599,
                'standard' => 1599,
                'business' => 3599
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
