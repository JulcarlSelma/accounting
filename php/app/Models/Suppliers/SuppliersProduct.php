<?php

namespace App\Models\Suppliers;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

class SuppliersProduct extends Model
{
    protected $table = 'supplier_products';

    public $fillable = [
        'product_id',
        'supplier_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'id', 'product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
