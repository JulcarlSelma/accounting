<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\CategoryRequest;
use App\Http\Services\Products\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->service = new CategoryService;
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
        $categories = $this->service->all($params);

        return view('pages.products.categories.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $params = $request->validated();
        $result = $this->service->create($params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.categories.index')->withErrors($result['errors']);
        }

        session()->flash('success', $result['message']);

        return redirect()->route('products.categories.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, int $id)
    {
        $params = $request->validated();
        $result = $this->service->update($id, $params)->getData(true);
        if (isset($result['errors']) && ! empty($result['errors'])) {
            return redirect()->route('products.categories.index')->withErrors($result['errors']);
        }
        session()->flash('success', $result['message']);

        return redirect()->route('products.categories.index', array_filter(request()->query(), function ($value) {
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
            return redirect()->route('products.categories.index')->withErrors($result['errors']);
        }
        session()->flash('success', $result['message']);

        return redirect()->route('products.categories.index', array_filter(request()->query(), function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        }));
    }
}
