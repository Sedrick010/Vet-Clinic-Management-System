<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

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
        'description',
        'database_name',
        'is_active',
        'approval_status',
        'rejection_reason',
        'owner_email',
        'owner_name',
        'temp_password',
        'subscription_status',
        'city',
        'state',
        'zip',
        'country',
        'current_subscription_id',
        'subscription_start_date',
        'subscription_end_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'approval_status' => 'string',
        'subscription_status' => 'string',
        'subscription_start_date' => 'datetime',
        'subscription_end_date' => 'datetime'
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active' 
            && $this->subscription_end_date 
            && $this->subscription_end_date->isFuture();
    }
} 