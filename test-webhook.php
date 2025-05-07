<?php
// Load Laravel environment
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a fake GitHub release webhook request
$payload = [
    'action' => 'published',
    'release' => [
        'tag_name' => 'v1.0.2',
        'name' => 'Version 1.0.2',
        'body' => "## Summary\nTest release for webhook integration\n\n## Changes\nTesting webhook integration\n\n## New Features\n- Added webhook integration\n\n## Bug Fixes\n- Fixed webhook issues\n\n#mandatory"
    ]
];

// Create request
$request = Illuminate\Http\Request::create(
    '/api/github-webhook',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json'],
    json_encode($payload)
);

// Add GitHub webhook headers
$request->headers->set('X-GitHub-Event', 'release');

// Use hardcoded webhook secret for testing
$webhookSecret = 'your_github_webhook_secret'; // This should match what you added to .env
$signature = 'sha256=' . hash_hmac('sha256', json_encode($payload), $webhookSecret);
$request->headers->set('X-Hub-Signature-256', $signature);

// Bootstrap Laravel application
$app->instance('request', $request);
$app->boot();

// Process request
$response = $kernel->handle($request);

// Output results
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

// Terminate application
$kernel->terminate($request, $response); 