<?php

namespace App\Http\Services\Shops;

use App\Http\Repositories\Shops\ShopStaffRepository;
use App\Http\Services\BaseService;

class ShopStaffService extends BaseService
{
    public function __construct()
    {
        $this->repository = new ShopStaffRepository;
    }

    public function all(array $params = [])
    {
        return $this->repository->all($params);
    }

    public function findManyByStaffId(int $id)
    {
        return $this->repository->findManyByStaffId($id);
    }

    public function findOneByStaffAndShopId(int $id, int $shopId)
    {
        return $this->repository->findOneByStaffAndShopId($id, $shopId);
    }

    public function create(array $params = [])
    {
        return $this->repository->create($params);
    }

    public function update(int $id, array $params = [])
    {
        return $this->repository->update($id, $params);
    }

    public function delete(int $id, int $shopId)
    {
        return $this->repository->delete($id, $shopId);
    }

    public function dropdown(bool $isShowAll = false, bool $isShowActiveOnly = false, bool $isShowInactiveOnly = false)
    {
        return $this->repository->dropdown($isShowAll, $isShowActiveOnly, $isShowInactiveOnly);
    }
}
