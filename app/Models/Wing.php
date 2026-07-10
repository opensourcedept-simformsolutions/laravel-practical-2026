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
            if ($wing->isForceDeleting()) {
                $wing->flats()->withTrashed()->get()->each->forceDelete();
            } else {
                $wing->flats()->get()->each->delete();
            }
        });

        static::restoring(function ($wing) {
            $deletedAt = $wing->deleted_at;
            if ($deletedAt) {
                $threshold = $deletedAt->copy()->subSeconds(5);
                $wing->flats()->onlyTrashed()->where('deleted_at', '>=', $threshold)->get()->each->restore();
            }
        });
    }

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }
}
