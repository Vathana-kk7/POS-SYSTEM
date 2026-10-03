<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
            "name" => [
                "required",
                "string",
                "max:255",
            ],
            "email" => [
                "nullable",
                "email",
                "max:255",
                Rule::unique("customers", "email")->ignore($this->route("customer")),
            ],
            "phone" => [
                "required",
                "string",
                "max:20",
            ],
            "address" => [
                "nullable",
                "string",
            ],
            "group" => [
                "nullable",
                "string",
                "in:Standard,VIP,Wholesale",
            ],
            "status" => [
                "nullable",
                "string",
                "in:Active,Inactive",
            ],
        ];
    }
}
