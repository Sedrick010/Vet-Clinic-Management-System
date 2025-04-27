<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'client_name',
        'pet_id',
        'client_id',
        'staff_id',
        'start_time',
        'end_time',
        'status',
        'reason',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * For backward compatibility, alias for staff relationship 
     */
    public function veterinarian(): BelongsTo
    {
        return $this->staff();
    }
    
    /**
     * Get the pet associated with this appointment
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    
    /**
     * Get the appropriate color class for the appointment status
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            'confirmed' => 'primary',
            'no-show' => 'warning',
            'scheduled' => 'info',
            default => 'secondary',
        };
    }
} 