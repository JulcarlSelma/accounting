<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\PurchaseOrderRequest;
use App\Http\Services\Shops\PurchaseOrderService;
use App\Models\Shops\PurchaseOrder;
use App\Models\Shops\Shop;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->service = new PurchaseOrderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop, Request $request)
    {
        $params = $request->all();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
        $purchaseOrders = $this->service->all($params);
        $dropdowns = $this->service->dropdowns([
            'is_pricing' => 1,
        ]);

        return view('pages.shops.manage.purchase_orders.index', compact('shop', 'purchaseOrders', 'dropdowns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Shop $shop, PurchaseOrderRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.purchase-orders.index', [
                'shop' => $shop->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.purchase-orders.index', array_merge(
            ['shop' => $shop->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop, PurchaseOrder $purchase_order)
    {
        $purchase_order->load([
            'orders.product',
        ]);
        $dropdowns = $this->service->dropdowns([
            'is_pricing' => 1,
            'is_no_selected_products' => true,
            'supplier_id' => $purchase_order->supplier_id,
        ]);

        return view('pages.shops.manage.purchase_orders.manage.purchase_order_items.index', compact('shop', 'purchase_order', 'dropdowns'));
    }

    /**
     * Display the specified resource.
     */
    public function items(Shop $shop, PurchaseOrder $purchase_order, PurchaseOrderRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->addItems($purchase_order->id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.purchase-orders.show', [
                'shop' => $shop->id,
                'purchase_order' => $purchase_order->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.purchase-orders.show', array_merge(
            [
                'shop' => $shop->id,
                'purchase_order' => $purchase_order->id,
            ],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Shop $shop, PurchaseOrderRequest $request, PurchaseOrder $purchase_order)
    {
        $params = $request->validated();
        $result = $this->service->update($purchase_order->id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.purchase-orders.index', [
                'shop' => $shop->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.purchase-orders.index', array_merge(
            ['shop' => $shop->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, string $id)
    {
        //
    }
}
