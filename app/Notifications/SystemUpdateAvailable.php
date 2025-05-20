<?php

namespace App\Notifications;

use App\Models\SystemUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemUpdateAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    protected $update;

    /**
     * Create a new notification instance.
     *
     * @param SystemUpdate $update
     * @return void
     */
    public function __construct(SystemUpdate $update)
    {
        $this->update = $update;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('VetClinic System Update Available')
            ->greeting('Hello!')
            ->line('A new system update is available for your VetClinic application.')
            ->line('Version: ' . $this->update->version)
            ->line('Name: ' . $this->update->name);

        if ($this->update->description) {
            $mailMessage->line('Description: ' . $this->update->description);
        }

        if ($this->update->is_critical) {
            $mailMessage->line('This is a critical update that addresses important issues.');
        }

        if ($this->update->is_security) {
            $mailMessage->line('This update contains security improvements.');
        }

        if ($this->update->is_mandatory) {
            $mailMessage->line('This is a mandatory update that must be applied.');
        }

        $mailMessage->action('View Update Details', url('/system-updates'))
            ->line('You can choose to apply this update or dismiss it from the System Updates section in your dashboard.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'update_id' => $this->update->id,
            'version' => $this->update->version,
            'name' => $this->update->name,
            'description' => $this->update->description,
            'is_critical' => $this->update->is_critical,
            'is_security' => $this->update->is_security,
            'is_mandatory' => $this->update->is_mandatory,
        ];
    }
} 