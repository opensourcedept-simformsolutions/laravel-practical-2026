<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'total_rows',
        'imported_rows',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
