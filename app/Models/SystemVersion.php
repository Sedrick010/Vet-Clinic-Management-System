<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'name',
        'description',
        'is_current',
        'released_at'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'released_at' => 'datetime'
    ];

    /**
     * Get the current system version
     *
     * @return self|null
     */
    public static function getCurrentVersion()
    {
        return self::where('is_current', true)->first();
    }

    /**
     * Get the update associated with this version
     */
    public function systemUpdate()
    {
        return $this->belongsTo(SystemUpdate::class, 'version', 'version');
    }
} 