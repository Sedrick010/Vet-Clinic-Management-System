<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pet;
use App\Models\Appointment;

class Client extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'notes'
    ];

    /**
     * Get the pets for this client.
     */
    public function pets()
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    /**
     * Get the appointments for this client.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
} 