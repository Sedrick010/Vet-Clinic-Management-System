<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Notifications\ClinicSubscriptionNotification;
use App\Services\TenantDatabaseService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ClinicSubscriptionController extends Controller
{
    protected $subscriptionService;
    
    public function __construct(SubscriptionService $subscriptionService) 
    {
        $this->subscriptionService = $subscriptionService;
    }
    
    /**
     * Display the subscription management form.
     */
    public function edit(Clinic $clinic): View
    {
        // Get all available subscription plans from the service
        $subscriptionPlans = [
            'free' => 'Free Plan',
            'basic' => 'Basic Plan',
            'standard' => 'Standard Plan',
            'business' => 'Business Plan',
            'premium' => 'Premium Plan',
        ];
        
        return view('admin.clinics.subscription', [
            'clinic' => $clinic,
            'subscriptionPlans' => $subscriptionPlans,
            'isSidebar' => true,
        ]);
    }
    
    /**
     * Update the clinic's subscription.
     */
    public function update(Request $request, Clinic $clinic): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:free,basic,standard,business,premium',
            'subscription_ends_at' => 'nullable|date|after:now',
            'is_subscription_active' => 'boolean',
            'deactivation_reason' => 'nullable|string|required_if:is_subscription_active,0',
        ]);
        
        // Set default values
        $is_subscription_active = $request->has('is_subscription_active');
        $deactivation_reason = $is_subscription_active ? null : $request->deactivation_reason;
        
        // If changing to free plan, set is_active based on subscription active status
        $updateData = [
            'subscription_plan' => $validated['subscription_plan'],
            'subscription_ends_at' => $validated['subscription_ends_at'],
            'is_subscription_active' => $is_subscription_active,
            'deactivation_reason' => $deactivation_reason,
        ];
        
        // Log the subscription update
        Log::info('Updating clinic subscription', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'old_plan' => $clinic->subscription_plan,
            'new_plan' => $validated['subscription_plan'],
            'is_active' => $clinic->is_active,
            'is_subscription_active' => $is_subscription_active,
        ]);
        
        // Update the clinic subscription
        $clinic->update($updateData);
        
        // Send notification to clinic
        try {
            $clinic->notify(new ClinicSubscriptionNotification($clinic));
            Log::info('Subscription notification sent', [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription notification: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        }
        
        return redirect()->route('admin.clinics.show', $clinic)
            ->with('success', "The clinic's subscription has been updated successfully.");
    }
    
    /**
     * Toggle clinic activation status.
     */
    public function toggleActivation(Request $request, Clinic $clinic, TenantDatabaseService $tenantDatabaseService): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
            'deactivation_reason' => 'nullable|string|required_if:is_active,0',
        ]);
        
        $is_active = $request->has('is_active');
        $deactivation_reason = $is_active ? null : $request->deactivation_reason;
        
        // Log the activation toggle
        Log::info('Toggling clinic activation status', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'old_status' => $clinic->is_active ? 'active' : 'inactive',
            'new_status' => $is_active ? 'active' : 'inactive',
        ]);
        
        // Verify the database exists if activating
        if ($is_active && !$tenantDatabaseService->databaseExists($clinic->database_name)) {
            return redirect()->back()
                ->with('error', 'Cannot activate clinic. Database does not exist.');
        }
        
        // Update the clinic activation status
        $clinic->update([
            'is_active' => $is_active,
            'deactivation_reason' => $deactivation_reason,
        ]);
        
        // Send notification to clinic
        try {
            $clinic->notify(new ClinicSubscriptionNotification($clinic, true));
            Log::info('Activation status notification sent', [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send activation notification: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        }
        
        return redirect()->route('admin.clinics.show', $clinic)
            ->with('success', $is_active 
                ? "The clinic has been activated successfully." 
                : "The clinic has been deactivated successfully.");
    }
    
    /**
     * Toggle subscription status directly without requiring a reason.
     */
    public function toggle(Clinic $clinic): RedirectResponse
    {
        // Toggle subscription active status
        $newStatus = !$clinic->is_subscription_active;
        
        // Log the subscription toggle
        Log::info('Toggling clinic subscription status', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'old_status' => $clinic->is_subscription_active ? 'active' : 'inactive',
            'new_status' => $newStatus ? 'active' : 'inactive',
        ]);
        
        // Update the clinic subscription
        $clinic->update([
            'is_subscription_active' => $newStatus,
            'deactivation_reason' => $newStatus ? null : 'Subscription deactivated by administrator',
        ]);
        
        // Send notification to clinic
        try {
            $clinic->notify(new ClinicSubscriptionNotification($clinic));
            Log::info('Subscription notification sent', [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription notification: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        }
        
        return back()->with('success', $newStatus 
            ? "The clinic's subscription has been activated successfully." 
            : "The clinic's subscription has been deactivated successfully.");
    }
    
    /**
     * Change a clinic's subscription plan directly.
     */
    public function changeSubscription(Request $request, Clinic $clinic): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:free,basic,standard,business,premium',
            'duration_months' => 'required|integer|min:1|max:24',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $oldPlan = $clinic->subscription_plan;
        $newPlan = $validated['subscription_plan'];
        $durationMonths = (int) $validated['duration_months']; // Cast to int explicitly
        
        // Calculate new expiry date
        $currentDate = now();
        $newExpiryDate = $clinic->subscription_ends_at && $clinic->subscription_ends_at->isFuture()
            ? $clinic->subscription_ends_at->copy()->addMonths($durationMonths)
            : $currentDate->copy()->addMonths($durationMonths);
        
        // Mark existing active subscription requests as replaced
        \App\Models\SubscriptionRequest::where('clinic_id', $clinic->id)
            ->where('status', 'approved')
            ->update([
                'status' => 'rejected',
                'admin_notes' => DB::raw("CONCAT(IFNULL(admin_notes, ''), ' Replaced by admin on " . now()->format('Y-m-d H:i:s') . "')"),
            ]);
        
        // Prepare update data
        $updateData = [
            'subscription_plan' => $newPlan,
            'subscription_ends_at' => $newExpiryDate,
            'is_subscription_active' => true,
            'deactivation_reason' => null,
        ];
        
        // Log the subscription plan change
        Log::info('Admin changing clinic subscription plan', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'old_plan' => $oldPlan,
            'new_plan' => $newPlan,
            'old_expiry' => $clinic->subscription_ends_at ? $clinic->subscription_ends_at->format('Y-m-d') : 'None',
            'new_expiry' => $newExpiryDate->format('Y-m-d'),
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
        ]);
        
        // Update the clinic subscription
        $clinic->update($updateData);
        
        // Create a subscription request record to track this change
        $subscriptionRequest = \App\Models\SubscriptionRequest::create([
            'clinic_id' => $clinic->id,
            'user_id' => auth()->id(),
            'plan' => $newPlan,
            'duration' => $durationMonths,
            'payment_method' => 'admin',
            'amount_paid' => 0, // Admin changes don't require payment
            'admin_notes' => $validated['admin_notes'] ?? "Plan changed by administrator.",
            'status' => 'approved',
            'approved_at' => now(),
            'expired_at' => $newExpiryDate,
            'processed_by' => auth()->id(),
        ]);
        
        // Send notification to clinic
        try {
            $clinic->notify(new ClinicSubscriptionNotification($clinic));
            Log::info('Subscription change notification sent', [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription change notification: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'email' => $clinic->email,
            ]);
        }
        
        return redirect()->route('admin.clinics.show', $clinic)
            ->with('success', "The clinic's subscription has been changed from {$oldPlan} to {$newPlan} and extended by {$durationMonths} months.");
    }
    
    /**
     * Show the form for changing a clinic's subscription.
     */
    public function showChangeForm(Clinic $clinic): View
    {
        return view('admin.clinics.change-subscription', [
            'clinic' => $clinic,
            'isSidebar' => true,
        ]);
    }
    
    /**
     * Remove a subscription record.
     */
    public function removeSubscription(Request $request, $id): RedirectResponse
    {
        // Find the subscription request
        $subscription = \App\Models\SubscriptionRequest::findOrFail($id);
        $clinic = Clinic::findOrFail($subscription->clinic_id);
        
        // Log the removal action
        Log::info('Admin removing subscription', [
            'subscription_id' => $id,
            'clinic_id' => $subscription->clinic_id,
            'clinic_name' => $clinic->name,
            'plan' => $subscription->plan,
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
        ]);
        
        // Mark the subscription as removed
        $subscription->update([
            'status' => 'cancelled',
            'admin_notes' => DB::raw("CONCAT(IFNULL(admin_notes, ''), ' Removed by admin on " . now()->format('Y-m-d H:i:s') . "')"),
        ]);
        
        // Check if this is the current active subscription for the clinic
        $isCurrentSubscription = $clinic->subscription_plan === $subscription->plan && 
            $clinic->subscription_ends_at && 
            $clinic->subscription_ends_at->format('Y-m-d') === $subscription->expired_at->format('Y-m-d');
        
        // If this is the current subscription, find another active one or revert to free plan
        if ($isCurrentSubscription) {
            // Find the most recent active subscription
            $activeSubscription = \App\Models\SubscriptionRequest::where('clinic_id', $clinic->id)
                ->where('status', 'approved')
                ->where('id', '!=', $id)
                ->orderBy('expired_at', 'desc')
                ->first();
                
            if ($activeSubscription) {
                // Set the clinic to use this subscription
                $clinic->update([
                    'subscription_plan' => $activeSubscription->plan,
                    'subscription_ends_at' => $activeSubscription->expired_at,
                    'is_subscription_active' => true,
                ]);
                
                $message = "Subscription removed. Clinic reverted to {$activeSubscription->plan} plan.";
            } else {
                // No active subscriptions left, revert to free plan
                $clinic->update([
                    'subscription_plan' => 'free',
                    'subscription_ends_at' => now()->addMonths(1),
                    'is_subscription_active' => true,
                ]);
                
                $message = "Subscription removed. Clinic reverted to free plan.";
            }
            
            // Send notification to clinic about the change
            try {
                $clinic->notify(new ClinicSubscriptionNotification($clinic));
            } catch (\Exception $e) {
                Log::error('Failed to send subscription removal notification: ' . $e->getMessage());
            }
        } else {
            $message = "Subscription record removed successfully.";
        }
        
        return back()->with('success', $message);
    }
} 