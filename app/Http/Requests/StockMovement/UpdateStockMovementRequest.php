<?php

namespace App\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockMovementRequest extends FormRequest
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
            "product_id"=>[
                "required",
                "integer",
                "exists:products,id"
            ],
            "user_id"=>[
                "required",
                "integer",
                "exists:users,id"
            ],
            "type"=>[
                "required",
                "string",
            ],
            "quantity"=>[
                "required",
                "integer",
                "min:1"
            ],
            "stock_before"=>[
                "required",
                "integer",
                "min:0"
            ],
            "stock_after"=>[
                "required",
                "integer",
                "min:0"
            ],
            "reference_type" => [
                "required",
                "string"
            ],
            "reference_id" => [
                "nullable",
                "integer"
            ],
        ];
    }
}
