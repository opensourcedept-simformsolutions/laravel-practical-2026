<?php

namespace App\Http\Requests\Delivery;

use App\Enums\DeliveryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handle validation for updating an existing delivery record.
 *
 * Supports partial updates by validating only the fields
 * present in the request payload.
 */
class UpdateDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for updating a delivery.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'resident_id' => ['required', 'exists:residents,id'],
            'vendor' => ['required', 'string', 'max:255'],
            'package_details' => ['required', 'string', 'min:3'],
        ];

        if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) {
            $rules['status'] = ['required', Rule::enum(DeliveryStatus::class)];
        }

        return $rules;
    }
}

