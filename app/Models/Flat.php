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
        'wing',
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
        static::saving(function ($flat) {
            if ($flat->wing_id) {
                $wing = Wing::find($flat->wing_id);
                if ($wing) {
                    $flat->wing = $wing->name;
                }
            } elseif ($flat->wing && $flat->society_id) {
                $wing = Wing::firstOrCreate([
                    'society_id' => $flat->society_id,
                    'name' => strtoupper($flat->wing),
                ], [
                    'total_floors' => 15,
                    'flats_per_floor' => 10,
                ]);
                $flat->wing_id = $wing->id;
                $flat->wing = $wing->name;
            }
        });
        static::deleting(function ($flat) {
            if ($flat->isForceDeleting()) {
                $flat->residents()->withTrashed()->get()->each->forceDelete();
                $flat->deliveries()->withTrashed()->get()->each->forceDelete();
                $flat->visitorLogs()->withTrashed()->get()->each->forceDelete();
            } else {
                $flat->residents()->get()->each->delete();
                $flat->deliveries()->get()->each->delete();
                $flat->visitorLogs()->get()->each->delete();
            }
        });

        static::restoring(function ($flat) {
            $deletedAt = $flat->deleted_at;
            if ($deletedAt) {
                $threshold = $deletedAt->copy()->subSeconds(5);
                $flat->residents()->onlyTrashed()->where('deleted_at', '>=', $threshold)->get()->each->restore();
                $flat->deliveries()->onlyTrashed()->where('deleted_at', '>=', $threshold)->get()->each->restore();
                $flat->visitorLogs()->onlyTrashed()->where('deleted_at', '>=', $threshold)->get()->each->restore();
            }
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
