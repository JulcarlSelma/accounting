<?php

namespace App\Models\Suppliers;

use App\Casts\ImageCast;
use App\Models\Shops\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    public $fillable = [
        'name',
        'contact_person',
        'logo_path',
        'email',
        'phone',
        'mobile',
        'address',
        'is_active',
    ];

    protected $casts = [
        'logo_path' => ImageCast::class,
        'is_active' => 'boolean',
    ];

    public function pricings()
    {
        return $this->hasMany(Pricing::class, 'supplier_id', 'id');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'supplier_id', 'id');
    }
}
