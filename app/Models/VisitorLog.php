<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visitor_id',
        'flat_id',
        'purpose',
        'created_by',
        'gatekeeper_id',
        'entry_time',
        'exit_time',
        'status',
        'photo_path',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class)->withTrashed();
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class)->withTrashed();
    }

    public function gatekeeper()
    {
        return $this->belongsTo(User::class, 'gatekeeper_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getQrCodeDataAttribute()
    {
        return encrypt($this->id);

    }
}
