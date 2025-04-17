<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\MakeTenantMigration;
use App\Console\Commands\RunTenantMigrations;
use App\Console\Commands\FixTenantDatabases;

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
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Run database backup daily at midnight
        $schedule->command('app:backup-databases')->dailyAt('00:00');
        
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