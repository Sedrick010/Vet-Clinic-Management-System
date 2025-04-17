<?php

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ClinicSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $clinic;
    protected $isActivationChange;

    /**
     * Create a new notification instance.
     */
    public function __construct(Clinic $clinic, bool $isActivationChange = false)
    {
        $this->clinic = $clinic;
        $this->isActivationChange = $isActivationChange;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Get the clinic URL
        $protocol = request()->secure() ? 'https://' : 'http://';
        $domain = config('app.domain');
        $clinicUrl = $protocol . $this->clinic->subdomain . '.' . $domain;
        
        // Log notification preparation
        Log::info('Preparing clinic subscription notification email', [
            'clinic_id' => $this->clinic->id,
            'is_activation_change' => $this->isActivationChange,
            'is_active' => $this->clinic->is_active,
            'plan' => $this->clinic->subscription_plan,
            'is_subscription_active' => $this->clinic->is_subscription_active,
            'clinic_url' => $clinicUrl,
        ]);
        
        $message = (new MailMessage)
            ->subject($this->getEmailSubject())
            ->greeting('Hello ' . $this->clinic->name);
            
        if ($this->isActivationChange) {
            if ($this->clinic->is_active) {
                $message->line('Your clinic has been activated.')
                    ->line('You can now access your clinic management system.')
                    ->action('Access Your Clinic', $clinicUrl);
            } else {
                $message->line('Your clinic has been deactivated.')
                    ->line('Reason: ' . ($this->clinic->deactivation_reason ?? 'No reason provided.'))
                    ->line('Please contact our support team for assistance.');
            }
        } else {
            $planName = ucfirst($this->clinic->subscription_plan);
            
            $message->line("Your subscription has been updated to the {$planName} plan.")
                ->line($this->getPlanDescription());
                
            if ($this->clinic->subscription_ends_at) {
                $message->line("Your subscription is valid until " . $this->clinic->subscription_ends_at->format('F j, Y') . ".");
            }
                
            if ($this->clinic->is_subscription_active && $this->clinic->is_active) {
                $message->action('Access Your Clinic', $clinicUrl);
            } elseif (!$this->clinic->is_subscription_active) {
                $message->line('Your subscription is currently inactive.')
                    ->line('Reason: ' . ($this->clinic->deactivation_reason ?? 'No reason provided.'))
                    ->line('Please contact our support team for assistance.');
            }
        }
        
        return $message->line('Thank you for using our service!');
    }
    
    /**
     * Get the email subject based on the notification type.
     */
    private function getEmailSubject(): string
    {
        if ($this->isActivationChange) {
            return $this->clinic->is_active 
                ? 'Your Clinic Has Been Activated' 
                : 'Your Clinic Has Been Deactivated';
        }
        
        return 'Your Subscription Has Been Updated';
    }
    
    /**
     * Get the description text for the subscription plan.
     */
    private function getPlanDescription(): string
    {
        switch ($this->clinic->subscription_plan) {
            case 'premium':
                return 'The Premium plan includes unlimited patient records, appointment scheduling, staff management, and advanced reporting.';
            case 'basic':
                return 'The Basic plan includes up to 500 patient records, appointment scheduling, and staff management.';
            case 'free':
            default:
                return 'The Free plan includes up to 100 patient records and basic appointment scheduling.';
        }
    }
} 