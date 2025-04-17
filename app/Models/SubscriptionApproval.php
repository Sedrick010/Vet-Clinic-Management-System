<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionApproval extends Model
{
    protected $fillable = [
        'subscription_id',
        'clinic_id',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    /**
     * Get the subscription associated with the approval.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the clinic associated with the approval.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Approve the subscription request.
     */
    public function approve(): bool
    {
        $this->status = 'approved';
        $this->approved_at = now();
        $this->rejected_at = null;
        $this->rejection_reason = null;
        
        // Update the subscription status
        $this->subscription->update([
            'status' => 'active'
        ]);
        
        return $this->save();
    }

    /**
     * Reject the subscription request.
     */
    public function reject(string $reason): bool
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->approved_at = null;
        $this->rejection_reason = $reason;
        
        // Update the subscription status
        $this->subscription->update([
            'status' => 'inactive'
        ]);
        
        return $this->save();
    }
} 