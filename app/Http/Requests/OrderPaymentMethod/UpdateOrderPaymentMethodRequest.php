<?php

namespace App\Http\Requests\OrderPaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderPaymentMethodRequest extends FormRequest
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
            "amount"=>[
                "required",
                "numeric",
                "min:0"
            ],
            "transaction_id"=>[
                "nullable",
                "string",
            ],
            "payment_status"=>[
                "required",
                "string",
            ],
            "paid_at"=>[
                "nullable",
                "date",
            ],
            "order_id"=>[
                "required",
                "integer",
                "exists:order,id"
            ],
            "bank_id"=>[
                "required",
                "integer",
                "exists:bank,id"
            ],
        ];
    }
}
