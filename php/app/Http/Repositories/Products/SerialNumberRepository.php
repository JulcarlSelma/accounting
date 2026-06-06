<?php

namespace App\Http\Repositories\Products;

use App\Http\Repositories\BaseRepository;
use App\Models\Products\SerialNumbers;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SerialNumberRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new SerialNumbers;
    }

    public function all(array $params = [])
    {
        try {
            $query = $this->model->with([
                'product',
            ]);

            $query = $this->filters($query, $params);

            return $query->paginate(10)->withQueryString();
        } catch (Exception $e) {
            Log::error(get_class().': '.__FUNCTION__.'function: '.$e);

            return null;
        }
    }

    public function filters(Builder $query, array $params = [])
    {
        if (empty($params)) {
            return $query;
        }

        $serialNumberParam = isset($param['serial_number']) ? $param['serial_number'] : null;
        $skuParam = isset($param['sku']) ? $param['sku'] : null;
        $statusParam = isset($param['status']) ? $param['status'] : null;

        if ($serialNumberParam) {
            $query->where('serial_number', $serialNumberParam);
        }

        if ($skuParam) {
            $query->where('sku', $skuParam);
        }

        if ($statusParam && $statusParam !== 'All') {
            $query->where('status', $statusParam);
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
            $serialNumber = $this->model->create($params);
            DB::commit();

            return $this->success($serialNumber, 'Serial number was added to this product successfully!');
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
            $serialNumber = $this->model->find($id);

            if (! isset($serialNumber)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $serialNumber->update($params);
            $newSerialNumber = $this->model->find($id);
            DB::commit();

            return $this->success($newSerialNumber, 'Details updated successfully!');
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
            $serialNumber = $this->model->find($id);

            if (! isset($serialNumber)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $serialNumber->delete();
            DB::commit();

            return $this->success([], 'Details deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown(array $params = [])
    {
        $query = $this->model->query();
        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->get();
    }
}
