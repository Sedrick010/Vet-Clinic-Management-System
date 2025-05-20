<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Log;

class UpdateSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-system {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the system to the latest version using Laravel Self-Updater';

    /**
     * Execute the console command.
     */
    public function handle(UpdaterManager $updater)
    {
        $this->info('Checking for updates...');

        try {
            $source = $updater->source('github');
            
            if (!$source->isNewVersionAvailable()) {
                $this->info('You are already using the latest version: ' . $source->getVersionInstalled());
                return;
            }
            
            $newVersion = $source->getVersionAvailable();
            $this->info('New version available: ' . $newVersion);
            
            if (!$this->option('force') && !$this->confirm('Do you wish to update to the latest version?')) {
                $this->info('Update cancelled.');
                return;
            }
            
            $this->info('Fetching update...');
            $release = $source->fetch();
            
            $this->info('Applying update...');
            $source->update($release);
            
            $this->info('Update successful! New version installed: ' . $newVersion);
            
        } catch (\Exception $e) {
            $this->error('Update failed: ' . $e->getMessage());
            Log::error('System update failed: ' . $e->getMessage());
        }
    }
} 