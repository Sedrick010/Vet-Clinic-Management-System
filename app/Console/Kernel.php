<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\MakeTenantMigration;
use App\Console\Commands\RunTenantMigrations;
use App\Console\Commands\FixTenantDatabases;
use App\Console\Commands\SeedClinicTemporaryData;
use App\Console\Commands\RunSpecificTenantMigration;
use App\Console\Commands\CheckTenantTableStructure;
use App\Console\Commands\FixPetsTableTenant;
use App\Console\Commands\ExecuteSqlForTenant;
use App\Console\Commands\FixAppointmentsTable;
use App\Console\Commands\RunFixTablesForAllClinics;
use App\Console\Commands\SyncApplicationVersion;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MakeTenantMigration::class,
        RunTenantMigrations::class,
        FixTenantDatabases::class,
        SeedClinicTemporaryData::class,
        RunSpecificTenantMigration::class,
        CheckTenantTableStructure::class,
        FixPetsTableTenant::class,
        ExecuteSqlForTenant::class,
        FixAppointmentsTable::class,
        RunFixTablesForAllClinics::class,
        \App\Console\Commands\RepairSubscriptionStatus::class,
        SyncApplicationVersion::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Run database backup daily at midnight
        $schedule->command('app:backup-databases')->dailyAt('00:00');
        
        // Run fix:tables for all clinics daily at 1:00 AM
        $schedule->command('app:run-fix-tables-for-all-clinics')->dailyAt('01:00');
        
        // Check for latest version daily at 2:00 AM and update if needed
        $schedule->command('app:sync-version')->dailyAt('02:00');
        
        // You can also schedule backups with compression (these are commented out by default)
        // $schedule->command('app:backup-databases --compress')->weeklyOn(1, '01:00'); // Every Monday at 1 AM
        // $schedule->command('app:backup-databases --destination=s3 --compress')->monthlyOn(1, '02:00'); // 1st day of month at 2 AM
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 