<?php

namespace App\Http\Requests\Backup;

use Illuminate\Foundation\Http\FormRequest;

class CreateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'table_name' => ['nullable', 'string', 'max:255'],
            'upload_cloud' => ['nullable', 'boolean'],
        ];
    }
}
