<?php

namespace App\Http\Services\Suppliers;

use App\Http\Repositories\Suppliers\PricingRepository;
use App\Http\Services\BaseService;
use App\Http\Services\Products\BrandService;
use App\Http\Services\Products\CategoryService;

class PricingService extends BaseService
{
    public function __construct()
    {
        $this->repository = new PricingRepository;
        $this->services = [
            'brand' => new BrandService,
            'category' => new CategoryService,
            'suppliersProduct' => new SuppliersProductService,
        ];
    }

    public function all(int $supplierId, array $params = [])
    {
        return $this->repository->all($supplierId, $params);
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

    public function dropdowns(int $supplierId)
    {
        return [
            'brands' => $this->services['brand']->dropdown(false, true, false),
            'categories' => $this->services['category']->dropdown(false, true, false),
            'products' => $this->services['suppliersProduct']->all($supplierId),
        ];
    }
}
