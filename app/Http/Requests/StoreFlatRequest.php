<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreFlatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'wing' => ['required', 'string', 'max:10'],
            'floor' => ['required', 'integer', 'min:0'],
            'flat_number' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'wing.required' => 'Wing is required.',
            'floor.required' => 'Floor is required.',
            'flat_number.required' => 'Flat number is required.',
        ];
    }
}
