<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'secret_hash',
        'status',
        'scopes',
        'ip_whitelist',
        'rate_limit_limit',
        'quota_limit',
        'quota_used',
        'expires_at',
        'last_used_at',
        'rotated_key_id',
        'rotation_grace_expires_at',
    ];

    protected $attributes = [
        'status' => 'active',
        'rate_limit_limit' => 60,
        'quota_limit' => 10000,
        'quota_used' => 0,
    ];

    protected $casts = [
        'scopes' => 'array',
        'ip_whitelist' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'rotation_grace_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiKeyLog::class);
    }

    public function rotatedKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'rotated_key_id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isRotationGraceExpired(): bool
    {
        return $this->rotation_grace_expires_at && $this->rotation_grace_expires_at->isPast();
    }

    public function hasScope(string $scope): bool
    {
        if (empty($this->scopes)) {
            return true; // No scopes defined = all permissions allowed (or customize as needed)
        }

        // Check if wildcard '*' exists or scope exists
        return in_array('*', $this->scopes) || in_array($scope, $this->scopes);
    }

    public function isValidIp(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true;
        }

        return in_array($ip, $this->ip_whitelist);
    }

    public function incrementQuota(): void
    {
        $this->increment('quota_used');
    }

    public function hasRemainingQuota(): bool
    {
        return $this->quota_limit === 0 || $this->quota_used < $this->quota_limit;
    }
}
