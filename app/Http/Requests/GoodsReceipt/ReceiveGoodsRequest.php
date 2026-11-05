<?php

namespace App\Http\Requests\GoodsReceipt;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveGoodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('purchase')) {
            $this->merge(['purchase_id' => $this->route('purchase')->id]);
        }
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'integer', 'exists:purchases,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:purchase_item,id',
            ],
            'items.*.received_quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
