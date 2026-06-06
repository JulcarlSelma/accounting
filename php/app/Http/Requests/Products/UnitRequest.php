<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit')?->id ?? $this->route('unit');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unitId),
            ],
            'abbreviation' => [
                'nullable',
                'string',
                'max:5',
                Rule::unique('units', 'abbreviation')->ignore($unitId),
            ],
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            // This converts 'true', '1', 'on' to a real boolean true,
            // and missing/null values to false.
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
