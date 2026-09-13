<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'max:20480', // បង្កើនទំហំ max ដល់ 20MB វិញព្រោះ zip ផ្ទុក image អាចធំជាង 2MB ធម្មតា
                'mimes:xlsx,xls,csv,zip',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,text/plain,application/csv,application/x-csv,application/zip,application/x-zip-compressed'
            ],
        ];
    }
}
