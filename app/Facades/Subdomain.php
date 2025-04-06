<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string url(mixed $clinicOrSubdomain, string $path = null, array $parameters = [], bool $secure = null)
 * @method static string route(mixed $clinicOrSubdomain, string $routeName, array $parameters = [], bool $secure = null)
 * @method static string|null current()
 * @method static bool isSubdomain(mixed $clinicOrSubdomain)
 * @method static \App\Models\Clinic|null getCurrentClinic()
 * 
 * @see \App\Services\SubdomainService
 */
class Subdomain extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'subdomain';
    }
} 