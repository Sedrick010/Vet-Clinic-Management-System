<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Test email functionality';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('This is a test email from the Vet Clinic System', function($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from Vet Clinic System');
            });
            
            $this->info('Test email sent successfully to ' . $email);
        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
        }
    }
} 