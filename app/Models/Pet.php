<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Client;
use App\Models\Appointment;

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
        return $this->belongsTo(Client::class, 'owner_id');
    }

    /**
     * Alternative method to get the client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'owner_id');
    }

    /**
     * Get the appointments for this pet.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
} 