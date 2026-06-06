<?php

namespace App\Http\Services\Suppliers;

use App\Http\Repositories\Suppliers\SupplierRepository;
use App\Http\Services\BaseService;

class SupplierService extends BaseService
{
    public function __construct()
    {
        $this->repository = new SupplierRepository;
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

    public function dropdown(array $params = [])
    {
        return $this->repository->dropdown($params);
    }
}
