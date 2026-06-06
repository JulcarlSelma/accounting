<?php

namespace App\Http\Repositories\Shops;

use App\Helper\FileHelper;
use App\Http\Repositories\BaseRepository;
use App\Models\Shops\ShopStaff;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ShopStaffRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ShopStaff;
        $this->helper = new FileHelper;
    }

    public function all(array $params = [])
    {
        try {
            $query = $this->model->query();

            $query = $this->filters($query, $params);

            $data = $query->paginate(10)->withQueryString();

            return $data;
        } catch (Exception $e) {
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return null;
        }
    }

    public function findOneByStaffAndShopId(int $id, int $shopId)
    {
        return $this->model
            ->where('staff_id', $id)
            ->where('shop_id', $shopId)
            ->first();
    }

    public function findManyByStaffId(int $id)
    {
        return $this->model->where('staff_id', $id)->get();
    }

    private function filters(Builder $query, array $params = [])
    {
        if (empty($params)) {
            return $query;
        }

        $shopIDParam = isset($params['shop_id']) ? $params['shop_id'] : null;
        $firstNameParam = isset($params['first_name']) ? $params['first_name'] : null;
        $lastNameParam = isset($params['last_name']) ? $params['last_name'] : null;
        $emailParam = isset($params['email']) ? $params['email'] : null;
        $phoneParam = isset($params['phone']) ? $params['phone'] : null;
        $mobileParam = isset($params['mobile']) ? $params['mobile'] : null;
        $addressParam = isset($params['address']) ? $params['address'] : null;
        $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

        if ($firstNameParam) {
            $query->whereLike('first_name', '%'.$firstNameParam.'%');
        }

        if ($lastNameParam) {
            $query->whereLike('last_name', '%'.$lastNameParam.'%');
        }

        if ($emailParam) {
            $query->whereLike('email', '%'.$emailParam.'%');
        }

        if ($phoneParam) {
            $query->whereLike('phone', '%'.$phoneParam.'%');
        }

        if ($mobileParam) {
            $query->whereLike('mobile', '%'.$mobileParam.'%');
        }

        if ($addressParam) {
            $query->whereLike('address', '%'.$addressParam.'%');
        }

        if ($isActiveParam && $isActiveParam !== 'All') {
            $isActive = filter_var($isActiveParam, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        return $query;
    }

    public function create(array $params = [])
    {
        if (empty($params)) {
            return $this->error('Empty parameters', [], $this->badRequest);
        }

        try {
            DB::beginTransaction();
            $shopStaff = [];
            $message = 'Shop Staff added successfully!';
            if (isset($params['shop_ids']) && ! empty($params['shop_ids'])) {
                $shop_ids = $params['shop_ids'];
                foreach ($shop_ids as $id) {
                    $record = $this->model
                        ->withTrashed()
                        ->where('staff_id', $params['staff_id'])
                        ->where('shop_id', $id)
                        ->first();

                    $newParam = [
                        'shop_id' => $id,
                        'staff_id' => $params['staff_id'],
                        'employment_status' => $params['employment_status'],
                        'hire_date' => $params['hire_date'],
                        'is_active' => true,
                    ];

                    if ($record) {
                        if ($record->trashed()) {
                            $record->restore();
                        }
                        $record->update($newParam);
                        $message = 'Shop staff was restored and updated its data successfully!';
                    } else {
                        $this->model->create($newParam);
                    }
                }
            } elseif (isset($params['shop_id'])) {
                unset($params['shop_ids']);
                $shopStaff = $this->model->create($params);
            }
            DB::commit();

            $staffs = $this->model->where('staff_id', $params['staff_id'])->get();

            return $this->success($staffs, $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function update(int $id, array $params = [])
    {
        if (! $id) {
            return $this->error('ID should be present', [], $this->badRequest);
        }

        if (empty($params)) {
            return $this->error('Empty parameters', [], $this->badRequest);
        }

        try {
            if (isset($params['shop_id'])) {
                $shopStaff = $this->model
                    ->where('shop_id', $params['shop_id'])
                    ->where('staff_id', $id)
                    ->first();
            } else {
                $shopStaff = $this->model->where('staff_id', $id)->first();
            }

            if (! isset($shopStaff)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            // TODO::shop_ids not working perfectly
            if (isset($params['shop_ids']) && ! empty($params['shop_ids'])) {
                $shop_ids = $params['shop_ids'];
                foreach ($shop_ids as $ids) {
                    $newParam = [
                        'shop_id' => $ids,
                        'staff_id' => $params['staff_id'],
                        'employment_status' => $params['employment_status'],
                        'hire_date' => $params['hire_date'],
                        'is_active' => true,
                    ];
                    $this->model->create($newParam);
                }
            } elseif (isset($params['shop_id'])) {
                unset($params['shop_ids']);
                $shopStaff->update($params);
            }
            $newStaff = $this->model->find($id);
            DB::commit();

            return $this->success($newStaff, 'Staff updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function delete(int $id, int $shopId)
    {
        if (! $id && ! $shopId) {
            return $this->error('ID and Shop ID should be present', [], $this->badRequest);
        }

        try {
            $shopStaff = $this->model->where('staff_id', $id)
                ->where('shop_id', $shopId)
                ->first();

            if (! isset($shopStaff)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $shopStaff->delete();
            DB::commit();

            return $this->success([], 'Shop staff deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }
}
