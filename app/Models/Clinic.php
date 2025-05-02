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
        'custom_theme_colors',
        'theme_customization_level',
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
        'custom_theme_colors' => 'json',
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
        
        $forestTheme = [
            'name' => 'forest',
            'colors' => [
                'primary' => '#2e7d32',
                'secondary' => '#66bb6a',
                'success' => '#43a047',
                'info' => '#26a69a',
                'warning' => '#ff9800',
                'danger' => '#e53935',
                'background' => '#f1f8e9',
                'card' => '#ffffff',
                'cardSecondary' => '#c8e6c9',
                'cardAccent' => '#e8f5e9',
                'text' => '#1b5e20',
                'textSecondary' => '#33691e'
            ]
        ];
        
        // Check if this clinic has custom theme colors
        if ($this->theme_customization_level === 'advanced' && $this->custom_theme_colors) {
            $baseTheme = $this->theme === 'dark' ? $darkTheme : $defaultTheme;
            
            // Merge custom colors with the base theme
            return [
                'name' => 'custom',
                'colors' => array_merge($baseTheme['colors'], $this->custom_theme_colors)
            ];
        }
        
        // Return standard theme based on selection
        switch ($this->theme) {
            case 'dark':
                return $darkTheme;
            case 'forest':
                return $forestTheme;
            case 'sunset':
                return [
                    'name' => 'sunset',
                    'colors' => [
                        'primary' => '#ff7043',
                        'secondary' => '#ffab91',
                        'success' => '#66bb6a',
                        'info' => '#29b6f6',
                        'warning' => '#ffa726',
                        'danger' => '#f44336',
                        'background' => '#fff3e0',
                        'card' => '#ffffff',
                        'cardSecondary' => '#ffe0b2',
                        'cardAccent' => '#fff8e1',
                        'text' => '#e64a19',
                        'textSecondary' => '#bf360c'
                    ]
                ];
            case 'vintage':
                return [
                    'name' => 'vintage',
                    'colors' => [
                        'primary' => '#a1887f',
                        'secondary' => '#bcaaa4',
                        'success' => '#558b2f',
                        'info' => '#0277bd',
                        'warning' => '#ef6c00',
                        'danger' => '#c62828',
                        'background' => '#efebe9',
                        'card' => '#ffffff',
                        'cardSecondary' => '#d7ccc8',
                        'cardAccent' => '#f5f5f5',
                        'text' => '#795548',
                        'textSecondary' => '#5d4037'
                    ]
                ];
            case 'blossom':
                return [
                    'name' => 'blossom',
                    'colors' => [
                        'primary' => '#ec407a',
                        'secondary' => '#f48fb1',
                        'success' => '#66bb6a',
                        'info' => '#26c6da',
                        'warning' => '#ffca28',
                        'danger' => '#ef5350',
                        'background' => '#fce4ec',
                        'card' => '#ffffff',
                        'cardSecondary' => '#f8bbd0',
                        'cardAccent' => '#ffebee',
                        'text' => '#d81b60',
                        'textSecondary' => '#ad1457'
                    ]
                ];
            case 'lagoon':
                return [
                    'name' => 'lagoon',
                    'colors' => [
                        'primary' => '#14b8a6',
                        'secondary' => '#5eead4',
                        'success' => '#10b981',
                        'info' => '#0ea5e9',
                        'warning' => '#f59e0b',
                        'danger' => '#ef4444',
                        'background' => '#ecfeff',
                        'card' => '#ffffff',
                        'cardSecondary' => '#cffafe',
                        'cardAccent' => '#f0fdfa',
                        'text' => '#0d9488',
                        'textSecondary' => '#0f766e'
                    ]
                ];
            case 'amber':
                return [
                    'name' => 'amber',
                    'colors' => [
                        'primary' => '#f59e0b',
                        'secondary' => '#fcd34d',
                        'success' => '#84cc16',
                        'info' => '#06b6d4',
                        'warning' => '#fb923c',
                        'danger' => '#ef4444',
                        'background' => '#fffbea',
                        'card' => '#ffffff',
                        'cardSecondary' => '#fef3c7',
                        'cardAccent' => '#fefce8',
                        'text' => '#d97706',
                        'textSecondary' => '#b45309'
                    ]
                ];
            default:
                return $defaultTheme;
        }
    }
} 