<?php

namespace App\Http\Repositories\Products;

use App\Helper\FileHelper;
use App\Http\Repositories\BaseRepository;
use App\Models\Products\Brand;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class BrandRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Brand;
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

    private function filters(Builder $query, array $params = [])
    {
        if (empty($params)) {
            return $query;
        }

        $nameParam = isset($params['name']) ? $params['name'] : null;
        $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

        if ($nameParam) {
            $query->whereLike('name', '%'.$nameParam.'%');
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
            $logoFile = isset($params['logo_path']) ? $params['logo_path'] : null;
            unset($params['logo_path']);
            unset($params['logo_path_remove']);
            $brand = $this->model->create($params);

            if ($logoFile instanceof UploadedFile) {
                $logoPath = $this->helper->uploadFile($logoFile, config('const.product_logo_path').'brands/'.$brand->id);
                $brand->update(['logo_path' => $logoPath]);
            }

            DB::commit();

            return $this->success($brand, 'Brand created successfully!');
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
            $brand = $this->model->find($id);

            if (! isset($brand)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $logoFile = isset($params['logo_path']) ? $params['logo_path'] : null;
            $logoPathRemove = $params['logo_path_remove'];

            if ($logoPathRemove && ! $logoFile) {
                $params['logo_path'] = null;
                if ($brand->logo_path) {
                    $this->helper->deleteFile($brand->getRawOriginal('logo_path'));
                }
            }

            if ($logoFile instanceof UploadedFile) {
                $params['logo_path'] = $this->helper->uploadFile($params['logo_path'], config('const.product_logo_path').'brands/'.$id);
                if ($brand->logo_path) {
                    $this->helper->deleteFile($brand->getRawOriginal('logo_path'));
                }
            }
            $brand->update($params);
            $newBrand = $this->model->find($id);
            DB::commit();

            return $this->success($newBrand, 'Brand updated successfully!');
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
            $brand = $this->model->find($id);

            if (! isset($brand)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $brand->delete();
            DB::commit();

            return $this->success([], 'Brand deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown(bool $isShowAll = false, bool $isShowActiveOnly = false, bool $isShowInactiveOnly = false)
    {
        $query = $this->model->query();
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
