<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get the pets that belong to this client.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'owner_id')->on($this->getConnectionName());
    }

    /**
     * Get the appointments for this client.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->on($this->getConnectionName());
    }
} 