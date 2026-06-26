<?php

namespace App\Http\Requests\Complaint;

use App\Enums\ComplaintCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
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
            'category' => ['required' , Rule::enum(ComplaintCategory::class)],
            'description' => ['required', 'string' ,'min:10', 'max:1000'],
        ];
    }
}
