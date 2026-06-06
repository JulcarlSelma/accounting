<?php

namespace App\Http\Services\Shops;

use App\Http\Repositories\Shops\PurchaseOrderRepository;
use App\Http\Services\BaseService;
use App\Http\Services\Suppliers\SupplierService;

class PurchaseOrderService extends BaseService
{
    public function __construct()
    {
        $this->repository = new PurchaseOrderRepository;
        $this->services = [
            'po_item' => new PurchaseOrderItemService,
            'supplier' => new SupplierService,
        ];
    }

    public function all(array $params = [])
    {
        return $this->repository->all($params);
    }

    public function create(array $params = [])
    {
        $purchaseOrderParams = [
            'supplier_id' => $params['supplier_id'] ?? null,
            'order_date' => $params['order_date'] ?? null,
            'expected_date' => $params['expected_date'] ?? null,
            'status' => 'Pending',
            'subtotal' => $params['subtotal'] ?? 0,
            'tax' => $params['tax'] ?? 0,
            'total' => $params['total'] ?? 0,
            'notes' => $params['notes'] ?? '',
            'created_by' => $params['created_by'] ?? null,
        ];

        $purchaseOrder = $this->repository->create($purchaseOrderParams)->getData(true);
        if (isset($purchaseOrder['errors'])) {
            return $this->repository->error('Failed to create purchase order', $purchaseOrder['errors'], $this->repository->internalServerError);
        }
        $purchaseOrderId = $purchaseOrder['data']['id'];

        $purchaseOrderItemsParams = array_map(function ($value) use ($purchaseOrderId) {
            return [
                'purchase_order_id' => $purchaseOrderId,
                'product_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'price' => $value['price'],
                'total' => $value['quantity'] * $value['price'],
            ];
        }, $params['product_ids']);

        $purchaseOrderItems = $this->services['po_item']->insert($purchaseOrderId, $purchaseOrderItemsParams)->getData(true);
        if (isset($purchaseOrderItems['errors'])) {
            return $this->repository->error('Failed to add purchase order items', $purchaseOrderItems['errors'], $this->repository->internalServerError);
        }

        return $this->repository->success($purchaseOrder['data'], $purchaseOrder['message']);
    }

    public function addItems(int $purchaseOrderId, array $params = [])
    {
        $purchaseOrderItemsParams = array_map(function ($value) use ($purchaseOrderId) {
            return [
                'purchase_order_id' => $purchaseOrderId,
                'product_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'price' => $value['price'],
                'total' => $value['quantity'] * $value['price'],
            ];
        }, $params['product_ids']);
        $purchaseOrderItems = $this->services['po_item']->insert($purchaseOrderId, $purchaseOrderItemsParams);

        $total = array_sum(array_map(function ($value) {
            return $value['quantity'] * $value['price'];
        }, $params['product_ids']));

        $result2 = $this->repository->updateTotal($purchaseOrderId, [
            'subtotal' => $total,
            'total' => $total,
        ]);

        return $purchaseOrderItems;
    }

    public function update(int $id, array $params = [])
    {
        return $this->repository->update($id, $params);
    }

    public function delete(int $id)
    {
        $purchaseOrder = $this->repository->delete($id)->getData(true);

        // TODO:: add PurchaseOrder Item
        return $this->repository->success();
    }

    public function dropdown()
    {
        return $this->repository->dropdown();
    }

    public function dropdowns(array $params = [])
    {
        return [
            'suppliers' => $this->services['supplier']->dropdown($params),
        ];
    }
}
