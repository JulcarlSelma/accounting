<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerialNumbers extends Model
{
    use SoftDeletes;

    protected $table = 'product_serial_numbers';

    protected $fillable = [
        'product_id',
        'serial_number',
        'sku',
        'status',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
