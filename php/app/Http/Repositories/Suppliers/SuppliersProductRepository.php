<?php

namespace App\Http\Repositories\Suppliers;

use App\Http\Repositories\BaseRepository;
use App\Models\Products\Product;
use App\Models\Suppliers\SuppliersProduct;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SuppliersProductRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new SuppliersProduct;
        $this->models = [
            'product' => new Product,
        ];
    }

    public function all(int $supplierId, array $params = [])
    {
        try {
            $query = $this->model->with([
                'products.brand',
                'products.category',
                'products.unitR',
            ])->where('supplier_id', $supplierId);

            $query = $this->filters($query, $params);

            $data = $query->get();

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

        if ($nameParam) {
            $query->whereHas('products', function ($productQuery) use ($nameParam) {
                $productQuery->whereLike('name', '%'.$nameParam.'%');
            });
        }

        if ($brandParam && $brandParam !== 'All') {
            $query->whereHas('products.brand', function ($brandQuery) use ($brandParam) {
                $brandQuery->where('id', $brandParam);
            });
        }

        if ($categoryParam && $categoryParam !== 'All') {
            $query->whereHas('products.category', function ($categoryQuery) use ($categoryParam) {
                $categoryQuery->where('id', $categoryParam);
            });
        }

        return $query;
    }

    public function insert(int $supplierId, array $params = [])
    {
        if (empty($params)) {
            return $this->error('Empty parameters', [], $this->badRequest);
        }

        try {
            DB::beginTransaction();
            $result = $this->model->insert($params);
            if (! $result) {
                return $this->error('Failed to add products', [], $this->internalServerError);
            }
            DB::commit();
            $products = $this->all($supplierId);

            return $this->success($products, 'Products was added succesfully');
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
            $suppliersProduct = $this->model->find($id);

            if (! isset($suppliersProduct)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $suppliersProduct->delete();
            DB::commit();

            return $this->success([], 'Product was removed successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown(int $supplierId)
    {
        return $this->models['product']
            ->where('is_active', true)
            ->whereDoesntHave('suppliersProducts', function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->get();
    }
}
