<?php

namespace App\Http\Requests\ApiKey;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authed via route middleware (Sanctum/Session)
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'required|string',
            'ip_whitelist' => 'nullable|array',
            'ip_whitelist.*' => 'required|ip',
            'rate_limit_limit' => 'nullable|integer|min:0',
            'quota_limit' => 'nullable|integer|min:0',
            'expires_in_days' => 'nullable|integer|min:1',
        ];
    }
}
