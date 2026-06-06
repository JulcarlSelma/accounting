<?php

namespace App\Http\Repositories\Suppliers;

use App\Helper\FileHelper;
use App\Http\Repositories\BaseRepository;
use App\Models\Suppliers\Supplier;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SupplierRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Supplier;
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
        $contactPersonParam = isset($params['contact_person']) ? $params['contact_person'] : null;
        $emailParam = isset($params['email']) ? $params['email'] : null;
        $phoneParam = isset($params['phone']) ? $params['phone'] : null;
        $mobileParam = isset($params['mobile']) ? $params['mobile'] : null;
        $addressParam = isset($params['address']) ? $params['address'] : null;
        $isActiveParam = isset($params['is_active']) ? $params['is_active'] : null;

        if ($nameParam) {
            $query->whereLike('name', '%'.$nameParam.'%');
        }

        if ($contactPersonParam) {
            $query->whereLike('contact_person', '%'.$contactPersonParam.'%');
        }

        if ($emailParam) {
            $query->whereLike('email', '%'.$emailParam.'%');
        }

        if ($phoneParam) {
            $query->whereLike('phone', '%'.$phoneParam.'%');
        }

        if ($mobileParam) {
            $query->whereLike('mobile', '%'.$mobileParam.'%');
        }

        if ($addressParam) {
            $query->whereLike('address', '%'.$addressParam.'%');
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
            $supplier = $this->model->create($params);

            if ($logoFile instanceof UploadedFile) {
                $logoPath = $this->helper->uploadFile($logoFile, config('const.product_logo_path').'suppliers/'.$supplier->id);
                $supplier->update(['logo_path' => $logoPath]);
            }

            DB::commit();

            return $this->success($supplier, 'Supplier created successfully!');
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
            $supplier = $this->model->find($id);

            if (! isset($supplier)) {
                return $this->error('Data not found', [], $this->notFound);
            }

            DB::beginTransaction();
            $logoFile = isset($params['logo_path']) ? $params['logo_path'] : null;
            $logoPathRemove = $params['logo_path_remove'];

            if ($logoPathRemove && ! $logoFile) {
                $params['logo_path'] = null;
                if ($supplier->logo_path) {
                    $this->helper->deleteFile($supplier->getRawOriginal('logo_path'));
                }
            }

            if ($logoFile instanceof UploadedFile) {
                $params['logo_path'] = $this->helper->uploadFile($params['logo_path'], config('const.product_logo_path').'suppliers/'.$id);
                if ($supplier->logo_path) {
                    $this->helper->deleteFile($supplier->getRawOriginal('logo_path'));
                }
            }
            $supplier->update($params);
            $newSupplier = $this->model->find($id);
            DB::commit();

            return $this->success($newSupplier, 'Supplier updated successfully!');
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
            $supplier = $this->model->find($id);

            if (! isset($supplier)) {
                return $this->error('Data not found', [], $this->notFound);
            }
            DB::beginTransaction();
            $supplier->delete();
            DB::commit();

            return $this->success([], 'Supplier deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class().': '.__FUNCTION__.' function: '.$e);

            return $this->error('Something went wrong!', [$e->getMessage()], $this->internalServerError);
        }
    }

    public function dropdown(array $params = [])
    {
        $query = $this->model->where('is_active', true);

        $query = $this->dropdownFilters($query, $params);

        return $query->get();
    }

    private function dropdownFilters(Builder $query, array $params = [])
    {
        if (empty($params)) {
            return $query;
        }

        $isPricing = isset($params['is_pricing']) ? $params['is_pricing'] : null;
        $isNoSelectedItems = isset($params['is_no_selected_products']) ? $params['is_no_selected_products'] : null;
        $supplierId = isset($params['supplier_id']) ? $params['supplier_id'] : null;

        if ($supplierId) {
            $query->where('id', $supplierId);
        }

        if ($isPricing) {
            $query->with([
                'pricings' => function ($pricingQuery) use ($isNoSelectedItems, $isPricing) {
                    $pricingQuery->where('is_active', $isPricing);

                    if ($isNoSelectedItems) {
                        $pricingQuery->when($isNoSelectedItems, function ($q) {
                            $q->whereDoesntHave('product.purchaseOrderItems');
                        });
                    }
                },
                'pricings.product',
            ])
                ->whereHas('pricings', function ($pricingQuery) use ($isPricing) {
                    $pricingQuery->where('is_active', $isPricing);
                });
        }

        return $query;
    }
}
