<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Clinic extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'subdomain',
        'address',
        'phone',
        'email',
        'logo',
        'logo_path',
        'logo_disk',
        'description',
        'database_name',
        'is_active',
        'approval_status',
        'rejection_reason',
        'owner_email',
        'owner_name',
        'temp_password',
        'subscription_plan',
        'subscription_ends_at',
        'is_subscription_active',
        'deactivation_reason',
        'is_enabled',
        'disable_reason',
        'theme',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_subscription_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    /**
     * Get the users for the clinic.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the clinic has an active premium subscription.
     */
    public function hasPremiumAccess(): bool
    {
        return $this->is_active && 
               $this->is_subscription_active && 
               $this->subscription_plan === 'premium' &&
               ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }
    
    /**
     * Check if the clinic's subscription is expired.
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_ends_at !== null && $this->subscription_ends_at->isPast();
    }

    /**
     * Get the subscription requests for the clinic.
     */
    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class);
    }
    
    /**
     * Get the clinic's pending subscription request, if any.
     */
    public function pendingSubscriptionRequest()
    {
        return $this->subscriptionRequests()->where('status', 'pending')->latest()->first();
    }
    
    /**
     * Check if the clinic has a pending subscription request.
     */
    public function hasPendingSubscriptionRequest(): bool
    {
        return $this->subscriptionRequests()->where('status', 'pending')->exists();
    }
    
    /**
     * Get the logo URL if it exists, or a default logo.
     */
    public function getLogoUrl(): string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        
        // Legacy support for old logo field
        if ($this->logo) {
            return $this->logo;
        }
        
        // Default logo
        return asset('images/logos/default-clinic-logo.png');
    }
    
    /**
     * Update the clinic logo
     */
    public function updateLogo($image)
    {
        if ($this->logo_path) {
            // Delete the old logo if it exists
            \Storage::disk($this->logo_disk)->delete($this->logo_path);
        }
        
        // Store the new logo
        $path = $image->store('clinic-logos', 'public');
        
        $this->update([
            'logo_path' => $path,
            'logo_disk' => 'public',
        ]);
        
        return $path;
    }
    
    /**
     * Get the theme configuration for the clinic
     */
    public function getThemeConfig(): array
    {
        $defaultTheme = [
            'name' => 'default',
            'colors' => [
                'primary' => '#5e72e4',
                'secondary' => '#8392ab',
                'success' => '#2dce89',
                'info' => '#11cdef',
                'warning' => '#fb6340',
                'danger' => '#f5365c',
                'background' => '#f8f9fe',
                'card' => '#ffffff',
                'text' => '#344767',
                'textSecondary' => '#67748e'
            ]
        ];
        
        $darkTheme = [
            'name' => 'dark',
            'colors' => [
                'primary' => '#6f42c1',
                'secondary' => '#4c566a',
                'success' => '#40b983',
                'info' => '#3498db',
                'warning' => '#f39c12',
                'danger' => '#e74c3c',
                'background' => '#1e1e2d',
                'card' => '#2a2a3c',
                'cardSecondary' => '#323248',
                'cardAccent' => '#252536',
                'text' => '#e6e6e6',
                'textSecondary' => '#b5b5c3'
            ]
        ];
        
        if ($this->theme === 'dark') {
            return $darkTheme;
        }
        
        return $defaultTheme;
    }
} 