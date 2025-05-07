<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SystemUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'name',
        'description',
        'changes',
        'features',
        'bug_fixes',
        'is_critical',
        'is_security',
        'is_mandatory',
        'available_from',
        'expires_at'
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'is_security' => 'boolean', 
        'is_mandatory' => 'boolean',
        'available_from' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Get all available updates that haven't expired
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAvailableUpdates()
    {
        return self::where('available_from', '<=', Carbon::now())
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', Carbon::now());
            })
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Get the clinic updates associated with this system update
     */
    public function clinicUpdates()
    {
        return $this->hasMany(ClinicUpdate::class);
    }
} 