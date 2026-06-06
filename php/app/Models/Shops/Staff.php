<?php

namespace App\Models\Shops;

use App\Casts\ImageCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staffs';

    public $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'address',
        'profile_path',
        'is_active',
    ];

    protected $casts = [
        'profile_path' => ImageCast::class,
        'is_active' => 'boolean',
    ];

    protected $appends = ['fullname'];

    public function getFullnameAttribute()
    {
        return $this->first_name.' '.($this->middle_name ? $this->middle_name.' ' : '').$this->last_name;
    }

    public function shops()
    {
        return $this->hasMany(ShopStaff::class, 'staff_id', 'id');
    }

    public function shop()
    {
        return $this->hasOne(ShopStaff::class, 'staff_id', 'id');
    }
}
