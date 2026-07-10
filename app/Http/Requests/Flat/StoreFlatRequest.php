<?php

namespace App\Http\Requests\Flat;

use App\Models\Wing;
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
        $wing = null;
        if ($this->filled('wing_id')) {
            $wing = Wing::find($this->input('wing_id'));
        }

        $floorMax = $wing ? (int) $wing->total_floors : 50;
        $flatMax = $wing ? (int) $wing->flats_per_floor : 9999;

        return [
            'wing_id' => ['required', 'exists:wings,id'],

            'floor' => [
                'required',
                'integer',
                'min:1',
                "max:{$floorMax}",
            ],

            'flat_number' => [
                'required',
                'integer',
                'min:1',
                'max:99999',
                Rule::unique('flats')->where(function ($query) {
                    return $query->where('wing_id', request('wing_id'))->where('floor', request('floor'));
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
            'wing_id.required' => 'Wing is required.',
            'wing_id.exists' => 'Selected wing is invalid.',

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
