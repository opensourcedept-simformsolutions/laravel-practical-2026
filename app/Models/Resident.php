<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'flat_id',
        'resident_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
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

    protected static function booted()
    {
        static::deleting(function (Resident $resident) {

            if ($resident->user) {
                $resident->user->delete();
            }

        });

        static::restored(function (Resident $resident) {

            $resident->user()
                ->withTrashed()
                ->restore();

        });
    }
}
