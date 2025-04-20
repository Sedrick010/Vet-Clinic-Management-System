<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionApproval;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    public function plans()
    {
        return view('subscription.plans', [
            'isSidebar' => true
        ]);
    }

    public function subscribe(Request $request)
    {
        try {
            // Get the clinic
            $clinic = Clinic::find(session('current_clinic_id'));
            
            if (!$clinic) {
                throw new \Exception('Clinic not found in session. Please try logging in again.');
            }

            // Validate tenant user
            $tenantUser = session('tenant_user');
            if (!$tenantUser || !isset($tenantUser->id)) {
                throw new \Exception('User session invalid. Please try logging in again.');
            }

            // Switch to tenant database to check current subscription
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            // Get current active subscription
            $currentSubscription = Subscription::where('status', 'active')
                ->where('approval_status', 'approved')
                ->where('end_date', '>', now())
                ->latest()
                ->first();

            // If trying to subscribe to the same plan that's currently active
            if ($currentSubscription && $currentSubscription->plan_name === $request->plan_name) {
                return response()->json([
                    'type' => 'notice',
                    'message' => 'You are already subscribed to this plan. Please choose a different plan to switch.'
                ], 400);
            }

            // Prepare subscription data
            $subscriptionData = [
                'user_id' => $tenantUser->id,
                'clinic_id' => $clinic->id,
                'plan_name' => $request->plan_name,
                'amount' => $request->amount,
                'status' => 'pending',
                'approval_status' => 'pending',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'payment_method' => 'card',
                'card_last_four' => substr($request->card_number, -4)
            ];

            // Create subscription in both databases
            $subscription = Subscription::createSubscription(
                $subscriptionData,
                $clinic->database_name
            );

            // Switch back to main database
            $this->tenantDatabaseService->switchToMain();

            // Update clinic status
            $clinic->update([
                'subscription_status' => 'pending'
            ]);

            // Prepare success message based on plan change
            $message = 'Subscription request submitted successfully! Please wait for admin approval.';
            if ($currentSubscription) {
                $message = sprintf(
                    'Plan change request from %s to %s submitted successfully! Please wait for admin approval.',
                    $currentSubscription->plan_name,
                    $request->plan_name
                );
            }

            return response()->json([
                'type' => 'notice',
                'message' => $message,
                'subscription' => [
                    'id' => $subscription->id,
                    'plan_name' => $subscription->plan_name,
                    'status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            // Switch back to main database in case of error
            $this->tenantDatabaseService->switchToMain();

            \Log::error('Error creating subscription', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'type' => 'notice',
                'message' => 'Failed to create subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approvals()
    {
        $subscriptions = collect();

        try {
            // Get all clinics
            $clinics = Clinic::all();
            
            foreach ($clinics as $clinic) {
                try {
                    // Switch to tenant database
                    $this->tenantDatabaseService->switchToTenant($clinic);
                    
                    // Get all subscriptions for this clinic with user relationship
                    $clinicSubscriptions = Subscription::with('user')->get();
                    
                    foreach ($clinicSubscriptions as $subscription) {
                        $subscription->clinic = $clinic;
                        $subscriptions->push($subscription);
                    }

                    // Switch back to main database after each clinic
                    $this->tenantDatabaseService->switchToMain();
                } catch (\Exception $e) {
                    \Log::error('Error fetching subscriptions for clinic', [
                        'clinic_id' => $clinic->id,
                        'clinic_name' => $clinic->name,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Switch back to main database in case of error
                    $this->tenantDatabaseService->switchToMain();
                    continue;
                }
            }

            // Sort subscriptions by created_at date, most recent first
            $subscriptions = $subscriptions->sortByDesc('created_at');

            return view('admin.subscription-approvals', [
                'subscriptions' => $subscriptions,
                'isSidebar' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in subscription approvals:', [
                'error' => $e->getMessage()
            ]);
            
            return view('admin.subscription-approvals', [
                'subscriptions' => collect(),
                'isSidebar' => true,
                'error' => 'Failed to load subscription approvals. Please try again.'
            ]);
        }
    }

    public function approve($id)
    {
        try {
            // Find the subscription's clinic
            $clinic = Clinic::whereHas('subscriptions', function($query) use ($id) {
                $query->where('id', $id);
            })->firstOrFail();

            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            try {
                // Get the subscription
                $subscription = Subscription::findOrFail($id);
                
                // Update subscription status using simple strings
                $subscription->update([
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth()
                ]);
                
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();
                
                // Update clinic subscription status
                $clinic->update([
                    'subscription_status' => 'active'
                ]);

                // Update subscription dates separately
                try {
                    $clinic->update([
                        'current_subscription_id' => $id,
                        'subscription_start_date' => now(),
                        'subscription_end_date' => now()->addMonth()
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Could not update subscription dates in clinic record', [
                        'clinic_id' => $clinic->id,
                        'error' => $e->getMessage()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription approved successfully'
                ]);
            } catch (\Exception $e) {
                $this->tenantDatabaseService->switchToMain();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error approving subscription', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve subscription. ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            // Find the subscription's clinic
            $clinic = Clinic::whereHas('subscriptions', function($query) use ($id) {
                $query->where('id', $id);
            })->firstOrFail();

            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            try {
                // Get the subscription
                $subscription = Subscription::findOrFail($id);
                
                // Update subscription status using simple strings
                $subscription->update([
                    'approval_status' => 'rejected',
                    'rejected_at' => now(),
                    'status' => 'pending'  // Changed from 'inactive' to 'pending'
                ]);
                
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();
                
                // Update clinic subscription status
                $clinic->update([
                    'subscription_status' => 'pending'  // Changed from 'inactive' to 'pending'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription rejected successfully'
                ]);
            } catch (\Exception $e) {
                $this->tenantDatabaseService->switchToMain();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error rejecting subscription', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject subscription. ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            // Find the subscription's clinic
            $clinic = Clinic::whereHas('subscriptions', function($query) use ($id) {
                $query->where('id', $id);
            })->firstOrFail();

            // Switch to tenant database
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            try {
                // Get the subscription
                $subscription = Subscription::findOrFail($id);
                
                // Delete the subscription
                $subscription->delete();
                
                // Switch back to main database
                $this->tenantDatabaseService->switchToMain();

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription deleted successfully'
                ]);
            } catch (\Exception $e) {
                $this->tenantDatabaseService->switchToMain();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting subscription', [
                'subscription_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscription. ' . $e->getMessage()
            ], 500);
        }
    }
} 