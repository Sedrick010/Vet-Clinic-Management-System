<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionRequest;
use App\Models\Clinic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RepairSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:repair';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair inconsistencies between subscription requests and clinic subscription statuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription status repair...');
        
        // Find all approved subscriptions
        $approvedSubscriptions = SubscriptionRequest::where('status', 'approved')
            ->whereNotNull('expired_at')
            ->where('expired_at', '>', now())
            ->get();
            
        $this->info("Found {$approvedSubscriptions->count()} active approved subscriptions");
        
        $fixed = 0;
        
        foreach ($approvedSubscriptions as $subscription) {
            // Skip if no clinic connection
            if (!$subscription->clinic_id) {
                $this->warn("Subscription #{$subscription->id} has no clinic_id");
                continue;
            }
            
            $clinic = Clinic::find($subscription->clinic_id);
            
            if (!$clinic) {
                $this->warn("Clinic #{$subscription->clinic_id} not found for subscription #{$subscription->id}");
                continue;
            }
            
            $needsUpdate = false;
            $changes = [];
            
            // Check subscription active status
            if (!$clinic->is_subscription_active) {
                $needsUpdate = true;
                $changes[] = 'is_subscription_active: false -> true';
                $clinic->is_subscription_active = true;
            }
            
            // Check subscription plan
            if ($clinic->subscription_plan != $subscription->plan) {
                $needsUpdate = true;
                $changes[] = "subscription_plan: {$clinic->subscription_plan} -> {$subscription->plan}";
                $clinic->subscription_plan = $subscription->plan;
            }
            
            // Check subscription end date
            if (!$clinic->subscription_ends_at || $clinic->subscription_ends_at->notEqualTo($subscription->expired_at)) {
                $needsUpdate = true;
                $oldDate = $clinic->subscription_ends_at ? $clinic->subscription_ends_at->format('Y-m-d') : 'null';
                $newDate = $subscription->expired_at->format('Y-m-d');
                $changes[] = "subscription_ends_at: {$oldDate} -> {$newDate}";
                $clinic->subscription_ends_at = $subscription->expired_at;
            }
            
            // Save changes if needed
            if ($needsUpdate) {
                $clinic->save();
                $fixed++;
                $this->info("Fixed clinic #{$clinic->id} ({$clinic->name}): " . implode(', ', $changes));
                
                // Log changes
                Log::info("Subscription repair fixed clinic", [
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'subscription_id' => $subscription->id,
                    'changes' => $changes
                ]);
            }
        }
        
        $this->info("Repair completed. Fixed {$fixed} clinics.");
        
        return 0;
    }
} 