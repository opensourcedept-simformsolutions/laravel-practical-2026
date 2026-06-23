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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'purpose' => ['required', 'string'],
            'visit_date' => ['required', 'date'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
        ];

        if (auth()->check() && auth()->user()->isAdmin() || auth()->user()->isGatekeeper()) {
            $rules['flat_id'] = [
                'required',
                Rule::exists('flats', 'id')->where(function ($query) {
                    $query->where(
                        'society_id',
                        auth()->user()->society_id
                    );
                }),
            ];
        }

        return $rules;
    }
}
