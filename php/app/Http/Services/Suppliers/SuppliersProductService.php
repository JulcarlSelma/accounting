<?php

namespace App\Http\Services\Suppliers;

use App\Http\Repositories\Suppliers\SuppliersProductRepository;
use App\Http\Services\BaseService;
use App\Http\Services\Products\BrandService;
use App\Http\Services\Products\CategoryService;

class SuppliersProductService extends BaseService
{
    public function __construct()
    {
        $this->repository = new SuppliersProductRepository;
        $this->services = [
            'brand' => new BrandService,
            'category' => new CategoryService,
        ];
    }

    public function all(int $supplierId, array $params = [])
    {
        return $this->repository->all($supplierId, $params);
    }

    public function insert(int $supplierId, array $params = [])
    {
        $insertParams = array_map(function ($id) use ($supplierId) {
            return [
                'product_id' => $id,
                'supplier_id' => $supplierId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $params['product_ids']);

        return $this->repository->insert($supplierId, $insertParams);
    }

    public function delete(int $supplierProductId)
    {
        return $this->repository->delete($supplierProductId);
    }

    public function dropdown(int $supplierId)
    {
        return $this->repository->dropdown($supplierId);
    }

    public function dropdowns()
    {
        return [
            'brands' => $this->services['brand']->dropdown(false, true, false),
            'categories' => $this->services['category']->dropdown(false, true, false),
        ];
    }
}
