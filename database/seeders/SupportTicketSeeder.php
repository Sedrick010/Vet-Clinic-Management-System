<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing users and clinics for reference
        $users = User::whereNotNull('clinic_id')->get();
        $clinics = Clinic::all();
        $adminUser = User::where('role', 'admin')->first();
        
        if ($users->isEmpty() || $clinics->isEmpty() || !$adminUser) {
            $this->command->info('Necessary data is missing. Please run the base seeders first.');
            return;
        }
        
        // Create some sample tickets
        $tickets = [
            [
                'subject' => 'Cannot create new appointments',
                'message' => 'When I try to create a new appointment in the calendar, I get an error message saying "validation failed". What could be causing this issue?',
                'status' => SupportTicket::STATUS_OPEN,
                'priority' => SupportTicket::PRIORITY_HIGH,
                'clinic_id' => $clinics->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'subject' => 'Login issues after password reset',
                'message' => 'After resetting my password, I cannot log in with the new password. The system keeps saying "invalid credentials". Please help!',
                'status' => SupportTicket::STATUS_IN_PROGRESS,
                'priority' => SupportTicket::PRIORITY_MEDIUM,
                'clinic_id' => $clinics->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'subject' => 'Payment integration not working',
                'message' => 'We are trying to use the payment integration feature but it does not seem to be working correctly. Payments are being registered but not showing up in our revenue reports.',
                'status' => SupportTicket::STATUS_RESOLVED,
                'priority' => SupportTicket::PRIORITY_CRITICAL,
                'clinic_id' => $clinics->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(1),
            ],
            [
                'subject' => 'Pet profiles not showing medical history',
                'message' => 'The medical history for our pet profiles is not displaying properly. This is a critical issue as we need to see the treatment history when treating animals.',
                'status' => SupportTicket::STATUS_OPEN,
                'priority' => SupportTicket::PRIORITY_CRITICAL,
                'clinic_id' => $clinics->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'subject' => 'Feature request: SMS reminders for appointments',
                'message' => 'Would it be possible to add SMS reminders for appointments? Our clients often forget their appointments and it would be very helpful to have automatic SMS reminders.',
                'status' => SupportTicket::STATUS_CLOSED,
                'priority' => SupportTicket::PRIORITY_LOW,
                'clinic_id' => $clinics->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(15),
            ],
        ];
        
        foreach ($tickets as $ticketData) {
            $ticket = SupportTicket::create($ticketData);
            
            // Add sample replies for tickets that have been responded to
            if (in_array($ticket->status, [SupportTicket::STATUS_IN_PROGRESS, SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED])) {
                // Admin reply
                SupportTicketReply::create([
                    'message' => 'Thank you for reporting this issue. We are looking into it and will get back to you shortly.',
                    'support_ticket_id' => $ticket->id,
                    'user_id' => $adminUser->id,
                    'is_admin' => true,
                    'created_at' => $ticket->created_at->addDays(1),
                    'updated_at' => $ticket->created_at->addDays(1),
                ]);
                
                // Client follow-up for in-progress tickets
                if ($ticket->status === SupportTicket::STATUS_IN_PROGRESS) {
                    SupportTicketReply::create([
                        'message' => 'Thanks for your response. Any update on this issue? It is really impacting our daily operations.',
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $ticket->user_id,
                        'is_admin' => false,
                        'created_at' => $ticket->created_at->addDays(2),
                        'updated_at' => $ticket->created_at->addDays(2),
                    ]);
                }
                
                // Resolution reply for resolved tickets
                if ($ticket->status === SupportTicket::STATUS_RESOLVED) {
                    SupportTicketReply::create([
                        'message' => 'We have identified and fixed the issue. Please try again and let us know if you still experience any problems.',
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $adminUser->id,
                        'is_admin' => true,
                        'created_at' => $ticket->created_at->addDays(3),
                        'updated_at' => $ticket->created_at->addDays(3),
                    ]);
                    
                    SupportTicketReply::create([
                        'message' => 'Great! It works perfectly now. Thank you for your help!',
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $ticket->user_id,
                        'is_admin' => false,
                        'created_at' => $ticket->created_at->addDays(4),
                        'updated_at' => $ticket->created_at->addDays(4),
                    ]);
                }
            }
        }
        
        $this->command->info('Support tickets seeded successfully!');
    }
} 