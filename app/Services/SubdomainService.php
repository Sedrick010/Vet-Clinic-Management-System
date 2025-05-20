<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SubdomainService
{
    /**
     * Get the full URL for a clinic subdomain
     *
     * @param Clinic|string $clinicOrSubdomain
     * @param string|null $path
     * @param array $parameters
     * @param bool $secure
     * @return string
     */
    public function url($clinicOrSubdomain, ?string $path = null, array $parameters = [], bool $secure = null): string
    {
        // Get the subdomain string
        $subdomain = $clinicOrSubdomain;
        if ($clinicOrSubdomain instanceof Clinic) {
            $subdomain = $clinicOrSubdomain->subdomain;
        }
        
        // Normalize the subdomain (lowercase, remove spaces)
        $subdomain = strtolower(trim($subdomain));
        
        // Get the base URL from config and parse its components
        $baseUrl = config('app.url');
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'http';
        $baseDomain = parse_url($baseUrl, PHP_URL_HOST) ?? 'localhost';
        
        // Use the secure parameter or detect from the current request
        $secure = $secure ?? request()->secure();
        $protocol = $secure ? 'https' : 'http';
        
        // Construct the full subdomain URL
        $url = "{$protocol}://{$subdomain}.{$baseDomain}";
        
        // Add the path if provided
        if ($path) {
            // Ensure path starts with a slash
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
            $url .= $path;
        }
        
        // Add query parameters if provided
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return $url;
    }
    
    /**
     * Generate a URL to a named route on a clinic subdomain
     *
     * @param Clinic|string $clinicOrSubdomain
     * @param string $routeName
     * @param array $parameters
     * @param bool $secure
     * @return string
     */
    public function route($clinicOrSubdomain, string $routeName, array $parameters = [], bool $secure = null): string
    {
        // Get the subdomain string
        $subdomain = $clinicOrSubdomain;
        if ($clinicOrSubdomain instanceof Clinic) {
            $subdomain = $clinicOrSubdomain->subdomain;
        }
        
        // Get the route path using Laravel's URL generator
        // We need to do this in the context of the main domain, not the subdomain
        $baseUrl = config('app.url');
        $path = parse_url(URL::route($routeName, $parameters, false), PHP_URL_PATH);
        
        // Now create the subdomain URL with this path
        return $this->url($subdomain, $path, [], $secure);
    }
    
    /**
     * Get the current subdomain from the request
     *
     * @return string|null
     */
    public function current(): ?string
    {
        $host = request()->getHost();
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        
        // No subdomain if host is the base domain
        if ($host === $baseDomain) {
            return null;
        }
        
        // Check if host ends with base domain
        if (!str_ends_with($host, $baseDomain)) {
            return null;
        }
        
        // Extract the subdomain part
        $subdomainPart = str_replace('.' . $baseDomain, '', $host);
        
        // Handle potential nested subdomains
        $parts = explode('.', $subdomainPart);
        if (count($parts) > 1) {
            // Take the first-level subdomain (directly before base domain)
            return $parts[count($parts) - 1];
        }
        
        return $subdomainPart;
    }
    
    /**
     * Check if the current request is on a specific subdomain
     *
     * @param string|Clinic $clinicOrSubdomain
     * @return bool
     */
    public function isSubdomain($clinicOrSubdomain): bool
    {
        $targetSubdomain = $clinicOrSubdomain;
        if ($clinicOrSubdomain instanceof Clinic) {
            $targetSubdomain = $clinicOrSubdomain->subdomain;
        }
        
        $currentSubdomain = $this->current();
        
        return $currentSubdomain === $targetSubdomain;
    }
    
    /**
     * Get a clinic based on the current subdomain
     *
     * @return Clinic|null
     */
    public function getCurrentClinic(): ?Clinic
    {
        $subdomain = $this->current();
        
        if (!$subdomain) {
            return null;
        }
        
        return Clinic::where('subdomain', $subdomain)->first();
    }
} 