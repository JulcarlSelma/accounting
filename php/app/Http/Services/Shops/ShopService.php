<?php

namespace App\Http\Services\Shops;

use App\Http\Repositories\Shops\ShopRepository;
use App\Http\Services\BaseService;

class ShopService extends BaseService
{
    public function __construct()
    {
        $this->repository = new ShopRepository;
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

    public function dropdown()
    {
        return $this->repository->dropdown();
    }
}
