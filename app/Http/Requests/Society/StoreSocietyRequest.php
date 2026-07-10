<?php

namespace App\Http\Requests\Society;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocietyRequest extends FormRequest
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

            'wings' => [
                'nullable',
                'array',
            ],

            'wings.*.name' => [
                'required',
                'string',
                'distinct',
                'max:50',
            ],

            'wings.*.total_floors' => [
                'required',
                'integer',
                'min:1',
                'max:200',
            ],

            'wings.*.flats_per_floor' => [
                'required',
                'integer',
                'min:1',
                'max:50',
            ],

            'residents' => [
                'nullable',
                'array',
            ],

            'residents.*.name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z\s\.\'-]+$/u',
            ],

            'residents.*.email' => [
                'required',
                'email',
                'distinct',
                'unique:users,email',
            ],

            'residents.*.phone' => [
                'required',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'residents.*.wing' => [
                'required',
                'string',
            ],

            'residents.*.flat_number' => [
                'required',
                'integer',
            ],

            'residents.*.resident_type' => [
                'required',
                'in:owner,tenant',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $wings = $this->input('wings', []);
            $residents = $this->input('residents', []);

            $wingsMap = [];
            foreach ($wings as $wing) {
                if (isset($wing['name'])) {
                    $wingsMap[$wing['name']] = $wing;
                }
            }

            foreach ($residents as $index => $resident) {
                $wingName = $resident['wing'] ?? '';
                $flatNumber = $resident['flat_number'] ?? '';

                if (! isset($wingsMap[$wingName])) {
                    $validator->errors()->add("residents.{$index}.wing", "Wing '{$wingName}' does not exist in the configured wings.");

                    continue;
                }

                $wing = $wingsMap[$wingName];
                $floor = (int) ($flatNumber / 100);

                if ($floor < 1 || $floor > $wing['total_floors']) {
                    $validator->errors()->add("residents.{$index}.flat_number", "Flat '{$flatNumber}' is on floor {$floor}, which is invalid for Wing '{$wingName}' (configured with {$wing['total_floors']} floors).");
                }
            }
        });
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
