<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'system_update_id',
        'is_applied',
        'is_dismissed',
        'applied_at',
        'dismissed_at',
        'notes'
    ];

    protected $casts = [
        'is_applied' => 'boolean',
        'is_dismissed' => 'boolean',
        'applied_at' => 'datetime',
        'dismissed_at' => 'datetime'
    ];

    /**
     * Get the clinic that owns this update status
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the system update associated with this clinic update
     */
    public function systemUpdate()
    {
        return $this->belongsTo(SystemUpdate::class, 'system_update_id');
    }
} 