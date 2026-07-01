<?php

namespace App\Http\Requests\Resident;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resident = $this->route('resident');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z\s\.\'-]+$/',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($resident->user_id)
                    ->withoutTrashed(),
            ],

            'phone' => [
                'required',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'flat_id' => [
                'required',
                'exists:flats,id',
            ],

            'resident_type' => [
                'required',
                Rule::in(['owner', 'tenant']),
            ],
            'society_id' => [
                auth()->user()->isSuperAdmin() ? 'required' : 'nullable',
                'exists:societies,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Resident name is required.',
            'name.min' => 'Resident name must be at least 2 characters.',
            'name.max' => 'Resident name may not be greater than 100 characters.',
            'name.regex' => 'Resident name may contain only letters, spaces, apostrophes (\'), hyphens (-), and dots (.).',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address may not exceed 255 characters.',
            'email.unique' => 'This email address is already registered.',

            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 7 characters.',
            'phone.max' => 'Phone number may not exceed 20 characters.',
            'phone.regex' => 'Please enter a valid phone number using digits, spaces, +, -, and ().',

            'flat_id.required' => 'Please select a flat.',
            'flat_id.exists' => 'The selected flat is invalid.',

            'resident_type.required' => 'Please select a resident type.',
            'resident_type.in' => 'Resident type must be either Owner or Tenant.',
        ];
    }
}
