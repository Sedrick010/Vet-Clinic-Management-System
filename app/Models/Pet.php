<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'owner_id',
        'name',
        'species',
        'breed',
        'birthdate',
        'gender',
        'notes'
    ];

    protected $casts = [
        'birthdate' => 'date'
    ];

    /**
     * Get the owner (client) of this pet.
     */
    public function owner()
    {
        return $this->belongsTo(\App\Models\Client::class, 'owner_id')->on('tenant');
    }

    /**
     * Alternative method to get the client.
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class, 'owner_id')->on('tenant');
    }

    /**
     * Get the appointments for this pet.
     */
    public function appointments()
    {
        return $this->hasMany(\App\Models\Appointment::class)->on('tenant');
    }
} 