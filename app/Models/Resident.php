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

    public static $cascading = false;

    protected static function booted()
    {
        static::deleting(function ($resident) {
            if (static::$cascading || (class_exists(User::class) && User::$cascading)) {
                return;
            }
            static::$cascading = true;
            try {
                $user = $resident->user;
                if ($user && ! $user->trashed()) {
                    if ($resident->isForceDeleting()) {
                        $user->forceDelete();
                    } else {
                        $user->delete();
                    }
                }
            } finally {
                static::$cascading = false;
            }
        });

        static::restoring(function ($resident) {
            if (static::$cascading || (class_exists(User::class) && User::$cascading)) {
                return;
            }
            static::$cascading = true;
            try {
                $user = $resident->user;
                if ($user && $user->trashed()) {
                    $user->restore();
                }
            } finally {
                static::$cascading = false;
            }
        });
    }
}
