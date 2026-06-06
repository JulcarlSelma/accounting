<?php

namespace App\Models\Shops;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopStaff extends Model
{
    use SoftDeletes;

    protected $table = 'shop_staffs';

    public $fillable = [
        'shop_id',
        'staff_id',
        'employment_status',
        'hire_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
