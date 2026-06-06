<?php

namespace App\Http\Repositories\Products;

use App\Http\Repositories\BaseRepository;
use App\Models\Products\Category;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CategoryRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Category;
    }

    public function all(array $params = []): ?LengthAwarePaginator
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
            $category = $this->model->create($params);
            DB::commit();

            return $this->success($category, 'Category created successfully!');
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
            $category = $this->model->find($id);

            if (! isset($category)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $category->update($params);

            $newCategory = $this->model->find($id);
            DB::commit();

            return $this->success($newCategory, 'Category updated successfully!');
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
            $category = $this->model->find($id);

            if (! isset($category)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $category->delete();
            DB::commit();

            return $this->success([], 'Category deleted successfully!');
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
