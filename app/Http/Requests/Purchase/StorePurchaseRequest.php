<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            "purchase_date"=>[
                "required",
                "string",
            ],
            "total"=>[
                "required",
                "numeric",
                "min:0",
            ],
            "status"=>[
                "required",
                "string",
            ],
            "supplier_id"=>[
                "required",
                "integer",
                "exists:suppliers,id"
            ],
            "user_id"=>[
                "required",
                "integer",
                "exists:users,id"
            ],
        ];
    }
}
