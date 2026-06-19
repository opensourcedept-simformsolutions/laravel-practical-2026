<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
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
        $resident = $this->route('resident');
        // dd($resident);
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($resident->user_id)
            ],
            'phone' => ['required', 'string', 'max:15'],
            'flat_id' => ['required', 'exists:flats,id'],
            'resident_type' => ['required', 'in:owner,tenant'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'name is required.',
            'email.required' => 'email is required.',
            'phone.required' => 'phone number is required.',
            'flat_id.required' => 'flat selection  is required.',
            'resident_type.required' => 'resident_type is required.',
        ];
    }
}
