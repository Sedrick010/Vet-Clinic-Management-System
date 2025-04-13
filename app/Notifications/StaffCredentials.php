<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    protected $credentials;
    protected $clinicName;

    public function __construct($credentials, $clinicName)
    {
        $this->credentials = $credentials;
        $this->clinicName = $clinicName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ' . $this->clinicName . ' - Your Staff Account Credentials')
            ->greeting('Hello ' . $this->credentials['name'] . '!')
            ->line('Welcome to ' . $this->clinicName . '! Your staff account has been created.')
            ->line('Here are your login credentials:')
            ->line('Email: ' . $this->credentials['email'])
            ->line('Password: ' . $this->credentials['password'])
            ->line('Please use these credentials to log in to the system.')
            ->action('Login Now', $this->credentials['login_url'])
            ->line('For security reasons, please change your password after your first login.')
            ->line('If you have any questions, please contact your clinic administrator.');
    }
} 