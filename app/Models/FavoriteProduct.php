<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FavoriteProduct extends Model
{
    //
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'wish_lists';

    protected $fillable = [
        'user_id',
        'product_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
