<?php

namespace App\Http\Requests\Shops;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
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
        if ($this->filled('staff_id')) {
            return [
                'staff_id' => 'required|exists:staffs,id',
                'shop_ids' => 'required',
                'is_active' => 'boolean',
                'employment_status' => [
                    'nullable',
                    Rule::in(config('const.employment_status')),
                ],
                'hire_date' => 'nullable',
            ];
        }

        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'profile_path' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_path_remove' => 'boolean',
            'is_active' => 'boolean',
            'shop_ids' => 'required',
            'employment_status' => [
                'nullable',
                Rule::in(config('const.employment_status')),
            ],
            'hire_date' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            // This converts 'true', '1', 'on' to a real boolean true,
            // and missing/null values to false.
            'is_active' => $this->boolean('is_active'),
            'profile_path_remove' => $this->boolean('profile_path_remove'),
        ]);
    }
}
