<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'string', 'in:xlsx,csv,pdf'],
            'status' => ['sometimes', 'nullable', 'string', 'in:active,inactive'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
