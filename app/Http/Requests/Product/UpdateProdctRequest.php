<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdctRequest extends FormRequest
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
    $productId = $this->route('product');

    return [
        "name" => "required|string",
        "stock_qty" => "required|integer",
        "cost_price" => "required|numeric",
        "description" => "nullable|string",
        "sku" => "required|string|unique:products,sku," . $productId,

        // Only apply file validations if the input is actually an uploaded file
        'image' => [
            'nullable',
            function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    // Validate only when a new file is uploaded
                    validator([$attribute => $value], [
                        $attribute => 'file|image|mimes:jpg,jpeg,png,webp|max:5120'
                    ])->validate();
                }
            },
        ],

        "min_stock_level" => "required|integer",
        "status" => "required|string",
        "product_type" => "required|string",
        "selling_price" => "required|numeric",
        "brand_id" => "required|integer|exists:brands,id",
        "category_id" => "required|integer|exists:categories,id",
    ];
}
}
