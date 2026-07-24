<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            // Expose plain secret only once when generated
            'secret' => $this->when(isset($this->plain_secret), $this->plain_secret),
            'status' => $this->status,
            'scopes' => $this->scopes,
            'ip_whitelist' => $this->ip_whitelist,
            'rate_limit_limit' => $this->rate_limit_limit,
            'quota_limit' => $this->quota_limit,
            'quota_used' => $this->quota_used,
            'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'last_used_at' => $this->last_used_at ? $this->last_used_at->toDateTimeString() : null,
            'rotation_grace_expires_at' => $this->rotation_grace_expires_at ? $this->rotation_grace_expires_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
