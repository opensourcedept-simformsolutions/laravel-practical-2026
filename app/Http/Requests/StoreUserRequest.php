<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that ap http://127.0.0.1:8000/admin/users ply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email'
                
                    
            ],
            'phone' => [
                'required',
                'digits_between:10,15',
                'unique:users,phone'
            ],
            
            'password' => [
                'required',
                'min:1',
            ],

            'role_id' => [
                'required',
                'exists:roles,id'
                ],

        ];
    }
}