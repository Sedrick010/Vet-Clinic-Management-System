<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GitHubWebhookController;
use Illuminate\Support\Facades\Log;

class TestGitHubWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-github-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the GitHub webhook controller with a simulated release event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing GitHub webhook with a simulated release event...');

        try {
            $controller = new GitHubWebhookController();
            
            // Create a mock release data
            $release = [
                'tag_name' => 'v1.0.2',
                'name' => 'Version 1.0.2',
                'body' => "## Summary\nTest release for webhook integration\n\n## Changes\nTesting webhook integration\n\n## New Features\n- Added webhook integration\n\n## Bug Fixes\n- Fixed webhook issues\n\n#mandatory"
            ];
            
            // Call the private processRelease method using reflection
            $reflectionMethod = new \ReflectionMethod(GitHubWebhookController::class, 'processRelease');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke($controller, $release);
            
            $this->info('Test completed successfully!');
            $this->info('Check the system_updates and clinic_updates tables to verify the update was created.');
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
} 