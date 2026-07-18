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
    // យក product id ចេញពី route params
    $productId = $this->route('product');

    return [
        "name" => "required|string",
        "stock_qty" => "required|integer",
        "cost_price" => "required|numeric",
        "description" => "nullable|string",
        // លើកលែង product id នេះចេញ មិនបាច់គិត unique ឡើយពេល update
        "sku" => "required|string|unique:products,sku," . $productId,
        "image" => "nullable|string",
        "min_stock_level" => "required|integer",
        "status" => "required|string",
        "selling_price" => "required|numeric",
        "brand_id" => "required|integer|exists:brands,id",
        "category_id" => "required|integer|exists:categories,id",
        "supplier_ids" => "required|array",
        "supplier_ids.*" => "required|integer|exists:suppliers,id",
    ];
}
}
