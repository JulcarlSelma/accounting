<?php

namespace App\Http\Repositories\Suppliers;

use App\Http\Repositories\BaseRepository;
use App\Models\Suppliers\Pricing;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PricingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Pricing;
    }

    public function all(?int $supplierId = null, array $params = [])
    {
        try {
            $query = $this->model->with([
                'product.brand',
                'product.category',
                'product.unitR',
            ])->where('supplier_id', $supplierId);

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
        $productParam = isset($params['product_id']) ? $params['product_id'] : null;
        $brandParam = isset($params['brand_id']) ? $params['brand_id'] : null;
        $categoryParam = isset($params['category_id']) ? $params['category_id'] : null;
        $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

        if ($nameParam) {
            $query->whereHas('product', function (Builder $productQuery) use ($nameParam) {
                $productQuery->whereLike('name', '%'.$nameParam.'%');
            });
        }

        if ($productParam) {
            $query->where('product_id', '%'.$productParam.'%');
        }

        if ($brandParam && $brandParam !== 'All') {
            $query->whereHas('product.brand', function ($brandQuery) use ($brandParam) {
                $brandQuery->where('id', $brandParam);
            });
        }

        if ($categoryParam && $categoryParam !== 'All') {
            $query->whereHas('product.category', function ($categoryQuery) use ($categoryParam) {
                $categoryQuery->where('id', $categoryParam);
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
            if ($params['is_active']) {
                $this->model
                    ->where('product_id', $params['product_id'])
                    ->update(['is_active' => false]);
            }
            $price = $this->model->create($params);

            DB::commit();

            return $this->success($price, 'Price created successfully!');
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
            $price = $this->model->find($id);

            DB::beginTransaction();
            if ($params['is_active']) {
                $this->model
                    ->where('product_id', $params['product_id'])
                    ->where('supplier_id', $params['supplier_id'])
                    ->update(['is_active' => false]);
            }

            if (! isset($price)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            $price->update($params);
            $newPrice = $this->model->find($id);
            DB::commit();

            return $this->success($newPrice, 'Price updated successfully!');
        } catch (Exception $e) {
            DB::rollback();
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
            $price = $this->model->find($id);

            if (! isset($price)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $price->delete();
            DB::commit();

            return $this->success([], 'Price deleted successfully!');
        } catch (Exception $e) {
            DB::rollback();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }
}
