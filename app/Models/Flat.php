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
        static::deleting(function (Flat $flat) {

            $flat->residents->each->delete();

        });

        static::restored(function (Flat $flat) {

            $flat->residents()
                ->onlyTrashed()
                ->get()
                ->each
                ->restore();

        });
    }

    protected function wing(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value),
            get: fn (string $value) => strtoupper($value),
        );
    }
}
