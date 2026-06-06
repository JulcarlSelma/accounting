<?php

namespace App\Models\Products;

use App\Casts\BarcodeCast;
use App\Casts\ImageCast;
use App\Models\Shops\PurchaseOrderItem;
use App\Models\Suppliers\SuppliersProduct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    public $fillable = [
        'name',
        'description',
        'logo_path',
        'brand_id',
        'category_id',
        'unit',
        'unit_id',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'logo_path' => ImageCast::class,
        'is_active' => 'boolean',
        'barcode' => BarcodeCast::class,
    ];

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function unitR()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function suppliersProducts()
    {
        return $this->hasMany(SuppliersProduct::class, 'product_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id', 'id');
    }

    protected function unitDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit && $this->unitR
                ? "{$this->unit} {$this->unitR->abbreviation}"
                : null,
        );
    }

    protected function unitDisplayText(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit && $this->unitR
                ? "{$this->unit} {$this->unitR->name}"
                : null,
        );
    }
}
