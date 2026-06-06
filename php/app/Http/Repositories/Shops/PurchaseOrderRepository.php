<?php

namespace App\Http\Repositories\Shops;

use App\Http\Repositories\BaseRepository;
use App\Models\Shops\PurchaseOrder;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PurchaseOrder;
    }

    public function all(array $params = [])
    {
        try {
            $query = $this->model->with([
                'orders.product',
                'supplier',
            ]);

            $query = $this->filters($query, $params);

            $data = $query->paginate(10)->withQueryString();

            return $data;
        } catch (Exception $e) {
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return null;
        }
    }

    public function find(int $id)
    {
        try {
            return $this->model->find($id);
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

        $poNumberParam = isset($params['po_number']) ? $params['po_number'] : null;

        if ($poNumberParam) {
            $query->where('po_number', $poNumberParam);
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
            $purchaseOrder = $this->model->create($params);

            if ($purchaseOrder) {
                $purchaseOrder->update(['po_number' => str_pad($purchaseOrder->id, 4, '0', STR_PAD_LEFT)]);
            }

            DB::commit();

            return $this->success($purchaseOrder, 'Purchase Order created successfully!');
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
            $purchaseOrder = $this->model->find($id);

            if (! isset($purchaseOrder)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $purchaseOrder->update($params);

            $newPurchaseOrder = $this->model->find($id);
            DB::commit();

            return $this->success($newPurchaseOrder, 'Purchase Order updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function updateTotal(int $id, array $params = [])
    {
        if (! $id) {
            return $this->error('ID should be present', [], $this->badRequest);
        }

        if (empty($params)) {
            return $this->error('Empty parameters', [], $this->badRequest);
        }

        try {
            $purchaseOrder = $this->model->find($id);

            if (! isset($purchaseOrder)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $params['total'] = $params['total'] + $purchaseOrder->total;
            $params['subtotal'] = $params['total'];
            $purchaseOrder->update($params);

            $newPurchaseOrder = $this->model->find($id);
            DB::commit();

            return $this->success($newPurchaseOrder, 'Purchase Order updated successfully!');
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
            $purchaseOrder = $this->model->find($id);

            if (! isset($purchaseOrder)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $purchaseOrder->delete();
            DB::commit();

            return $this->success([], 'Purchase Order deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown()
    {
        return $this->model->where('status', 'Pending')->get();
    }
}
