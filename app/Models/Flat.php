<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
}
