<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\ResolveTenant;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \App\Http\Middleware\SubdomainSession::class, // Set subdomain-specific session cookie name
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ValidateTenantSubdomain::class, // Validate tenant subdomain first
            \App\Http\Middleware\RedirectIfAuthenticatedForWrongDomain::class, // Enforce domain-specific authentication
            ResolveTenant::class, // Then resolve tenant database if subdomain is valid
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            // Combined cache prevention and session validation
            \App\Http\Middleware\CheckSessionValid::class,
            \App\Http\Middleware\RealTimeSubscriptionCheck::class, // Check subscription status in real-time
            \App\Http\Middleware\NoCacheMiddleware::class, // Prevent caching of assets
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'tenant' => \App\Http\Middleware\ResolveTenant::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'subscription' => \App\Http\Middleware\CheckSubscriptionAccess::class,
        'tenant.auth' => \App\Http\Middleware\TenantAuthentication::class,
        'tenant.validate' => \App\Http\Middleware\ValidateTenantSubdomain::class, // Validate subdomain against registered clinics
        'check.session' => \App\Http\Middleware\CheckSessionValid::class, // Verify session validity
        'customer.auth' => \App\Http\Middleware\CustomerAuth::class,
        'auth.tenant.staff' => \App\Http\Middleware\AuthTenantStaff::class, // Auth for both Laravel users and tenant users
        'clinic.active' => \App\Http\Middleware\CheckClinicActive::class,
        'clinic.enabled' => \App\Http\Middleware\CheckClinicEnabled::class, // Check if clinic is enabled
        'domain.auth' => \App\Http\Middleware\RedirectIfAuthenticatedForWrongDomain::class, // Enforce domain-specific authentication
        'real.time.subscription' => \App\Http\Middleware\RealTimeSubscriptionCheck::class, // Real-time subscription status checker
        'no.cache' => \App\Http\Middleware\NoCacheMiddleware::class, // Prevent caching of assets
        'pdf.access' => \App\Http\Middleware\CheckPdfAccess::class, // Check if user has access to PDF features
        'github.webhook.secret' => \App\Http\Middleware\GitHubWebhookSecret::class, // Verify GitHub webhook signatures
    ];
} 