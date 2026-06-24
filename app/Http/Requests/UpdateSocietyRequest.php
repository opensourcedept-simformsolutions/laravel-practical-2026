<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocietyRequest extends FormRequest
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
                'min:2',
                'max:100',
                'regex:/^[\pL\pN\s\.\'&,\-\/()]+$/u',
            ],

            'address' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'city' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\pL\s\.\'-]+$/u',
            ],

            'state' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\pL\s\.\'-]+$/u',
            ],

            'pincode' => [
                'required',
                'regex:/^[1-9][0-9]{5}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Society name is required.',
            'name.regex' => 'Society name contains invalid characters.',

            'address.required' => 'Address is required.',

            'city.required' => 'City is required.',
            'city.regex' => 'City contains invalid characters.',

            'state.required' => 'State is required.',
            'state.regex' => 'State contains invalid characters.',

            'pincode.required' => 'Pincode is required.',
            'pincode.regex' => 'Enter a valid 6-digit Indian pincode.',
        ];
    }
}
