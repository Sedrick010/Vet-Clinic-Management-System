<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'species',
        'breed',
        'date_of_birth',
        'gender',
        'medical_history'
    ];

    protected $casts = [
        'date_of_birth' => 'date'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
} 