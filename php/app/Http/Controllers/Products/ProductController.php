<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductRequest;
use App\Http\Services\Products\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->service = new ProductService;
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
        $products = $this->service->all($params);
        $dropdowns = $this->service->dropdowns();

        return view('pages.products.products.index', compact('products', 'dropdowns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.index', array_filter(request()->query(), function ($value) {
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
            return redirect()->route('products.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }
}
