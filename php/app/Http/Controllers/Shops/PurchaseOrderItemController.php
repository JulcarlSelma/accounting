<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\PurchaseOrderRequest;
use App\Http\Services\Shops\PurchaseOrderService;
use App\Models\Shops\PurchaseOrder;
use App\Models\Shops\Shop;
use Illuminate\Http\Request;

class PurchaseOrderItemController extends Controller
{
    public function __construct()
    {
        $this->service = new PurchaseOrderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Shop $shop, PurchaseOrder $purchase_order, PurchaseOrderRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->addItems($purchase_order->id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.purchase-orders.show', [
                'shop' => $shop->id,
                'purchaseOrder' => $purchaseOrder->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.purchase-orders.show', array_merge(
            [
                'shop' => $shop->id,
                'purchaseOrder' => $purchaseOrder->id,
            ],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
