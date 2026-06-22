<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id',
        'wing',
        'floor',
        'flat_number',
    ];

    public function wing()
    {
        return $this->belongsTo(Wing::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function visitorLogs()
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    protected static function booted()
    {
        static::deleting(function ($flat) {
            $flat->residents()->delete();
        });
    }

    public function getDisplayNumberAttribute(): string
    {
        return $this->wing->name . '-' .
            ($this->floor * 100 + $this->flat_number);
    }
}
