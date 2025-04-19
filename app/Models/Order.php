<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    //
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'location_id',
        'total_price',
        'status',
        'delivery_man_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function deliveryMan()
    {
        return $this->belongsTo(User::class, 'delivery_man_id');
    }
    public function location()
    {
        return $this->belongsTo(UserLocation::class);
    }
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function products()
    {
        return $this->hasManyThrough(Product::class, OrderProduct::class, 'order_id', 'id', 'id', 'product_id');
    }
    public function getTotalPrice()
    {
        return $this->unit_price * $this->quantity;
    }
}
