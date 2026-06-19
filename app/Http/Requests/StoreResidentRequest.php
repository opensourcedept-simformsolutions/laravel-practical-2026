<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->withoutTrashed(),
            ],
            'phone' => ['required', 'string', 'max:15'],
            'flat_id' => ['required', 'exists:flats,id'],
            'resident_type' => ['required', 'in:owner,tenant'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required.',
            'flat_id.required' => 'Flat number is required.',
            'resident_type.required' => 'select a valid resident_type',
        ];
    }
}
