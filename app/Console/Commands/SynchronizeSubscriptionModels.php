<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SynchronizeSubscriptionModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-subscription-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize data between Subscription and SubscriptionRequest models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting synchronization between subscription models...');

        // Check if both tables exist
        if (!Schema::hasTable('subscriptions') || !Schema::hasTable('subscription_requests')) {
            $this->error('One or both of the required tables do not exist.');
            return 1;
        }

        // Get all existing subscriptions
        $subscriptions = Subscription::all();
        $this->info("Found {$subscriptions->count()} subscriptions to process.");

        // Create subscription requests for each subscription
        $count = 0;
        foreach ($subscriptions as $subscription) {
            $this->comment("Processing subscription #{$subscription->id}...");

            // Check if a corresponding request already exists
            $existingRequest = SubscriptionRequest::where('user_id', $subscription->user_id)
                ->where('plan', $subscription->plan)
                ->where('created_at', $subscription->created_at)
                ->first();

            if ($existingRequest) {
                $this->line("Subscription request already exists for subscription #{$subscription->id}. Skipping.");
                continue;
            }

            // Find clinic based on user's clinic
            $clinicId = null;
            if ($subscription->user_id) {
                $user = User::find($subscription->user_id);
                if ($user && $user->clinic_id) {
                    $clinicId = $user->clinic_id;
                    $this->line("Found clinic ID {$clinicId} from user #{$subscription->user_id}");
                } else {
                    $this->warn("User #{$subscription->user_id} not found or has no clinic associated");
                }
            } 
            
            // If no clinic found from user, try to find by guest information
            if (!$clinicId && ($subscription->guest_clinic_name || $subscription->guest_email)) {
                $clinic = null;
                
                if ($subscription->guest_clinic_name) {
                    $clinic = Clinic::where('name', $subscription->guest_clinic_name)->first();
                }
                
                if (!$clinic && $subscription->guest_email) {
                    $clinic = Clinic::where('email', $subscription->guest_email)->first();
                }
                
                if ($clinic) {
                    $clinicId = $clinic->id;
                    $this->line("Found clinic ID {$clinicId} from guest information");
                } else {
                    $this->warn("No clinic found for guest clinic name '{$subscription->guest_clinic_name}' or guest email '{$subscription->guest_email}'");
                }
            }
            
            // If still no clinic, use the default clinic or create one
            if (!$clinicId) {
                // Attempt to get the first clinic as a fallback
                $defaultClinic = Clinic::first();
                if ($defaultClinic) {
                    $clinicId = $defaultClinic->id;
                    $this->warn("Using default clinic ID {$clinicId} as fallback");
                } else {
                    // If no clinics exist, we'll create a placeholder clinic
                    try {
                        $newClinic = Clinic::create([
                            'name' => $subscription->guest_clinic_name ?? 'Unknown Clinic',
                            'email' => $subscription->guest_email ?? 'unknown@example.com',
                            'phone' => $subscription->guest_phone ?? 'Unknown',
                        ]);
                        $clinicId = $newClinic->id;
                        $this->info("Created new placeholder clinic with ID {$clinicId}");
                    } catch (\Exception $e) {
                        $this->error("Failed to create placeholder clinic: {$e->getMessage()}");
                        continue; // Skip this subscription as we can't proceed without a clinic
                    }
                }
            }

            try {
                DB::beginTransaction();
                
                // Create new subscription request
                $subscriptionRequest = new SubscriptionRequest([
                    'clinic_id' => $clinicId,
                    'user_id' => $subscription->user_id,
                    'plan' => $subscription->plan,
                    'duration' => $subscription->duration,
                    'payment_method' => $subscription->payment_method,
                    'payment_reference' => $subscription->payment_reference,
                    'payment_details' => $subscription->payment_details,
                    'amount_paid' => $subscription->amount_paid,
                    'notes' => $subscription->notes,
                    'admin_notes' => $subscription->admin_notes ?? null,
                    'status' => $subscription->status,
                    'auto_renew' => $subscription->auto_renew,
                    'guest_clinic_name' => $subscription->guest_clinic_name,
                    'guest_email' => $subscription->guest_email,
                    'guest_phone' => $subscription->guest_phone,
                    'approved_at' => $subscription->approved_at,
                    'rejected_at' => $subscription->rejected_at,
                    'cancelled_at' => $subscription->cancelled_at,
                    'expired_at' => $subscription->expired_at,
                    'rejection_reason' => $subscription->rejection_reason,
                ]);

                // Set timestamps
                $subscriptionRequest->created_at = $subscription->created_at;
                $subscriptionRequest->updated_at = $subscription->updated_at;
                $subscriptionRequest->save();

                DB::commit();
                
                $count++;
                $this->info("Successfully created subscription request for subscription #{$subscription->id}");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error processing subscription #{$subscription->id}: {$e->getMessage()}");
                Log::error("Failed to sync subscription #{$subscription->id}: {$e->getMessage()}", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Synchronization complete. Created {$count} new subscription requests.");
        return 0;
    }
}
