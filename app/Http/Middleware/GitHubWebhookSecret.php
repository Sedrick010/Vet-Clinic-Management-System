<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class GitHubWebhookSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            Log::warning('GitHub webhook request missing signature');
            return response()->json(['error' => 'No signature provided'], 401);
        }
        
        $payload = $request->getContent();
        $secret = config('services.github.webhook_secret');
        
        if (empty($secret)) {
            Log::error('GitHub webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }
        
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid GitHub webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        return $next($request);
    }
} 