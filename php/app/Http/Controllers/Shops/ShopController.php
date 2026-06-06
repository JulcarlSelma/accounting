<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\ShopRequest;
use App\Http\Services\Shops\ShopService;
use App\Models\Shops\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->service = new ShopService;
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
        $shops = $this->service->all($params);

        return view('pages.shops.index', compact('shops'));
    }

    public function show(Shop $shop)
    {
        return view('pages.shops.manage.dashboard', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShopRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShopRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.index', array_filter(request()->query(), function ($value) {
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
            return redirect()->route('shops.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }
}
