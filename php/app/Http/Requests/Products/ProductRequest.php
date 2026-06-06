<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Ensure we get the ID regardless of whether it's a string or a Model
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_path' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048',
            'brand_id' => 'nullable|numeric|exists:brands,id',
            'category_id' => 'nullable|numeric|exists:categories,id',
            'unit' => 'nullable|numeric',
            'unit_id' => 'nullable|numeric|exists:units,id',
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'logo_path_remove' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            // This converts 'true', '1', 'on' to a real boolean true,
            // and missing/null values to false.
            'is_active' => $this->boolean('is_active'),
            'logo_path_remove' => $this->boolean('logo_path_remove'),
        ]);
    }
}
