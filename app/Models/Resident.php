<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Resident extends Model
{
    use HasFactory, SoftDeletes,Notifiable;

    protected $fillable = [
        'user_id',
        'flat_id',
        'resident_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function society()
    {
        return $this->hasOneThrough(Society::class, User::class);
    }
}
