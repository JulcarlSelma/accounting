<?php

namespace App\Http\Repositories\Shops;

use App\Http\Repositories\BaseRepository;
use App\Models\Shops\PurchaseOrderItem;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PurchaseOrderItemRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PurchaseOrderItem;
    }

    public function all(array $params = [])
    {
        try {
            $query = $this->model->with([
                'purchaseOrder',
                'product',
            ]);

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

        $purchaseOrderId = isset($params['purchase_order_id']) ? $params['purchase_order_id'] : null;

        if ($purchaseOrderId) {
            $query->where('purchase_order_id', $purchaseOrderId);
        }

        return $query;
    }

    public function insert(int $purchaseOrderId, array $params = [])
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
            $purchaseOrderItems = $this->all([
                'purchase_order_id' => $purchaseOrderId,
            ]);

            return $this->success($purchaseOrderItems, 'Purchase Order Items added successfully!');
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
            $purchaseOrderItem = $this->model->find($id);

            if (! isset($purchaseOrderItem)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $purchaseOrderItem->update($params);

            $newPurchaseOrderItem = $this->model->find($id);
            DB::commit();

            return $this->success($newPurchaseOrderItem, 'Purchase Order Item updated successfully!');
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
}
