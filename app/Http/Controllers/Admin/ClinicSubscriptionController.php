<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Notifications\ClinicSubscriptionNotification;
use App\Services\TenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ClinicSubscriptionController extends Controller
{
    /**
     * Display the subscription management form.
     */
    public function edit(Clinic $clinic): View
    {
        // Define available subscription plans
        $subscriptionPlans = [
            'free' => 'Free Plan',
            'basic' => 'Basic Plan',
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
            'subscription_plan' => 'required|in:free,basic,premium',
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
} 