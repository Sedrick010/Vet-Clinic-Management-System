<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'plan_name',
        'amount',
        'status',
        'approval_status',
        'payment_method',
        'card_last_four',
        'start_date',
        'end_date',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'status' => 'string',
        'approval_status' => 'string'
    ];

    // Define constants for status
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Define constants for approval status
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clinic that owns the subscription.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Create a subscription record in both central and tenant databases
     */
    public static function createSubscription(array $data, string $tenantConnection = null)
    {
        // Create in central database
        $subscription = self::create($data);

        // If tenant connection is provided, create in tenant database too
        if ($tenantConnection) {
            config(['database.connections.tenant.database' => $tenantConnection]);
            \DB::purge('tenant');
            
            $tenantSubscription = new self();
            $tenantSubscription->setConnection('tenant');
            $tenantSubscription->fill($data);
            $tenantSubscription->save();
        }

        return $subscription;
    }

    /**
     * Approve the subscription
     */
    public function approve(): bool
    {
        $this->approval_status = 'approved';
        $this->status = 'active';
        $this->approved_at = now();
        $this->rejected_at = null;
        $this->rejection_reason = null;
        return $this->save();
    }

    /**
     * Reject the subscription
     */
    public function reject(string $reason): bool
    {
        $this->approval_status = 'rejected';
        $this->status = 'inactive';
        $this->rejected_at = now();
        $this->approved_at = null;
        $this->rejection_reason = $reason;
        return $this->save();
    }
} 