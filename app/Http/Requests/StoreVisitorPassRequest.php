<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitorPassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s\.\'-]+$/u',
            ],

            'phone' => [
                'required',
                'regex:/^[6-9][0-9]{9}$/',
            ],

            'purpose' => [
                'required',
                'string',
                'min:2',
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
                'max:20',
                'regex:/^([A-Z]{2}\s?\d{1,2}\s?[A-Z]{1,3}\s?\d{1,4}|\d{2}\s?BH\s?\d{4}\s?[A-Z]{1,2})$/i',
            ],
        ];

        $user = $this->user();

        if ($user && ($user->isAdmin() || $user->isGatekeeper())) {
            $rules['flat_id'] = [
                'required',
                Rule::exists('flats', 'id')->where(function ($query) use ($user) {
                    $query->where(
                        'society_id',
                        $user->society_id
                    );
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name contains invalid characters.',
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
            'purpose.not_regex' => 'HTML tags are not allowed in purpose.',
            'vehicle_number.regex' => 'Invalid vehicle number format.',
        ];
    }
}
