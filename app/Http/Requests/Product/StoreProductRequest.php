<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [
        "name" => "required|string",
        "stock_qty" => "required|integer",
        "cost_price" => "required|numeric",
        "description" => "nullable|string",
        "sku" => "required|string|unique:products,sku",
        // កែប្រែពី string ទៅជា image file (កំណត់ប្រភេទ និងទំហំ file)
        "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // ទំហំអតិបរមា 2MB
        "min_stock_level" => "required|integer",
        'status' => [
            'required',
            'in:active,inactive',
        ],
        "product_type" => "required|string",
        "selling_price" => "required|numeric",
        "brand_id" => "required|integer|exists:brands,id",
        "category_id" => "required|integer|exists:categories,id",
        // "supplier_ids" => "required|array",
        // "supplier_ids.*" => "required|integer|exists:suppliers,id",
    ];
}
}
