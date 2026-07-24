<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'receiver_id',
        'type',
        'amount',
        'currency',
        'status',
        'gateway_payment_id',
        'gateway_order_id',
        'gateway_signature',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the resident who made the payment.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class)->withTrashed();
    }

    /**
     * Get the receiver of the payment (e.g. flat owner / admin).
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id')->withTrashed();
    }
}
