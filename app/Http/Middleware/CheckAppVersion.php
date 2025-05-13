<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $minVersion): Response
    {
        // Get current app version from self-updater config
        $currentVersion = config('self-update.version_installed') ?? '1.0.0';
        
        // Compare versions using version_compare
        if (version_compare($currentVersion, $minVersion, '<')) {
            return response()->json([
                'error' => 'This feature requires a newer version of the application.',
                'current_version' => $currentVersion,
                'required_version' => $minVersion,
                'update_available' => route('updates.check')
            ], 403);
        }

        return $next($request);
    }
} 