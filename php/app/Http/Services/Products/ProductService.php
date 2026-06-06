<?php

namespace App\Http\Services\Products;

use App\Http\Repositories\Products\ProductRepository;
use App\Http\Services\BaseService;

class ProductService extends BaseService
{
    public function __construct()
    {
        $this->repository = new ProductRepository;
        $this->services = [
            'brand' => new BrandService,
            'category' => new CategoryService,
            'unit' => new UnitService,
        ];
    }

    public function all(array $params = [])
    {
        return $this->repository->all($params);
    }

    public function create(array $params = [])
    {
        return $this->repository->create($params);
    }

    public function update(int $id, array $params = [])
    {
        return $this->repository->update($id, $params);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function dropdown(bool $isShowAll = false, bool $isShowActiveOnly = false, bool $isShowInactiveOnly = false)
    {
        return $this->repository->dropdown($isShowAll, $isShowActiveOnly, $isShowInactiveOnly);
    }

    public function dropdowns()
    {
        return [
            'brands' => $this->services['brand']->dropdown(false, true, false),
            'categories' => $this->services['category']->dropdown(false, true, false),
            'units' => $this->services['unit']->dropdown(false, true, false),
        ];
    }
}
