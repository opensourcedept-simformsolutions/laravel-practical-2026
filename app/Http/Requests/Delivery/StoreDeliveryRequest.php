<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handle validation for creating a new delivery record.
 *
 * Ensures the selected resident exists and validates
 * delivery vendor and package information.
 */
class StoreDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for creating a delivery.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resident_id' => [
                'required',
                Rule::exists('residents', 'id')->where(function ($query) {
                    if (! auth()->user()->isSuperAdmin()) {
                        $query->whereIn('flat_id', function ($q) {
                            $q->select('id')
                                ->from('flats')
                                ->where('society_id', auth()->user()->society_id);
                        });
                    }
                }),
            ],
            'vendor' => ['required', 'string', 'max:255'],
            'package_details' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
