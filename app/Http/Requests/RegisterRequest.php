<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
// RequestRegister
class RegisterRequest extends FormRequest
{
    // ត្រូវតែបើកវាឱ្យទៅជា true
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6' // ប្តូរមកសល់ 6 ខ្ទង់វិញ ដើម្បីឱ្យត្រូវនឹងអ្វីដែលអ្នកតេស្តក្នុង Postman
        ];
    }
}
