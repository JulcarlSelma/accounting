<?php

namespace App\Http\Repositories\Shops;

use App\Helper\FileHelper;
use App\Http\Repositories\BaseRepository;
use App\Models\Shops\Staff;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class StaffRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Staff;
        $this->helper = new FileHelper;
    }

    public function all(array $params, int $shopId)
    {
        try {
            $query = $this->model->with([
                'shop' => function ($q) use ($shopId, $params) {
                    $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

                    $q->where('shop_id', $shopId);

                    if ($isActiveParam !== null && $isActiveParam !== 'All') {
                        $isActive = filter_var($isActiveParam, FILTER_VALIDATE_BOOLEAN);
                        $q->where('is_active', $isActive);
                    }
                },
            ])->whereHas('shop', function ($shopQuery) use ($shopId, $params) {
                $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;
                $shopQuery->where('shop_id', $shopId);

                if ($isActiveParam !== null && $isActiveParam !== 'All') {
                    $isActive = filter_var($isActiveParam, FILTER_VALIDATE_BOOLEAN);
                    $shopQuery->where('is_active', $isActive);
                }
            });

            $query = $this->filters($query, $params);

            $data = $query->paginate(10)->withQueryString();

            return $data;
        } catch (Exception $e) {
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return null;
        }
    }

    private function filters(Builder $query, array $params = [])
    {
        if (empty($params)) {
            return $query;
        }

        $nameParam = isset($params['name']) ? $params['name'] : null;
        $emailParam = isset($params['email']) ? $params['email'] : null;
        $phoneParam = isset($params['phone']) ? $params['phone'] : null;
        $mobileParam = isset($params['mobile']) ? $params['mobile'] : null;
        $addressParam = isset($params['address']) ? $params['address'] : null;

        if ($nameParam) {
            $query->whereLike('first_name', '%'.$nameParam.'%')
                ->orWhereLike('middle_name', '%'.$nameParam.'%')
                ->orWhereLike('last_name', '%'.$nameParam.'%');
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

        return $query;
    }

    public function create(array $params = [])
    {
        if (empty($params)) {
            return $this->error('Empty parameters', [], $this->badRequest);
        }

        try {
            DB::beginTransaction();
            $logoFile = isset($params['profile_path']) ? $params['profile_path'] : null;
            unset($params['profile_path']);
            unset($params['profile_path_remove']);
            $staff = $this->model->create($params);

            if ($logoFile instanceof UploadedFile) {
                $logoPath = $this->helper->uploadFile($logoFile, config('const.shops_logo_path').'staff/'.$staff->id);
                $staff->update(['profile_path' => $logoPath]);
            }

            DB::commit();

            return $this->success($staff, 'Staff created successfully!');
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
            $staff = $this->model->find($id);

            if (! isset($staff)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $logoFile = isset($params['profile_path']) ? $params['profile_path'] : null;
            $logoPathRemove = $params['profile_path_remove'];

            if ($logoPathRemove && ! $logoFile) {
                $params['profile_path'] = null;
                if ($staff->profile_path) {
                    $this->helper->deleteFile($staff->getRawOriginal('profile_path'));
                }
            }

            if ($logoFile instanceof UploadedFile) {
                $params['profile_path'] = $this->helper->uploadFile($params['profile_path'], config('const.shops_logo_path').'staff/'.$id);
                if ($staff->profile_path) {
                    $this->helper->deleteFile($staff->getRawOriginal('profile_path'));
                }
            }
            $staff->update($params);
            $newStaff = $this->model->find($id);
            DB::commit();

            return $this->success($newStaff, 'Staff updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function delete(int $id)
    {
        if (! $id) {
            return $this->error('ID should be present', [], $this->badRequest);
        }

        try {
            $staff = $this->model->find($id);

            if (! isset($staff)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $staff->delete();
            DB::commit();

            return $this->success([], 'Staff deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown(int $shopId, bool $isShowAll = false, bool $isShowActiveOnly = false, bool $isShowInactiveOnly = false)
    {
        $query = $this->model->with([
            'shop' => function ($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            },
        ])->whereHas('shop', function ($shopQuery) use ($shopId) {
            $shopQuery->where('shop_id', '!=', $shopId);
        });

        if ($isShowAll) {
            return $query->get();
        }

        if ($isShowActiveOnly) {
            $query = $query->where('is_active', true);
        }

        if ($isShowInactiveOnly) {
            $query = $query->where('is_active', false);
        }

        return $query->get();
    }
}
