<?php

namespace App\Http\Services\Shops;

use App\Http\Repositories\Shops\StaffRepository;
use App\Http\Services\BaseService;

class StaffService extends BaseService
{
    public function __construct()
    {
        $this->repository = new StaffRepository;
        $this->service = new ShopStaffService;
    }

    public function all(array $params, int $shopId)
    {
        return $this->repository->all($params, $shopId);
    }

    public function create(array $params = [])
    {
        $staffId = $params['staff_id'] ?? null;
        if (! isset($staffId)) {
            $staffParams = [
                'first_name' => $params['first_name'],
                'middle_name' => $params['middle_name'] ?? null,
                'last_name' => $params['last_name'] ?? null,
                'profile_path' => $params['profile_path'] ?? null,
                'email' => $params['email'] ?? null,
                'phone' => $params['phone'] ?? null,
                'mobile' => $params['mobile'] ?? null,
                'address' => $params['address'] ?? null,
                'profile_path_remove' => $params['profile_path_remove'] ?? null,
                'is_active' => true,
            ];
            $staffResult = $this->repository->create($staffParams)->getData(true);
            if (isset($staffResult['errors'])) {
                return $this->repository->error('Failed to create staff', $staffResult['errors'], $this->repository->internalServerError);
            }
            $staffId = $staffResult['data']['id'];
        }
        $shopStaffParams = [
            'shop_ids' => $params['shop_ids'],
            'staff_id' => $staffId,
            'employment_status' => $params['employment_status'],
            'hire_date' => $params['hire_date'],
            'is_active' => $params['is_active'] ?? null,
        ];

        return $this->service->create($shopStaffParams);
    }

    public function update(int $id, array $params = [])
    {
        $staffParams = [
            'first_name' => $params['first_name'],
            'middle_name' => $params['middle_name'] ?? null,
            'last_name' => $params['last_name'],
            'profile_path' => $params['profile_path'] ?? null,
            'email' => $params['email'] ?? null,
            'phone' => $params['phone'] ?? null,
            'mobile' => $params['mobile'] ?? null,
            'address' => $params['address'] ?? null,
            'profile_path_remove' => $params['profile_path_remove'] ?? null,
        ];
        $staffParams = array_filter($staffParams, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
        $staffResult = $this->repository->update($id, $staffParams)->getData(true);
        if (isset($staffResult['errors'])) {
            return $this->repository->error('Failed to update staff', $staffResult['errors'], $this->repository->internalServerError);
        }
        $shopStaffParams = [
            'shop_id' => $params['shop_ids'][0],
            'staff_id' => $staffResult['data']['id'],
            'employment_status' => $params['employment_status'],
            'hire_date' => $params['hire_date'],
            'is_active' => $params['is_active'] ?? null,
        ];

        return $this->service->update($id, $shopStaffParams);
    }

    public function delete(int $id, int $shopId)
    {
        $result = $this->service->delete($id, $shopId);
        $staffShops = $this->service->findManyByStaffId($id);
        if (count($staffShops) === 0) {
            return $this->repository->delete($id, $shopId);
        }

        return $result;
    }

    public function dropdown(int $shopId, bool $isShowAll = false, bool $isShowActiveOnly = false, bool $isShowInactiveOnly = false)
    {
        return $this->repository->dropdown($shopId, $isShowAll, $isShowActiveOnly, $isShowInactiveOnly);
    }
}
