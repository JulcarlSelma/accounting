<?php

namespace App\Http\Services\Products;

use App\Http\Repositories\Products\CategoryRepository;
use App\Http\Services\BaseService;

class CategoryService extends BaseService
{
    public function __construct()
    {
        $this->repository = new CategoryRepository;
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
}
