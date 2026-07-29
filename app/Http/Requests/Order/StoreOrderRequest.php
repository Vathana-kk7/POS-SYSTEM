<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            "total"=>[
                "required",
                "numeric",
                "min:0",
            ],
            "discount"=>[
                "required",
                "numeric",
                "min:0",
            ],
            "tax"=>[
                "required",
                "numeric",
                "min:0",
            ],
            "grand_total"=>[
                "required",
                "numeric",
                "min:0",
            ],
            "payment_status"=>[
                "required",
                "string",
            ],
            "order_status"=>[
                "required",
                "string",
            ],
            "customer_id"=>[
                "required",
                "integer",
                "exists:customers,id",
            ],
        ];
    }
}
