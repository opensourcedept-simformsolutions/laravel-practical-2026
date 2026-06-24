<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'wing' => ['required', 'string',  'max:5',
                'regex:/^[A-Za-z]+$/', ],
            'floor' => ['required', 'integer', 'min:0',
                'max:50', ],
            'flat_number' => [
                'required',
                'integer',
                'between:1,9999',
                Rule::unique('flats')
                    ->where(function ($query) {

                        return $query->where(
                            'society_id',
                            auth()->user()->society_id
                        )->where(
                            'wing',
                            request('wing')
                        )->where(
                            'floor',
                            request('floor')
                        );

                    }),
            ],

            'society_id' => auth()->user()->isSuperAdmin()
                ? ['required', 'exists:societies,id']
                : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'wing.required' => 'Wing is required.',
            'wing.regex' => 'Wing must contain only letters.',
            'wing.max' => 'Wing may not exceed 5 characters.',

            'floor.required' => 'Floor is required.',
            'floor.integer' => 'Floor must be a number.',
            'floor.between' => 'Floor must be between 0 and 50.',

            'flat_number.required' => 'Flat number is required.',
            'flat_number.integer' => 'Flat number must be numeric.',
            'flat_number.between' => 'Flat number must be between 1 and 9999.',
            'flat_number.unique' => 'This flat already exists in the selected wing and floor.',
        ];
    }
}
