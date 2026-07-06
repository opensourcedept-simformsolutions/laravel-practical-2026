<?php

namespace App\Http\Requests\Wing;

use Illuminate\Foundation\Http\FormRequest;

class StoreWingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'total_floors' => ['required', 'integer', 'min:1', 'max:200'],
            'flats_per_floor' => ['required', 'integer', 'min:1', 'max:200'],
            'society_id' => auth()->user()->isSuperAdmin() ? ['required', 'exists:societies,id'] : ['nullable'],
        ];
    }
}
