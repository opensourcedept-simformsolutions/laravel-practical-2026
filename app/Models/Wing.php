<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wing extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->hasMany(Flat::class, 'wing_id');
    }

    protected static function booted()
    {
        static::deleting(function ($wing) {
            // When a wing is deleted, soft-delete its flats
            $wing->flats()->delete();
        });
    }

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }
}
