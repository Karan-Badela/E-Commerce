<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'], 'name' => ['sometimes', 'required', 'string', 'max:255'], 'description' => ['sometimes', 'nullable', 'string'], 'price' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0'], 'stock' => ['sometimes', 'required', 'integer', 'min:0']]; }
}
