<?php

namespace App\Http\Requests;

use App\Enum\ComplaintCategory;
use App\Enum\ComplaintStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintRequest extends FormRequest
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
        if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) {
            return [
                'status' => ['required', Rule::enum(ComplaintStatus::class)],
                'admin_notes' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'category' => ['required' , Rule::enum(ComplaintCategory::class)],
            'description' => ['required', 'string' ,'min:10', 'max:1000'],
        ];
    }
}
