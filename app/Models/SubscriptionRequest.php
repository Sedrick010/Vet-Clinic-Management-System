<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'clinic_id',
        'user_id',
        'plan',
        'duration',
        'payment_method',
        'payment_reference',
        'payment_details',
        'amount_paid',
        'notes',
        'admin_notes',
        'payment_date',
        'receipt_image_path',
        'status',
        'auto_renew',
        'guest_clinic_name',
        'guest_email',
        'guest_phone',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'expired_at',
        'rejection_reason',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_paid' => 'float',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expired_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * Get the clinic that owns the subscription request.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user that owns the subscription request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin user who processed the request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the subscription is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'approved' && 
               $this->expired_at !== null && 
               now()->lt($this->expired_at);
    }

    /**
     * Check if the subscription is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === 'approved' && 
               $this->expired_at !== null && 
               now()->gte($this->expired_at);
    }

    /**
     * Check if the subscription is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the time remaining for the subscription.
     *
     * @return string
     */
    public function getTimeRemaining()
    {
        if (!$this->isActive() || !$this->expired_at) {
            return 'N/A';
        }
        
        return now()->diffForHumans($this->expired_at, true) . ' remaining';
    }
}
