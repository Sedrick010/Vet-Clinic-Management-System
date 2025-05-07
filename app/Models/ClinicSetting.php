<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'key',
        'value',
        'description'
    ];

    /**
     * Get the clinic that owns this setting
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
} 