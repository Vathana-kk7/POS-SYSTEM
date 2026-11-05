<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
            "name"=>"sometimes|required|string|max:255",
            "contact_person"=>"nullable|string|max:255",
            "email"=>["nullable","email","max:255",Rule::unique("suppliers","email")->ignore($this->route("supplier"))],
            "address"=>"nullable|string|max:255",
            "phone"=>"sometimes|required|string|max:30",
            "status"=>"nullable|in:Active,Inactive",
            "bank_name"=>"sometimes|nullable|string|max:255",
            "account_name"=>"sometimes|nullable|string|max:255",
            "account_number"=>"sometimes|nullable|string|max:255",
            "bakong_account_id"=>"sometimes|nullable|string|max:255",
            "khqr_image"=>"sometimes|nullable|image|max:5120",
        ];
    }
}
