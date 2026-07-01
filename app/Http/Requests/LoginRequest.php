<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
// RequestLogin
class LoginRequest extends FormRequest
{
    // ប្តូរពី false ទៅជា true ដាច់ខាត
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }
}
