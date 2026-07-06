<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Society extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'pincode',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function wings()
    {
        return $this->hasMany(Wing::class);
    }

    public function flats()
    {
        // flats via wings
        return $this->hasManyThrough(Flat::class, Wing::class, 'society_id', 'wing_id', 'id', 'id');
    }
}
