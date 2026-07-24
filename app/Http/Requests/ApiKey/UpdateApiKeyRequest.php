<?php

namespace App\Http\Requests\ApiKey;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'required|string',
            'ip_whitelist' => 'nullable|array',
            'ip_whitelist.*' => 'required|ip',
            'rate_limit_limit' => 'nullable|integer|min:0',
            'quota_limit' => 'nullable|integer|min:0',
        ];
    }
}
