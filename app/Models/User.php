<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'society_id',
        'email',
        'phone',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function visitorLogs()
    {
        return $this->hasMany(VisitorLog::class, 'gatekeeper_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function isSuperAdmin()
    {
        return $this->role?->name === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role?->name === 'admin';
    }

    public function isResident()
    {
        return $this->role?->name === 'resident';
    }

    public function isGatekeeper()
    {
        return $this->role?->name === 'gatekeeper';
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public static $cascading = false;

    protected static function booted()
    {
        static::deleting(function ($user) {
            if (static::$cascading || (class_exists(Resident::class) && Resident::$cascading)) {
                return;
            }
            static::$cascading = true;
            try {
                if ($user->isForceDeleting()) {
                    $user->resident()?->withTrashed()->forceDelete();
                    $user->complaints()->withTrashed()->forceDelete();
                    $user->notifications()->withTrashed()->forceDelete();
                } else {
                    $user->resident()?->delete();
                    $user->complaints()->delete();
                    $user->notifications()->delete();
                }
            } finally {
                static::$cascading = false;
            }
        });

        static::restoring(function ($user) {
            if (static::$cascading || (class_exists(Resident::class) && Resident::$cascading)) {
                return;
            }
            static::$cascading = true;
            try {
                $deletedAt = $user->deleted_at;
                if ($deletedAt) {
                    $threshold = $deletedAt->copy()->subSeconds(5);
                    $user->resident()?->onlyTrashed()->where('deleted_at', '>=', $threshold)->restore();
                    $user->complaints()->onlyTrashed()->where('deleted_at', '>=', $threshold)->restore();
                    $user->notifications()->onlyTrashed()->where('deleted_at', '>=', $threshold)->restore();
                }
            } finally {
                static::$cascading = false;
            }
        });
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->latest();
    }
}
