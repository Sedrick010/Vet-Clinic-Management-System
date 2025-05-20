<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomUpdaterService;

class TestCustomUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-custom-updater';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the custom updater service';

    /**
     * The custom updater service.
     */
    protected $updater;

    /**
     * Create a new command instance.
     */
    public function __construct(CustomUpdaterService $updater)
    {
        parent::__construct();
        $this->updater = $updater;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Custom Updater Service...');
        
        $this->info('Current version: ' . config('self-update.version_installed'));
        
        $latestVersion = $this->updater->getLatestVersion();
        $this->info('Latest version: ' . $latestVersion);
        
        $isNewVersionAvailable = $this->updater->isNewVersionAvailable();
        $this->info('New version available: ' . ($isNewVersionAvailable ? 'Yes' : 'No'));
        
        $this->info('Latest version details:');
        $details = $this->updater->getLatestVersionDetails();
        
        if ($details) {
            $this->table(
                ['Key', 'Value'],
                collect($details)->map(function ($value, $key) {
                    return [$key, $value];
                })->toArray()
            );
        } else {
            $this->error('No release details found.');
        }
        
        return 0;
    }
} 