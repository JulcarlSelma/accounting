<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\StaffRequest;
use App\Http\Services\Shops\StaffService;
use App\Models\Shops\Shop;
use App\Models\Shops\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->service = new StaffService;
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
        $staffs = $this->service->all($params, $shop->id);
        $staffDropdown = $this->service->dropdown($shop->id, false, true, false);

        return view('pages.shops.manage.staffs.index', compact('shop', 'staffs', 'staffDropdown'));
    }

    public function show(Shop $shop, Staff $staff)
    {
        return view('pages.staffs.manage.dashboard', compact('shop', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Shop $shop, StaffRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.staffs.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.staffs.index', array_merge(
            ['shop' => $shop->id], // required route param
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Shop $shop, StaffRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.staffs.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.staffs.index', array_merge(
            ['shop' => $shop->id], // required route param
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, int $id)
    {
        $result = $this->service->delete($id, $shop->id)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('shops.staffs.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('shops.staffs.index', array_merge(
            ['shop' => $shop->id], // required route param
            array_filter(request()->query(), function ($value) {
                return $value !== null && $value !== '' && $value !== 'null';
            })
        ));
    }
}
