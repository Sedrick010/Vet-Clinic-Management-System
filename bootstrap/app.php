<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases for Laravel 11
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'tenant.auth' => \App\Http\Middleware\TenantAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
