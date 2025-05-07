<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemUpdatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define sample updates
        $updates = [
            [
                'version' => '1.1.0',
                'name' => 'Staff Management Update',
                'description' => 'This update adds the ability to manage staff members in your clinic.',
                'changes' => "Added new staff management feature\nImproved user interface for staff listings\nAdded staff role permissions",
                'features' => "Staff management dashboard\nRole-based access control\nStaff activity logs",
                'bug_fixes' => null,
                'is_critical' => false,
                'is_security' => false,
                'is_mandatory' => false,
                'available_from' => Carbon::now()->subDays(30),
                'expires_at' => null,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30)
            ],
            [
                'version' => '1.1.1',
                'name' => 'Security Patch',
                'description' => 'This update addresses important security vulnerabilities in the system.',
                'changes' => "Fixed security vulnerabilities in authentication system\nUpdated security libraries\nImproved password validation",
                'features' => null,
                'bug_fixes' => "Fixed authentication bypass vulnerability\nFixed SQL injection vulnerability\nFixed cross-site scripting vulnerability",
                'is_critical' => false,
                'is_security' => true,
                'is_mandatory' => true,
                'available_from' => Carbon::now()->subDays(15),
                'expires_at' => null,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15)
            ],
            [
                'version' => '1.2.0',
                'name' => 'Inventory Management System',
                'description' => 'Comprehensive inventory management system for tracking medical supplies and medications.',
                'changes' => "Added inventory management system\nAdded medication tracking\nAdded stock level alerts\nAdded inventory reports",
                'features' => "Track medication inventory\nSet minimum stock levels\nAutomated low stock alerts\nInventory usage reports\nExpiration date tracking",
                'bug_fixes' => null,
                'is_critical' => false,
                'is_security' => false,
                'is_mandatory' => false,
                'available_from' => Carbon::now()->subDays(7),
                'expires_at' => null,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7)
            ],
            [
                'version' => '1.2.1',
                'name' => 'Critical Performance Fix',
                'description' => 'This update resolves critical performance issues affecting large clinics.',
                'changes' => "Optimized database queries\nImproved page loading performance\nFixed memory leaks\nReduced server load",
                'features' => null,
                'bug_fixes' => "Fixed slow appointment loading for clinics with >1000 records\nFixed memory leak in report generation\nFixed database connection pooling issues",
                'is_critical' => true,
                'is_security' => false,
                'is_mandatory' => true,
                'available_from' => Carbon::now()->subDays(3),
                'expires_at' => null,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'version' => '1.3.0',
                'name' => 'System Updates Framework',
                'description' => 'Introducing the new system updates framework that allows clinics to choose when to apply updates.',
                'changes' => "Added system updates framework\nAdded update notifications\nAdded update management interface\nAllows clinics to choose when to apply updates",
                'features' => "Update notifications on dashboard\nDetailed update information\nSelective update application\nUpdate history tracking",
                'bug_fixes' => null,
                'is_critical' => false,
                'is_security' => false,
                'is_mandatory' => false,
                'available_from' => Carbon::now(),
                'expires_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Insert the updates
        foreach ($updates as $update) {
            DB::table('system_updates')->insertOrIgnore($update);
        }

        // Update the current version
        DB::table('system_versions')
            ->where('is_current', true)
            ->update([
                'is_current' => false
            ]);

        DB::table('system_versions')->insertOrIgnore([
            'version' => '1.3.0',
            'name' => 'System Updates Framework',
            'description' => 'Introducing the new system updates framework',
            'is_current' => true,
            'released_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
} 