<?php

namespace App\Http\Requests\Flat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wing' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9]+$/',
            ],

            'floor' => [
                'required',
                'integer',
                'min:0',
                'max:50',
            ],

            'flat_number' => [
                'required',
                'integer',
                'between:1,9999',
                Rule::unique('flats')
                    ->where(function ($query) {

                        return $query->where(
                            'society_id',
                            auth()->user()->isSuperAdmin() ? request('society_id') : auth()->user()->society_id
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
            'wing.regex' => 'Wing must be valid.',
            'wing.max' => 'Wing may not exceed 20 characters.',

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
