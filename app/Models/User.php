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

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
                    ->withPivot('is_granted');
    }

    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function visitorLogs()
    {
        return $this->hasMany(VisitorLog::class, 'gatekeeper_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->whereNull('read_at')->latest();
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

    public function hasPermission($permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $slug = strtolower($permission instanceof Permission ? $permission->slug : (string) $permission);

        if (! $this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        $direct = $this->permissions->firstWhere('slug', $slug);
        if ($direct !== null) {
            return (bool) $direct->pivot->is_granted;
        }

        if ($this->role) {
            if (! $this->role->relationLoaded('permissions')) {
                $this->role->load('permissions');
            }
            return $this->role->permissions->contains('slug', $slug);
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function getAllPermissions()
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        if (! $this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        $rolePermissions = collect();
        if ($this->role) {
            if (! $this->role->relationLoaded('permissions')) {
                $this->role->load('permissions');
            }
            $rolePermissions = $this->role->permissions;
        }

        $directGrants = $this->permissions->filter(fn ($p) => $p->pivot->is_granted);
        $directRevocations = $this->permissions->filter(fn ($p) => ! $p->pivot->is_granted)->pluck('id')->toArray();

        return $rolePermissions
            ->reject(fn ($p) => in_array($p->id, $directRevocations))
            ->merge($directGrants)
            ->unique('id');
    }

    public function givePermissionTo(...$permissions): static
    {
        $resolved = collect($permissions)->flatten()->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('slug', strtolower(trim($permission)))->firstOrFail();
            }
            return $permission;
        });

        $syncData = [];
        foreach ($resolved as $perm) {
            $syncData[$perm->id] = ['is_granted' => true];
        }

        if (! empty($syncData)) {
            $this->permissions()->syncWithoutDetaching($syncData);
        }

        $this->load('permissions');

        return $this;
    }

    public function revokePermissionTo(...$permissions): static
    {
        $resolved = collect($permissions)->flatten()->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('slug', strtolower(trim($permission)))->firstOrFail();
            }
            return $permission;
        });

        $syncData = [];
        foreach ($resolved as $perm) {
            $syncData[$perm->id] = ['is_granted' => false];
        }

        if (! empty($syncData)) {
            $this->permissions()->syncWithoutDetaching($syncData);
        }

        $this->load('permissions');

        return $this;
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
}
