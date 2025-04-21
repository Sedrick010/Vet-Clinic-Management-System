<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'guest_clinic_name',
        'guest_email',
        'guest_phone', 
        'plan',
        'duration',
        'payment_method',
        'payment_reference',
        'payment_details',
        'payment_proof',
        'amount_paid',
        'notes',
        'status',
        'auto_renew',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'expired_at',
        'rejection_reason',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_paid' => 'decimal:2',
        'auto_renew' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if the subscription is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               $this->expired_at !== null && 
               now()->lt($this->expired_at);
    }
    
    /**
     * Check if the subscription is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if the subscription is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === 'active' && 
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
     * Check if the subscription is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
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