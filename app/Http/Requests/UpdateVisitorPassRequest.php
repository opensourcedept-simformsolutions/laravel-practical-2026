<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitorPassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'digits:10',
            ],

            'purpose' => [
                'required',
                'string',
                'max:255',
            ],

            'visit_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'vehicle_number' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'visit_date.required' => 'Visit date is required.',
        ];
    }
}
