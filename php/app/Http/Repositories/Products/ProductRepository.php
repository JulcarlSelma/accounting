<?php

namespace App\Http\Repositories\Products;

use App\Helper\FileHelper;
use App\Http\Repositories\BaseRepository;
use App\Models\Products\Product;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Product;
        $this->helper = new FileHelper;
    }

    public function all(array $params = [])
    {
        try {
            $query = $this->model->with([
                'brand',
                'category',
                'unitR',
            ]);

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
        $brandParam = isset($params['brand_id']) ? $params['brand_id'] : null;
        $categoryParam = isset($params['category_id']) ? $params['category_id'] : null;
        $unitParam = isset($params['unit_id']) ? $params['unit_id'] : null;
        $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

        if ($nameParam) {
            $query->whereLike('name', '%'.$nameParam.'%');
        }

        if ($brandParam && $brandParam !== 'All') {
            $query->whereHas('brand', function ($brandQuery) use ($brandParam) {
                $brandQuery->where('id', $brandParam);
            });
        }

        if ($categoryParam && $categoryParam !== 'All') {
            $query->whereHas('category', function ($categoryQuery) use ($categoryParam) {
                $categoryQuery->where('id', $categoryParam);
            });
        }

        if ($unitParam && $unitParam !== 'All') {
            $query->whereHas('unitR', function ($unitQuery) use ($unitParam) {
                $unitQuery->where('id', $unitParam);
            });
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
            $product = $this->model->create($params);

            if ($logoFile instanceof UploadedFile) {
                $logoPath = $this->helper->uploadFile($logoFile, config('const.product_logo_path').'product/'.$product->id);
                $product->update(['logo_path' => $logoPath]);
            }

            DB::commit();

            return $this->success($product, 'Product created successfully!');
        } catch (Exception $e) {
            DB::rollback();
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
            $product = $this->model->find($id);

            if (! isset($product)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $logoFile = isset($params['logo_path']) ? $params['logo_path'] : null;
            $logoPathRemove = $params['logo_path_remove'];

            if ($logoPathRemove && ! $logoFile) {
                $params['logo_path'] = null;
                if ($product->logo_path) {
                    $this->helper->deleteFile($product->getRawOriginal('logo_path'));
                }
            }

            if ($logoFile instanceof UploadedFile) {
                $params['logo_path'] = $this->helper->uploadFile($params['logo_path'], config('const.product_logo_path').'product/'.$id);
                if ($product->logo_path) {
                    $this->helper->deleteFile($product->getRawOriginal('logo_path'));
                }
            }
            $product->update($params);
            $newProduct = $this->model->find($id);
            DB::commit();

            return $this->success($newProduct, 'Product updated successfully!');
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
            $product = $this->model->find($id);

            if (! isset($product)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $product->delete();
            DB::commit();

            return $this->success([], 'Product deleted successfully!');
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
