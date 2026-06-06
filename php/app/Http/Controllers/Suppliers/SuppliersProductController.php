<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Suppliers\SupplierProductRequest;
use App\Http\Services\Suppliers\SuppliersProductService;
use App\Models\Suppliers\Supplier;
use Illuminate\Http\Request;

class SuppliersProductController extends Controller
{
    public function __construct()
    {
        $this->service = new SuppliersProductService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Supplier $supplier, Request $request)
    {
        $params = $request->all();
        $products = $this->service->dropdown($supplier->id);
        $supplierProducts = $this->service->all($supplier->id, $params);
        $dropdowns = $this->service->dropdowns();

        return view('pages.suppliers.manage.products.index', compact('supplier', 'products', 'supplierProducts', 'dropdowns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Supplier $supplier, SupplierProductRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->insert($supplier->id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.product.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.product.index', array_merge(
            ['supplier' => $supplier],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier, string $id)
    {
        $result = $this->service->delete($id)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.product.index', $supplier->id)->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.product.index', array_merge(
            ['supplier' => $supplier->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }
}
