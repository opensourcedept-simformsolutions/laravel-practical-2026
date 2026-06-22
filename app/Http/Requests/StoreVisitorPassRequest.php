<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorPassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'purpose' => ['required', 'string'],
            'visit_date' => ['required', 'date'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
        ];

        if (auth()->check() && auth()->user()->role->name === 'gatekeeper') {
            $rules['flat_id'] = ['required', 'exists:flats,id'];
        }

        return $rules;
    }
}
