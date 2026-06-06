<?php

namespace App\Models\Suppliers;

use App\Casts\DateCast;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers_pricings';

    public $fillable = [
        'product_id',
        'supplier_id',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'updated_at' => DateCast::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
