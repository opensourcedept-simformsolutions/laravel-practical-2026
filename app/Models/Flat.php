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
        'wing_id',
        'floor',
        'flat_number',
    ];

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

    public function wingRelation()
    {
        return $this->belongsTo(Wing::class, 'wing_id');
    }

    protected static function booted()
    {
        static::deleting(function ($flat) {
            $flat->residents()->delete();
        });
    }

    // Keep backward-compatible `wing` attribute accessor so views can continue
    // to use `$flat->wing`. Preference is given to the new `wingRelation`.
    public function getWingAttribute($value)
    {
        if ($this->wing_id && $this->relationLoaded('wingRelation')) {
            return $this->wingRelation->name;
        }

        if ($this->wing_id) {
            $wing = Wing::find($this->wing_id);

            return $wing ? $wing->name : strtoupper($value);
        }

        return strtoupper($value);
    }
}
