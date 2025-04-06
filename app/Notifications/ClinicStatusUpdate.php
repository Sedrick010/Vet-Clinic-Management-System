<?php

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class ClinicStatusUpdate extends Notification
{
    use Queueable;

    protected $clinic;
    protected $status;
    protected $rejectionReason;
    protected $ownerEmail;
    protected $ownerName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Clinic $clinic, string $status, ?string $ownerEmail = null, ?string $ownerName = null, ?string $rejectionReason = null)
    {
        $this->clinic = $clinic;
        $this->status = $status;
        $this->ownerEmail = $ownerEmail;
        $this->ownerName = $ownerName;
        $this->rejectionReason = $rejectionReason;

        // Log notification creation
        Log::info('Creating clinic status notification', [
            'clinic_id' => $clinic->id,
            'status' => $status,
            'owner_email' => $ownerEmail,
            'clinic_email' => $clinic->email
        ]);
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
        $baseUrl = config('app.url');
        $protocol = request()->secure() ? 'https://' : 'http://';
        $domain = str_replace(['http://', 'https://'], '', $baseUrl);
        $clinicUrl = $protocol . $this->clinic->subdomain . '.' . $domain;
        
        // Log email preparation
        Log::info('Preparing clinic status email', [
            'clinic_id' => $this->clinic->id,
            'status' => $this->status,
            'to_email' => $this->clinic->email,
            'cc_email' => $this->ownerEmail,
            'clinic_url' => $clinicUrl
        ]);
        
        $message = new MailMessage;
        
        // Copy the notification to owner's email if it's different from clinic email
        if ($this->ownerEmail && $this->ownerEmail !== $this->clinic->email) {
            $message->cc($this->ownerEmail, $this->ownerName);
            Log::info('Adding CC to email', [
                'cc_email' => $this->ownerEmail,
                'cc_name' => $this->ownerName
            ]);
        }
        
        if ($this->status === 'approved') {
            return $message
                ->subject('Your Veterinary Clinic Registration Has Been Approved!')
                ->greeting('Hello ' . ($this->ownerName ?? 'Clinic Owner') . '!')
                ->line('Great news! Your veterinary clinic registration has been approved.')
                ->line('Your clinic ' . $this->clinic->name . ' is now ready to use.')
                ->action('Access Your Clinic', $clinicUrl)
                ->line('You can log in using the email and password you provided during registration.')
                ->line('Your clinic subdomain: ' . $this->clinic->subdomain . '.' . $domain)
                ->line('Thank you for choosing our Veterinary Clinic Management System!');
        } elseif ($this->status === 'rejected') {
            return $message
                ->subject('Update on Your Veterinary Clinic Registration')
                ->greeting('Hello ' . ($this->ownerName ?? 'Clinic Owner') . ',')
                ->line('We have reviewed your registration for ' . $this->clinic->name . '.')
                ->line('Unfortunately, we are unable to approve your clinic registration at this time.')
                ->line('Reason: ' . ($this->rejectionReason ?? 'Your application did not meet our requirements.'))
                ->action('Review Your Application', URL::route('clinics.pending'))
                ->line('You can update your information and submit a new application.')
                ->line('If you have any questions or need assistance, please contact our support team.');
        } else {
            return $message
                ->subject('Veterinary Clinic Registration Status Update')
                ->greeting('Hello ' . ($this->ownerName ?? 'Clinic Owner') . ',')
                ->line('We are writing to inform you of an update to your clinic registration for ' . $this->clinic->name . '.')
                ->line('Your registration is currently: ' . strtoupper($this->status))
                ->action('Check Status', URL::route('clinics.pending'))
                ->line('Thank you for your patience.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'clinic_id' => $this->clinic->id,
            'clinic_name' => $this->clinic->name,
            'status' => $this->status,
            'rejection_reason' => $this->rejectionReason,
        ];
    }
}
