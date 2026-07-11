<?php

namespace App\Http\Requests\User;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name"=>[
                'required',
                "string",
                "max:255",
            ],
            "email"=>[
                'required',
                "email",
                "unique:users,email",
            ],
            "password"=>[
                'required',
                "string",
                "min:6",
            ],
            "role_id"=>[
                'required',
                "exists:roles,id",
            ],

        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',

            'email.required' => 'Email is required',

            'email.unique' => 'Email already exists',

            'password.min' => 'Password must be at least 6 characters',

            'role_id.exists' => 'Role does not exist',
        ];
    }
}
