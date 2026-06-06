<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Suppliers\PricingRequest;
use App\Http\Services\Suppliers\PricingService;
use App\Models\Suppliers\Supplier;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __construct()
    {
        $this->service = new PricingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Supplier $supplier, Request $request)
    {
        $params = $request->all();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
        $prices = $this->service->all($supplier->id, $params);
        $dropdowns = $this->service->dropdowns($supplier->id);

        return view('pages.suppliers.manage.prices.index', compact('supplier', 'prices', 'dropdowns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Supplier $supplier, PricingRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.pricing.index', $supplier->id)->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.pricing.index', array_merge(
            ['supplier' => $supplier->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Supplier $supplier, PricingRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.pricing.index', $supplier->id)->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.pricing.index', array_merge(
            ['supplier' => $supplier],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier, int $id)
    {
        $result = $this->service->delete($id)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.pricing.index', $supplier->id)->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.pricing.index', array_merge(
            ['supplier' => $supplier->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }
}
