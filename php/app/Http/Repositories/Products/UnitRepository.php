<?php

namespace App\Http\Repositories\Products;

use App\Http\Repositories\BaseRepository;
use App\Models\Products\Unit;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class UnitRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Unit;
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

        $nameParams = isset($params['name']) ? $params['name'] : null;
        $abbreviationParams = isset($params['abbreviation']) ? $params['abbreviation'] : null;
        $isActiveParams = isset($params['is_active']) ? $params['is_active'] : null;

        if ($nameParams) {
            $query->whereLike('name', '%'.$nameParams.'%');
        }

        if ($abbreviationParams && $abbreviationParams !== 'All') {
            $query->where('abbreviation', $abbreviationParams);
        }

        if ($isActiveParams && $isActiveParams !== 'All') {
            $isActive = filter_var($isActiveParams, FILTER_VALIDATE_BOOLEAN);
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
            $unit = $this->model->create($params);
            DB::commit();

            return $this->success($unit, 'Unit created successfully!');
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
            $unit = $this->model->find($id);

            if (! isset($unit)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $unit->update($params);

            $newUnit = $this->model->find($id);
            DB::commit();

            return $this->success($newUnit, 'Unit updated successfully!');
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
            $unit = $this->model->find($id);

            if (! isset($unit)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $unit->delete();
            DB::commit();

            return $this->success([], 'Unit deleted successfully!');
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
