<?php

namespace App\Http\Requests\Shops;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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
        if ($this->routeIs('shops.purchase-orders.items')) {
            return [
                'product_ids' => 'required',
                'subtotal' => 'required',
                'total' => 'required',
            ];
        }

        $purchaseOrderId = $this->route('purchase_order')?->id ?? $this->route('purchase_order');
        if (isset($purchaseOrderId)) {
            return [
                'order_date' => 'required|date',
                'expected_date' => 'nullable|date',
                'status' => 'nullable',
                'notes' => 'nullable',
            ];
        }

        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'product_ids' => 'required',
            'subtotal' => 'required',
            'total' => 'required',
        ];
    }
}
