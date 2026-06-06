<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Suppliers\SupplierRequest;
use App\Http\Services\Suppliers\SupplierService;
use App\Models\Suppliers\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->service = new SupplierService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
        $suppliers = $this->service->all($params);

        return view('pages.suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        return view('pages.suppliers.manage.dashboard', compact('supplier'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $result = $this->service->delete($id)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('suppliers.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('suppliers.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }
}
