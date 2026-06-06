<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\SerialNumberRequest;
use App\Http\Services\Products\SerialNumberService;
use App\Models\Products\Product;
use Illuminate\Http\Request;

class SerialNumberController extends Controller
{
    public function __construct()
    {
        $this->service = new SerialNumberService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Product $product, Request $request)
    {
        $params = $request->all();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
        $serialNumbers = $this->service->all($params);

        return view('pages.products.serial_numbers.index', compact('product', 'serialNumbers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product, SerialNumberRequest $request)
    {
        $params = $request->validated();
        $params['product_id'] = $product->id;
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.serial_number.index', [
                'product' => $product->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.serial_number.index', array_merge(
            ['product' => $product->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Product $product, SerialNumberRequest $request, int $id)
    {
        $params = $request->validated();
        $params['product_id'] = $product->id;
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.serial_number.index', [
                'product' => $product->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.serial_number.index', array_merge(
            ['product' => $product->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, string $id)
    {
        $result = $this->service->delete($id)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.serial_number.index', [
                'product' => $product->id,
            ])->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.serial_number.index', array_merge(
            ['product' => $product->id],
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            }
            )));
    }
}
