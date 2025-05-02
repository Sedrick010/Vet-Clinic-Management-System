<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Services\SubdomainService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionRequestController extends Controller
{
    protected $subdomainService;
    protected $subscriptionService;

    public function __construct(SubdomainService $subdomainService, SubscriptionService $subscriptionService)
    {
        $this->subdomainService = $subdomainService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display the subscription request form or current status.
     */
    public function index()
    {
        // Get statistics for admin view
        $stats = [
            'totalActive' => SubscriptionRequest::where('status', 'approved')->count(),
            'totalPending' => SubscriptionRequest::where('status', 'pending')->count(),
            'totalRevenue' => SubscriptionRequest::where('status', 'approved')->sum('amount_paid'),
            'averageRevenue' => SubscriptionRequest::where('status', 'approved')->avg('amount_paid') ?? 0,
        ];
        
        // Get plan distribution data for the chart
        $planDistribution = [
            'free' => Clinic::where('subscription_plan', 'free')->where('is_subscription_active', true)->count(),
            'basic' => Clinic::where('subscription_plan', 'basic')->where('is_subscription_active', true)->count(),
            'standard' => Clinic::where('subscription_plan', 'standard')->where('is_subscription_active', true)->count(),
            'premium' => Clinic::where('subscription_plan', 'premium')->where('is_subscription_active', true)->count(),
        ];
        
        // Add monthly requests data for the chart (last 6 months)
        $monthlyRequests = [
            'labels' => [],
            'data' => []
        ];

        // Get monthly revenue data for the chart (also referenced in the view)
        $monthlyRevenue = [
            'labels' => [],
            'data' => []
        ];

        // Generate data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            // Add to labels array
            $monthlyRequests['labels'][] = $monthName;
            $monthlyRevenue['labels'][] = $monthName;
            
            // Count requests for this month
            $requestCount = SubscriptionRequest::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyRequests['data'][] = $requestCount;
            
            // Sum revenue for this month
            $revenue = SubscriptionRequest::where('status', 'approved')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount_paid');
            $monthlyRevenue['data'][] = $revenue;
        }
        
        // Get clinic and subscription info
        $clinic = null;
        $activeSubscription = null;
        $pendingSubscription = null;
        
        // Check if user is logged in
        if (Auth::check()) {
            // Admin should see all subscriptions
            if (Auth::user()->role === 'admin') {
                // Get all subscription requests for admin display
                $allRequests = SubscriptionRequest::with(['clinic', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                // Log the count of requests for debugging
                Log::info('Admin subscription dashboard', [
                    'total_requests' => $allRequests->count(),
                    'pending_requests' => $allRequests->where('status', 'pending')->count(),
                    'approved_requests' => $allRequests->where('status', 'approved')->count(),
                    'cancelled_requests' => $allRequests->where('status', 'cancelled')->count(),
                    'has_clinic_relation' => $allRequests->filter(function($request) {
                        return $request->clinic_id && $request->clinic;
                    })->count()
                ]);
                
                return view('subscription.index', compact(
                    'stats', 
                    'planDistribution', 
                    'monthlyRequests', 
                    'monthlyRevenue', 
                    'allRequests'
                ));
            }
            
            // Regular user - get their clinic
            $user = Auth::user();
            if ($user->clinic_id) {
                $clinic = Clinic::find($user->clinic_id);
            }
        } else {
            // Try to get clinic from tenant session
            if (session()->has('tenant_user') && session()->has('current_clinic_id')) {
                $clinic = Clinic::find(session('current_clinic_id'));
            }
        }
        
        // If we have a clinic, get subscription details
        if ($clinic) {
            // Get pending subscription request if any
            $pendingSubscription = SubscriptionRequest::where('clinic_id', $clinic->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
                
            // Get active subscription request
            $activeSubscription = SubscriptionRequest::where('clinic_id', $clinic->id)
                ->where('status', 'approved')
                ->where('expired_at', '>', now())
                ->latest()
                ->first();
        }
        
        // Get subscription plan features
        $planFeatures = [
            'free' => $this->subscriptionService->getPlanDescription('free'),
            'basic' => $this->subscriptionService->getPlanDescription('basic'),
            'premium' => $this->subscriptionService->getPlanDescription('premium'),
        ];
        
        return view('subscription.index', compact('stats', 'planDistribution', 'clinic', 'activeSubscription', 'pendingSubscription', 'planFeatures', 'monthlyRequests', 'monthlyRevenue'));
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
            'plan' => 'required|string|in:basic,standard,business',
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
            $clinicId = null;
            
            // For authenticated users
            if (auth()->check()) {
                $userId = auth()->id();
                // If the user has a clinic_id, use it
                if (auth()->user()->clinic_id) {
                    $clinicId = auth()->user()->clinic_id;
                }
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
            
            // Log the subscription request data for debugging
            Log::info('Creating subscription request', [
                'user_id' => $userId,
                'clinic_id' => $clinicId,
                'plan' => $validated['plan'],
                'amount' => $amount,
                'is_authenticated' => auth()->check(),
                'has_tenant_session' => session()->has('tenant_user'),
                'current_clinic_id_in_session' => session('current_clinic_id')
            ]);
            
            // Create the subscription without requiring a user_id
            $subscription = new SubscriptionRequest([
                'user_id' => $userId, // Will be null for guests, set for authenticated or tenant users
                'clinic_id' => $clinicId, // Use the determined clinic_id instead of directly from session
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
            
            // Ensure clinic_id is set before saving
            if (empty($subscription->clinic_id) && session()->has('current_clinic_id')) {
                $subscription->clinic_id = session('current_clinic_id');
                Log::info('Setting clinic_id from session', [
                    'clinic_id' => $subscription->clinic_id
                ]);
            }
            
            $subscription->save();
            
            // Log the saved subscription for verification
            Log::info('Subscription request saved', [
                'subscription_id' => $subscription->id,
                'clinic_id' => $subscription->clinic_id,
                'user_id' => $subscription->user_id,
                'status' => $subscription->status
            ]);
            
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
            $subscription = SubscriptionRequest::find($id);
            
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
        
        // Get the subscription and ensure it belongs to the user
        $subscription = SubscriptionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);
        
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
        $user = Auth::user();
        
        // Check if the user exists or if there's a clinic session
        if (!$user && !session()->has('tenant_user')) {
            return redirect()->route('login');
        }
        
        // Get the subscription request
        $subscriptionRequest = SubscriptionRequest::findOrFail($id);
        
        // Check if the user has permission to cancel this request
        $clinicId = session('current_clinic_id');
        if (!$user && $clinicId != $subscriptionRequest->clinic_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($user && !$user->hasRole('admin') && $subscriptionRequest->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only pending subscriptions can be cancelled
        if ($subscriptionRequest->status !== 'pending') {
            return redirect()->route('subscription.show', $id)
                ->with('error', 'Only pending subscription requests can be cancelled.');
        }
        
        $subscriptionRequest->status = 'cancelled';
        $subscriptionRequest->cancelled_at = now();
        $subscriptionRequest->save();
        
        return redirect()->route('subscription.index')
            ->with('success', 'Subscription request has been cancelled successfully.');
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
            'plan' => 'required|string|in:basic,standard,business',
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
