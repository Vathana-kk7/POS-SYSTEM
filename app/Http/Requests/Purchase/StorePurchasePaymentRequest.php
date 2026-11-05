<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'method' => ['required', Rule::in(['cash', 'bakong', 'bank_transfer'])],
            'reference_no' => ['required', 'string', 'max:255', 'unique:purchase_payments,reference_no'],
            'proof_image' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
