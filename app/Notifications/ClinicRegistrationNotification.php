<?php

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ClinicRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $clinic;
    protected $ownerEmail;
    protected $ownerName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Clinic $clinic, string $ownerEmail, string $ownerName)
    {
        $this->clinic = $clinic;
        $this->ownerEmail = $ownerEmail;
        $this->ownerName = $ownerName;
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
        $message = new MailMessage;
        
        // Copy the notification to owner's email if it's different from clinic email
        if ($this->ownerEmail && $this->ownerEmail !== $this->clinic->email) {
            $message->cc($this->ownerEmail, $this->ownerName);
        }
        
        return $message
            ->subject('Veterinary Clinic Registration Received')
            ->greeting('Hello ' . $this->ownerName . '!')
            ->line('Thank you for registering your veterinary clinic with our system.')
            ->line('Your clinic "' . $this->clinic->name . '" has been successfully registered and is pending approval.')
            ->line('Our administrative team will review your application and you will receive another email once it is approved.')
            ->action('Check Registration Status', URL::route('clinics.pending'))
            ->line('If you have any questions or need assistance, please contact our support team.');
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
            'owner_email' => $this->ownerEmail,
            'owner_name' => $this->ownerName,
        ];
    }
} 