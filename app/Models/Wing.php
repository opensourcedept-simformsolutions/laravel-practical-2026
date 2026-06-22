<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'society_id',
        'name',
        'total_floors',
        'flats_per_floor',
    ];

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function flats()
    {
        return $this->hasMany(Flat::class);
    }
}
